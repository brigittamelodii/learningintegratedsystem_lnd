<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Program;
use App\Models\User;
use App\Models\payments;
use App\Models\ParticipantsPayment;
use App\Models\ClassEvaluation;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class TrainingOperationsMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $users = User::role('pic')->get();
        $selectedPic = $request->user_id ? User::find($request->user_id) : null;

        $paymentStats = $this->getPaymentStatistics($request);
        $evaluationStats = $this->getEvaluationStatistics($request);
        $picTransactionDetails = $this->getPicTransactionDetails($request);
        $summaryStats = $this->getSummaryStats($request);

        return view('monitoring.training-operations', compact(
            'users', 'paymentStats', 'evaluationStats', 'picTransactionDetails', 'summaryStats', 'selectedPic'
        ));
    }

    public function getPaymentStatistics(Request $request)
    {
        $users = User::role('pic')->get();
        $stats = [];

        foreach ($users as $user) {
            // Skip jika ada filter user_id dan bukan user ini
            if ($request->filled('user_id') && $request->user_id != $user->id) {
                continue;
            }

            $picName = $user->name ?? ucwords(str_replace(['.', '_'], ' ', Str::before($user->email, '@')));

            // Get programs for this PIC
            $programs = Program::where('user_id', $user->id)
                ->when($request->filled('year'), function ($q) use ($request) {
                    $q->whereHas('classes', fn($q2) => $q2->whereYear('start_date', $request->year));
                })
                ->get();

            $programIds = $programs->pluck('id');

            // Get general payments
            $generalPayments = payments::whereIn('program_id', $programIds)->get();
            
            // Get participant payments
            $participantPayments = ParticipantsPayment::whereIn('program_id', $programIds)->get();

            // Combine payments
            $allPayments = collect();
            
            // Add general payments
            foreach ($generalPayments as $payment) {
                $allPayments->push([
                    'status' => $payment->status,
                    'amount' => $payment->total_transfer ?? $payment->amount_fee,
                    'type' => 'general'
                ]);
            }
            
            // Add participant payments
            foreach ($participantPayments as $payment) {
                $allPayments->push([
                    'status' => $payment->status,
                    'amount' => $payment->amount_fee,
                    'type' => 'participant'
                ]);
            }

            // Group by status
            $grouped = $allPayments->groupBy('status');

            $stats[$user->id] = [
                'pic_name' => $picName,
                'pending' => [
                    'count' => $grouped->get('Pending', collect())->count(),
                    'amount' => $grouped->get('Pending', collect())->sum('amount')
                ],
                'check_by_pic' => [
                    'count' => $grouped->get('Check by PIC', collect())->count(),
                    'amount' => $grouped->get('Check by PIC', collect())->sum('amount')
                ],
                'check_by_manager' => [
                    'count' => $grouped->get('Check by Manager', collect())->count(),
                    'amount' => $grouped->get('Check by Manager', collect())->sum('amount')
                ],
                'approve' => [
                    'count' => $grouped->get('Approve', collect())->count(),
                    'amount' => $grouped->get('Approve', collect())->sum('amount')
                ],
                'reject' => [
                    'count' => $grouped->get('Reject', collect())->count(),
                    'amount' => $grouped->get('Reject', collect())->sum('amount')
                ]
            ];
        }

        return $stats;
    }

    public function getEvaluationStatistics(Request $request)
    {
        $users = User::role('pic')->get();
        $result = [];

        foreach ($users as $user) {
            // Skip jika ada filter user_id dan bukan user ini
            if ($request->filled('user_id') && $request->user_id != $user->id) {
                continue;
            }

            $picName = $user->name ?? ucwords(str_replace(['.', '_'], ' ', Str::before($user->email, '@')));

            $programs = Program::where('user_id', $user->id)
                ->when($request->filled('year'), function ($q) use ($request) {
                    $q->whereHas('classes', fn($q2) => $q2->whereYear('start_date', $request->year));
                })
                ->get();

            $allScores = [];
            $totalClasses = 0;
            $classDetails = [];

            foreach ($programs as $program) {
                $classes = Classes::where('program_id', $program->id)
                    ->when($request->filled('year'), fn($q) => $q->whereYear('start_date', $request->year))
                    ->get();

                foreach ($classes as $class) {
                    $totalClasses++;

                    // Get evaluations for this class
                    $evaluations = ClassEvaluation::where('class_id', $class->id)->get();
                    
                    $classScores = [];
                    $categoryScores = [];

                    // Get scores grouped by evaluation category
                    foreach ($evaluations as $eval) {
                        $evaluation = Evaluation::find($eval->eval_id);
                        if ($evaluation) {
                            $category = $evaluation->eval_cat;
                            if (!isset($categoryScores[$category])) {
                                $categoryScores[$category] = [];
                            }
                            $categoryScores[$category][] = $eval->eval_score;
                            $classScores[] = $eval->eval_score;
                        }
                    }

                    // Calculate averages per category
                    $avgByCategory = [];
                    foreach ($categoryScores as $category => $scores) {
                        $avgByCategory[$category] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
                    }

                    // Overall class average
                    $classAverage = count($classScores) > 0 ? round(array_sum($classScores) / count($classScores), 2) : 0;

                    $classDetails[] = [
                        'class_name' => $class->class_name,
                        'program_name' => $program->program_name,
                        'start_date' => $class->start_date,
                        'end_date' => $class->end_date,
                        'scores' => [
                            'Materi' => $avgByCategory['Materi'] ?? 0,
                            'Pengajar' => $avgByCategory['Pengajar'] ?? 0,
                            'Kepanitiaan' => $avgByCategory['Kepanitiaan'] ?? 0,
                        ],
                        'overall_average' => $classAverage,
                        'class_batch' => $class->class_batch,
                    ];

                    $allScores = array_merge($allScores, $classScores);
                }
            }

            $overallAverage = count($allScores) > 0 ? round(array_sum($allScores) / count($allScores), 2) : 0;

            $result[$user->id] = [
                'pic_name' => $picName,
                'total_classes' => $totalClasses,
                'overall_pic_average' => $overallAverage,
                'classes' => $classDetails
            ];
        }

        return $result;
    }

    public function getPicTransactionDetails(Request $request)
    {
        $users = User::role('pic')->get();
        $result = [];

        foreach ($users as $user) {
            // Skip jika ada filter user_id dan bukan user ini
            if ($request->filled('user_id') && $request->user_id != $user->id) {
                continue;
            }

            $picName = $user->name ?? ucwords(str_replace(['.', '_'], ' ', Str::before($user->email, '@')));

            $programs = Program::with(['category_budget', 'payments', 'participants_payment','classes'])
                ->where('user_id', $user->id)
                ->when($request->filled('year'), function ($q) use ($request) {
                    $q->whereHas('classes', fn($q2) => $q2->whereYear('start_date', $request->year));
                })
                ->get();

            $programDetails = [];

            foreach ($programs as $program) {
                $totalBudget = $program->category_budget->sum('amount_fee');
                
                // General payments by status
                $generalPayments = $program->payments->groupBy('status');
                $generalStats = [
                    'Approve' => $generalPayments->get('Approve', collect())->count(),
                    'Pending' => $generalPayments->get('Pending', collect())->count(),
                    'Reject' => $generalPayments->get('Reject', collect())->count(),
                ];

                // Participant payments by status
                $participantPayments = $program->participants_payment->groupBy('status');
                $participantStats = [
                    'Approve' => $participantPayments->get('Approve', collect())->count(),
                    'Pending' => $participantPayments->get('Pending', collect())->count(),
                    'Reject' => $participantPayments->get('Reject', collect())->count(),
                ];

                // Calculate totals
                $totalApprovedGeneral = $program->payments->where('status', 'Approve')->sum('total_transfer');
                $totalApprovedParticipant = $program->participants_payment->where('status', 'Approve')->sum('amount_fee');
                $totalApproved = $totalApprovedGeneral + $totalApprovedParticipant;
                
                $remainingBudget = $totalBudget - $totalApproved;
                $budgetUtilization = $totalBudget > 0 ? round(($totalApproved / $totalBudget) * 100, 2) : 0;

                $programDetails[] = [
                    'program_id' => $program->id,
                    'program_name' => $program->program_name,
                    'total_budget' => $totalBudget,
                    'general_payments' => $generalStats,
                    'participant_payments' => $participantStats,
                    'total_approved' => $totalApproved,
                    'remaining_budget' => $remainingBudget,
                    'budget_utilization' => $budgetUtilization,
                    'classes' => $program->classes ?? collect()
                ];
            }

            $result[$user->id] = [
                'pic_name' => $picName,
                'programs' => $programDetails
            ];
        }

        return $result;
    }

    public function getSummaryStats(Request $request)
    {
        $users = User::role('pic')->get();
        $result = [];

        foreach ($users as $user) {
            // Skip jika ada filter user_id dan bukan user ini
            if ($request->filled('user_id') && $request->user_id != $user->id) {
                continue;
            }

            $picName = $user->name ?? ucwords(str_replace(['.', '_'], ' ', Str::before($user->email, '@')));

            $programs = Program::where('user_id', $user->id)
                ->when($request->filled('year'), function ($q) use ($request) {
                    $q->whereHas('classes', fn($q2) => $q2->whereYear('start_date', $request->year));
                })
                ->get();

            foreach ($programs as $program) {
                $classes = Classes::where('program_id', $program->id)
                    ->when($request->filled('year'), fn($q) => $q->whereYear('start_date', $request->year))
                    ->get();

                $allScores = [];
                foreach ($classes as $class) {
                    $scores = ClassEvaluation::where('class_id', $class->id)->pluck('eval_score')->toArray();
                    $allScores = array_merge($allScores, $scores);
                }

                $overallAverage = count($allScores) > 0 ? round(array_sum($allScores) / count($allScores), 2) : 0;

                $result[] = [
                    'program_name' => $program->program_name,
                    'total_classes' => $classes->count(),
                    'overall_average' => $overallAverage,
                    'pic_name' => $picName,
                ];
            }
        }

        return $result;
    }

    public function picDetail($id)
    {
        $pic = User::findOrFail($id);
        $request = new Request(['user_id' => $id]);
        
        $users = User::role('pic')->get();
        $selectedPic = $pic;
        $paymentStats = $this->getPaymentStatistics($request);
        $evaluationStats = $this->getEvaluationStatistics($request);
        $picTransactionDetails = $this->getPicTransactionDetails($request);
        $summaryStats = $this->getSummaryStats($request);

        return view('monitoring.pic-detail', compact(
            'pic', 'users', 'paymentStats', 'evaluationStats', 'picTransactionDetails', 'summaryStats', 'selectedPic'
        ));
    }

    public function exportPdf(Request $request)
    {
        $users = User::role('pic')->get();
        $selectedPic = $request->user_id ? User::find($request->user_id) : null;

        $paymentStats = $this->getPaymentStatistics($request);
        $evaluationStats = $this->getEvaluationStatistics($request);
        $picTransactionDetails = $this->getPicTransactionDetails($request);
        $summaryStats = $this->getSummaryStats($request);

        $pdf = Pdf::loadView('monitoring.training-operations-pdf', compact(
            'users', 'paymentStats', 'evaluationStats', 'picTransactionDetails', 'summaryStats', 'selectedPic'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('training-operations.pdf');
    }

    private function getPicNameFromEmail($email)
    {
        return Str::of($email)
            ->before('@')
            ->replace(['.', '_'], ' ')
            ->title();
    }
}