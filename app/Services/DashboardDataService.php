<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Participant;
use App\Models\ParticipantsTemp;
use App\Models\payments;
use App\Models\ParticipantsPayment;
use App\Models\Tna;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class DashboardDataService
{
    /**
     * Get all dashboard data for superadmin
     */
    public function getAllDashboardData(int $year): array
    {
        return [
            'trainingCalendar' => $this->getTrainingCalendar($year),
            'participantStats' => $this->getCompactParticipantStats($year), // Updated method name
            'paymentStats' => $this->getPaymentStats($year),
            'tnaRealizationStats' => $this->getTnaRealizationStats($year),
            'overviewStats' => $this->getOverviewStats($year),
            'classesAttendanceChart' => $this->getClassesAttendanceChart($year),
            'recentActivities' => $this->getActivities(), // Updated to use new method
        ];
    }

        private function getActivities(): array
    {
        try {
            $activities = [];
            
            // Get programs where user is PIC
            $programIds = Program::all()->pluck('id')->toArray();
            
            if (empty($programIds)) {
                return [];
            }
            
            // Recent payment activities
            $recentPayments = payments::whereIn('program_id', $programIds)
                ->where('updated_at', '>=', now()->subDays(7))
                ->orderBy('updated_at', 'desc')
                ->take(3)
                ->get();
                
            foreach ($recentPayments as $payment) {
                $activities[] = [
                    'title' => 'Payment Status Updated',
                    'description' => "Payment #{$payment->id} status changed to {$payment->status}",
                    'time' => $payment->updated_at->diffForHumans(),
                    'type' => $this->getActivityType($payment->status)
                ];
            }
            
            // Recent participant payments
            $recentParticipantPayments = ParticipantsPayment::whereIn('program_id', $programIds)
                ->where('updated_at', '>=', now()->subDays(7))
                ->with(['programs'])
                ->orderBy('updated_at', 'desc')
                ->take(3)
                ->get();
                
            foreach ($recentParticipantPayments as $payment) {
                $activities[] = [
                    'title' => 'Participant Payment',
                    'description' => "New participant payment for {$payment->programs->program_name}",
                    'time' => $payment->updated_at->diffForHumans(),
                    'type' => 'info'
                ];
            }
            
            // Recent class completions
            $recentCompletedClasses = Classes::whereIn('program_id', $programIds)
                ->where('end_date', '>=', now()->subDays(7))
                ->where('end_date', '<', now())
                ->with(['programs'])
                ->orderBy('end_date', 'desc')
                ->take(2)
                ->get();
                
            foreach ($recentCompletedClasses as $class) {
                $activities[] = [
                    'title' => 'Class Completed',
                    'description' => "{$class->class_name} has been completed",
                    'time' => Carbon::parse($class->end_date)->diffForHumans(),
                    'type' => 'success'
                ];
            }
            
            // Sort activities by time and limit to 5
            usort($activities, function($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });
            
            return array_slice($activities, 0, 5);
            
        } catch (\Exception $e) {
            \Log::error('Error in getPicActivities: ' . $e->getMessage());
            return [];
        }
    }

    private function getActivityType(string $status): string
    {
        switch ($status) {
            case 'Approve':
                return 'success';
            case 'Reject':
                return 'warning';
            case 'Pending':
            case 'Check by PIC':
            case 'Check by Manager':
                return 'info';
            default:
                return 'info';
        }
    }
    

    /**
     * Get default dashboard data (for error scenarios)
     */
    public function getDefaultDashboardData(): array
    {
        return [
            'trainingCalendar' => [],
            'participantStats' => [
                'total_participants' => 0,
                'total_invited' => 0,
                'attendance_rate' => 0,
                'status_summary' => [
                    'present' => 0,
                    'absent_sick' => 0,
                    'absent_busy' => 0,
                    'absent_maternity' => 0,
                    'absent_business' => 0,
                    'absent_general' => 0,
                    'invited' => 0
                ],
                'attendance_breakdown' => [
                    'attended_percentage' => 0,
                    'not_attended_percentage' => 0
                ]
            ],
            'paymentStats' => [
                'combined_stats' => [
                    'total_amount' => 0,
                    'total_count' => 0,
                    'approve' => ['count' => 0],
                    'pending' => ['count' => 0],
                    'check_by_pic' => ['count' => 0],
                    'check_by_manager' => ['count' => 0]
                ]
            ],
            'tnaRealizationStats' => [
                'tna_details' => [],
                'overall' => [
                    'total_min_budget' => 0,
                    'total_realization' => 0,
                    'overall_percentage' => 0,
                    'total_remaining' => 0
                ]
            ],
            'overviewStats' => [
                'total_classes' => 0,
                'total_programs' => 0,
                'total_pics' => 0,
                'upcoming_classes' => 0,
                'active_tnas' => 0
            ],
            'classesAttendanceChart' => []
        ];
    }

    /**
     * Get compact participant statistics (combining temp and final participants)
     * This provides a unified view of total invited vs actual attendance
     */
    public function getCompactParticipantStats(int $year): array
    {
        try {
            \Log::info("Getting compact participant stats for year: {$year}");
            
            $classIds = Classes::whereYear('start_date', $year)->pluck('id');
            
            if ($classIds->isEmpty()) {
                \Log::info("No classes found for year: {$year}");
                return $this->getDefaultDashboardData()['participantStats'];
            }

            // Get all participants data in one go for better performance
            $finalParticipants = Participant::whereIn('class_id', $classIds)
                ->select('status', 'class_id')
                ->get();
            
            $tempParticipants = ParticipantsTemp::whereIn('class_id', $classIds)
                ->select('status', 'class_id')
                ->get();

            // Calculate unified statistics
            $stats = $this->calculateUnifiedParticipantStats($finalParticipants, $tempParticipants);
            
            \Log::info("Participant stats calculated", $stats);
            
            return $stats;
            
        } catch (\Exception $e) {
            \Log::error('Error in getCompactParticipantStats: ' . $e->getMessage());
            return $this->getDefaultDashboardData()['participantStats'];
        }
    }

    /**
     * Calculate unified participant statistics from both final and temp participants
     */
    private function calculateUnifiedParticipantStats($finalParticipants, $tempParticipants): array
    {
        // Count status from final participants (actual attendance)
        $finalStatusCounts = [
            'present' => $finalParticipants->where('status', 'Present')->count(),
            'absent_sick' => $finalParticipants->where('status', 'Absent - Sick')->count(),
            'absent_busy' => $finalParticipants->where('status', 'Absent - Busy')->count(),
            'absent_maternity' => $finalParticipants->where('status', 'Absent - Maternity')->count(),
            'absent_business' => $finalParticipants->where('status', 'Absent - Business')->count(),
            'absent_general' => $finalParticipants->where('status', 'Absent - General')->count(),
        ];

        // Count invitations from temp participants (pending attendance)
        $tempStatusCounts = [
            'invited' => $tempParticipants->where('status', 'Invited')->count(),
        ];

        // Calculate totals
        $totalFinalParticipants = $finalParticipants->count();
        $totalTempParticipants = $tempParticipants->count();
        $totalInvited = $totalFinalParticipants + $totalTempParticipants; // Total people ever invited
        $totalPresent = $finalStatusCounts['present'];
        
        // Calculate total absent from all absent categories
        $totalAbsent = $finalStatusCounts['absent_sick'] + 
                      $finalStatusCounts['absent_busy'] + 
                      $finalStatusCounts['absent_maternity'] + 
                      $finalStatusCounts['absent_business'] + 
                      $finalStatusCounts['absent_general'];

        // Calculate attendance rate (present / total with final status)
        $attendanceRate = $totalFinalParticipants > 0 
            ? round(($totalPresent / $totalFinalParticipants) * 100, 2) 
            : 0;

        // Calculate attendance breakdown percentages
        $attendedPercentage = $totalInvited > 0 
            ? round(($totalPresent / $totalInvited) * 100, 2) 
            : 0;
            
        $notAttendedPercentage = $totalInvited > 0 
            ? round((($totalAbsent + $tempStatusCounts['invited']) / $totalInvited) * 100, 2) 
            : 0;

        return [
            'total_participants' => $totalFinalParticipants, // Participants with final status
            'total_invited' => $totalInvited, // Total people invited (temp + final)
            'attendance_rate' => $attendanceRate,
            'status_summary' => array_merge($finalStatusCounts, $tempStatusCounts),
            'attendance_breakdown' => [
                'attended_percentage' => $attendedPercentage,
                'not_attended_percentage' => $notAttendedPercentage,
                'pending_percentage' => 100 - $attendedPercentage - ($totalAbsent > 0 ? round(($totalAbsent / $totalInvited) * 100, 2) : 0)
            ],
            'summary_totals' => [
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_pending' => $tempStatusCounts['invited']
            ]
        ];
    }

    public function getClassesAttendanceChart(int $year): array
    {
        try {
            \Log::info("Getting classes attendance chart for year: {$year}");
            
            $classes = Classes::with(['programs'])
                ->whereYear('start_date', $year)
                ->orderBy('start_date', 'desc')
                ->take(10)
                ->get();

            if ($classes->isEmpty()) {
                \Log::info("No classes found for year: {$year}");
                return [];
            }

            $classIds = $classes->pluck('id')->toArray();
            
            // FIXED: Filter participants by year through relationship
            $finalParticipants = Participant::whereIn('class_id', $classIds)
                ->whereHas('classes', function($query) use ($year) {
                    $query->whereYear('start_date', $year);
                })
                ->select('class_id', 'status')
                ->get()
                ->groupBy('class_id');
                
            $tempParticipants = ParticipantsTemp::whereIn('class_id', $classIds)
                ->whereHas('classes', function($query) use ($year) {
                    $query->whereYear('start_date', $year);
                })
                ->select('class_id', 'status')
                ->get()
                ->groupBy('class_id');

            $chartData = [];
            
            foreach ($classes as $class) {
                $classId = $class->id;
                $classFinalParticipants = $finalParticipants->get($classId, collect());
                $classTempParticipants = $tempParticipants->get($classId, collect());
                
                // Count status for this specific class
                $attendance = [
                    'present' => $classFinalParticipants->where('status', 'Present')->count(),
                    'absent_sick' => $classFinalParticipants->where('status', 'Absent - Sick')->count(),
                    'absent_busy' => $classFinalParticipants->where('status', 'Absent - Busy')->count(),
                    'absent_maternity' => $classFinalParticipants->where('status', 'Absent - Maternity')->count(),
                    'absent_business' => $classFinalParticipants->where('status', 'Absent - Business')->count(),
                    'absent_general' => $classFinalParticipants->where('status', 'Absent - General')->count(),
                    'invited' => $classTempParticipants->where('status', 'Invited')->count(),
                ];
                
                $totalParticipants = $classFinalParticipants->count() + $classTempParticipants->count();
                
                // Only include classes with participants
                if ($totalParticipants > 0) {
                    $chartData[] = [
                        'class_id' => $class->id,
                        'class_name' => $class->class_name,
                        'program_name' => $class->programs->program_name ?? 'Unknown Program',
                        'start_date' => Carbon::parse($class->start_date)->format('M d, Y'),
                        'class_batch' => $class->class_batch,
                        'total_participants' => $totalParticipants,
                        'attendance' => $attendance
                    ];
                }
            }
            
            return array_reverse($chartData); // Show oldest first (left to right)
            
        } catch (\Exception $e) {
            \Log::error('Error in getClassesAttendanceChart: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming classes for chart data (optimized)
     * FIXED: Better year filtering
     */
    public function getUpcomingClassesChart(int $year): Collection
    {
        try {
            \Log::info("Getting upcoming classes chart for year: {$year}");
            
            $upcomingClasses = Classes::with(['programs'])
                ->where('start_date', '>=', now())
                ->whereYear('start_date', $year)
                ->orderBy('start_date')
                ->take(5)
                ->get();

            if ($upcomingClasses->isEmpty()) {
                \Log::info("No upcoming classes found for year: {$year}");
                return collect([]);
            }

            $classIds = $upcomingClasses->pluck('id')->toArray();
            
            // FIXED: Filter participants by year through relationship
            $finalParticipants = Participant::whereIn('class_id', $classIds)
                ->whereHas('classes', function($query) use ($year) {
                    $query->whereYear('start_date', $year);
                })
                ->select('class_id', 'status')
                ->get()
                ->groupBy('class_id');
                
            $tempParticipants = ParticipantsTemp::whereIn('class_id', $classIds)
                ->whereHas('classes', function($query) use ($year) {
                    $query->whereYear('start_date', $year);
                })
                ->select('class_id', 'status')
                ->get()
                ->groupBy('class_id');

            return $upcomingClasses->map(function($class) use ($finalParticipants, $tempParticipants) {
                $classId = $class->id;
                $classFinalParticipants = $finalParticipants->get($classId, collect());
                $classTempParticipants = $tempParticipants->get($classId, collect());
                
                return [
                    'class_name' => $class->class_name,
                    'start_date' => $class->start_date->format('M d'),
                    'full_start_date' => $class->start_date->format('Y-m-d'),
                    'program_name' => $class->programs->program_name ?? 'Unknown Program',
                    'status' => [
                        'present' => $classFinalParticipants->where('status', 'Present')->count(),
                        'absent_busy' => $classFinalParticipants->where('status', 'Absent - Busy')->count(),
                        'absent_general' => $classFinalParticipants->where('status', 'Absent - General')->count(),
                        'absent_sick' => $classFinalParticipants->where('status', 'Absent - Sick')->count(),
                        'absent_business' => $classFinalParticipants->where('status', 'Absent - Business')->count(),
                        'absent_maternity' => $classFinalParticipants->where('status', 'Absent - Maternity')->count(),
                        'invited' => $classTempParticipants->where('status', 'Invited')->count(),
                    ],
                    'total_participants' => $classFinalParticipants->count() + $classTempParticipants->count(),
                    'class_location' => $class->class_loc,
                    'class_batch' => $class->class_batch
                ];
            });
            
        } catch (\Exception $e) {
            \Log::error('Error in getUpcomingClassesChart: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * Get training calendar data
     */
    public function getTrainingCalendar(int $year): array
    {
        try {
            $classes = Classes::with(['programs'])
                ->whereYear('start_date', $year)
                ->get();
            
            $calendar = [];
            foreach ($classes as $class) {
                $startDate = Carbon::parse($class->start_date);
                $endDate = Carbon::parse($class->end_date);
                
                $calendar[] = [
                    'id' => $class->id,
                    'title' => $class->class_name,
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->addDay()->toDateString(),
                    'program_name' => $class->programs->program_name ?? 'Unknown Program',
                    'batch' => $class->class_batch,
                    'location' => $class->class_loc,
                    'participants_count' => $class->participants()->count(),
                    'backgroundColor' => $this->getClassColor($class),
                    'borderColor' => $this->getClassColor($class),
                    'textColor' => '#ffffff'
                ];
            }
            
            return $calendar;
        } catch (\Exception $e) {
            \Log::error('Error in getTrainingCalendar: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats(int $year): array
    {
        try {
            $programIds = Program::whereHas('classes', function ($query) use ($year) {
                $query->whereYear('start_date', $year);
            })->pluck('id');

            $generalPayments = payments::whereIn('program_id', $programIds)->get();
            $participantPayments = ParticipantsPayment::whereIn('program_id', $programIds)->get();
            
            $allPayments = $this->combinePayments($generalPayments, $participantPayments);
            $statusBreakdown = $allPayments->groupBy('status');
            
            return [
                'general_payments' => [
                    'total' => $generalPayments->count(),
                    'amount' => $generalPayments->sum('total_transfer'),
                    'status_breakdown' => $generalPayments->groupBy('status')->map->count()
                ],
                'participant_payments' => [
                    'total' => $participantPayments->count(),
                    'amount' => $participantPayments->sum('amount_fee'),
                    'status_breakdown' => $participantPayments->groupBy('status')->map->count()
                ],
                'combined_stats' => $this->calculateCombinedPaymentStats($allPayments, $statusBreakdown)
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getPaymentStats: ' . $e->getMessage());
            return $this->getDefaultDashboardData()['paymentStats'];
        }
    }

    /**
     * Get TNA realization statistics
     */
    public function getTnaRealizationStats(int $year): array
    {
        try {
            \Log::info("Getting TNA stats for year: {$year}");

            $tnas = Tna::where('tna_year', $year)
                ->with([
                    'category.training_program.programs.payments',
                    'category.training_program.programs.participants_payment'
                ])
                ->get();

            if ($tnas->isEmpty()) {
                \Log::info("No TNAs found for year: {$year}");
                return $this->getDefaultDashboardData()['tnaRealizationStats'];
            }

            $tnaStats = [];
            $totalMinBudget = 0;
            $totalRealization = 0;

            foreach ($tnas as $tna) {
                $tnaRealization = $this->calculateTnaRealization($tna);
                
                $realizationPercentage = $tna->tna_min_budget > 0
                    ? round(($tnaRealization / $tna->tna_min_budget) * 100, 2)
                    : 0;

                $programsCount = $tna->category->flatMap(function($category) {
                    return $category->training_program;
                })->count();

                $tnaStats[] = [
                    'tna_id' => $tna->id,
                    'tna_year' => $tna->tna_year,
                    'min_budget' => $tna->tna_min_budget,
                    'realization' => $tnaRealization,
                    'percentage' => $realizationPercentage,
                    'remaining' => $tna->tna_min_budget - $tnaRealization,
                    'programs_count' => $programsCount,
                    'programs' => $this->getTnaProgramDetails($tna, $year)
                ];

                $totalMinBudget += $tna->tna_min_budget;
                $totalRealization += $tnaRealization;
            }

            $overallPercentage = $totalMinBudget > 0
                ? round(($totalRealization / $totalMinBudget) * 100, 2)
                : 0;

            return [
                'tna_details' => $tnaStats,
                'overall' => [
                    'total_min_budget' => $totalMinBudget,
                    'total_realization' => $totalRealization,
                    'overall_percentage' => $overallPercentage,
                    'total_remaining' => $totalMinBudget - $totalRealization
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getTnaRealizationStats: ' . $e->getMessage());
            return $this->getDefaultDashboardData()['tnaRealizationStats'];
        }
    }

    /**
     * Get overview statistics for dashboard cards
     */
    public function getOverviewStats(int $year): array
    {
        try {
            $totalClasses = Classes::whereYear('start_date', $year)->count();
            
            $totalPrograms = Program::whereHas('classes', function ($query) use ($year) {
                $query->whereYear('start_date', $year);
            })->count();
            
            $totalPics = User::role('pic')->count();
            
            $upcomingClasses = Classes::whereBetween('start_date', [
                Carbon::now(),
                Carbon::now()->addDays(30)
            ])->count();
            
            $activeTnas = Tna::where('tna_year', $year)->count();
            
            return [
                'total_classes' => $totalClasses,
                'total_programs' => $totalPrograms,
                'total_pics' => $totalPics,
                'upcoming_classes' => $upcomingClasses,
                'active_tnas' => $activeTnas
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getOverviewStats: ' . $e->getMessage());
            return $this->getDefaultDashboardData()['overviewStats'];
        }
    }

    /**
     * Get calendar events
     */
    public function getCalendarEvents(int $year): array
    {
        $classes = Classes::with(['programs'])
            ->whereYear('start_date', $year)
            ->get();
        
        $events = [];
        foreach ($classes as $class) {
            $events[] = [
                'id' => $class->id,
                'title' => $class->class_name,
                'start' => $class->start_date,
                'end' => Carbon::parse($class->end_date)->addDay()->toDateString(),
                'backgroundColor' => $this->getClassColor($class),
                'borderColor' => $this->getClassColor($class),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'program_name' => $class->programs->program_name ?? 'Unknown Program',
                    'batch' => $class->class_batch,
                    'location' => $class->class_loc,
                    'participants_count' => $class->participants()->count()
                ]
            ];
        }
        
        return $events;
    }

    /**
     * Get class details
     */
    public function getClassDetails(int $classId): array
    {
        $class = Classes::with([
            'programs.user',
            'participants',
            'agenda'
        ])->findOrFail($classId);
        
        $participantStats = [
            'total' => $class->participants->count(),
            'present' => $class->participants->where('status', 'Present')->count(),
            'absent' => $class->participants->where('status', '!=', 'Present')->count()
        ];
        
        return [
            'class' => $class,
            'participant_stats' => $participantStats
        ];
    }

    /**
     * Get PIC dashboard data
     */
    public function getPicDashboardData(int $selectedYear): array
{
    try {
        $user = auth()->user();
        
        // Delegate to specialized PIC service
        $picService = new \App\Services\PicDashboardService($user);
        return $picService->getDashboardData($selectedYear);
        
    } catch (\Exception $e) {
        \Log::error('Error in getPicDashboardData: ' . $e->getMessage());
        return [
            'picStats' => [
                'total_programs' => 0,
                'active_classes' => 0,
                'total_participants' => 0,
                'pending_payments' => 0,
                'success_rate' => 0,
                'avg_rating' => 0,
                'completed_classes' => 0,
                'upcoming_classes' => 0,
            ],
            'picTrainingCalendar' => [],
            'picTasks' => [],
            'picActivities' => [],
            'picClasses' => [],
        ];
    }
}

    /**
     * Get participant transactions
     */
    public function getParticipantTransactions(): Collection
    {
        $user = auth()->user();
        
        return ParticipantsPayment::where('user_id', $user->id)
            ->with(['programs', 'classes'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'program_name' => $transaction->programs->program_name ?? 'Unknown Program',
                    'class_name' => $transaction->classes->class_name ?? 'Unknown Class',
                    'amount' => $transaction->amount_fee,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'remarks' => $transaction->remarks
                ];
            });
    }

    // Private helper methods
    private function getClassColor($class): string
    {
        $colors = [
            '#3498db', '#e74c3c', '#2ecc71', '#f39c12',
            '#9b59b6', '#1abc9c', '#34495e', '#e67e22'
        ];
        
        $programId = $class->programs->id ?? 0;
        return $colors[$programId % count($colors)];
    }

    private function combinePayments($generalPayments, $participantPayments): Collection
    {
        $allPayments = collect();
        
        foreach ($generalPayments as $payment) {
            $allPayments->push([
                'status' => $payment->status,
                'amount' => $payment->total_transfer ?? $payment->amount_fee,
                'type' => 'general',
                'created_at' => $payment->created_at
            ]);
        }
        
        foreach ($participantPayments as $payment) {
            $allPayments->push([
                'status' => $payment->status,
                'amount' => $payment->amount_fee,
                'type' => 'participant',
                'created_at' => $payment->created_at
            ]);
        }

        return $allPayments;
    }

    private function calculateCombinedPaymentStats($allPayments, $statusBreakdown): array
    {
        return [
            'total_count' => $allPayments->count(),
            'total_amount' => $allPayments->sum('amount'),
            'pending' => [
                'count' => $statusBreakdown->get('Pending', collect())->count(),
                'amount' => $statusBreakdown->get('Pending', collect())->sum('amount')
            ],
            'check_by_pic' => [
                'count' => $statusBreakdown->get('Check by PIC', collect())->count(),
                'amount' => $statusBreakdown->get('Check by PIC', collect())->sum('amount')
            ],
            'check_by_manager' => [
                'count' => $statusBreakdown->get('Check by Manager', collect())->count(),
                'amount' => $statusBreakdown->get('Check by Manager', collect())->sum('amount')
            ],
            'approve' => [
                'count' => $statusBreakdown->get('Approve', collect())->count(),
                'amount' => $statusBreakdown->get('Approve', collect())->sum('amount')
            ],
            'reject' => [
                'count' => $statusBreakdown->get('Reject', collect())->count(),
                'amount' => $statusBreakdown->get('Reject', collect())->sum('amount')
            ]
        ];
    }

    private function calculateTnaRealization(Tna $tna): float
    {
        try {
            $totalRealization = 0;

            foreach ($tna->category as $category) {
                foreach ($category->training_program as $trainingProgram) {
                    foreach ($trainingProgram->programs as $program) {
                        $generalPayments = $program->payments()
                            ->where('status', 'Approve')
                            ->sum('total_transfer');

                        $participantPayments = $program->participants_payment()
                            ->where('status', 'Approve')
                            ->sum('amount_fee');

                        $totalRealization += ($generalPayments + $participantPayments);
                    }
                }
            }

            return $totalRealization;
        } catch (\Exception $e) {
            \Log::error("Error calculating TNA realization for TNA {$tna->id}: " . $e->getMessage());
            return 0;
        }
    }

    private function getTnaProgramDetails(Tna $tna, int $year): array
    {
        try {
            $programDetails = collect();

            foreach ($tna->category as $category) {
                foreach ($category->training_program as $trainingProgram) {
                    foreach ($trainingProgram->programs as $program) {
                        $generalPayments = $program->payments()
                            ->where('status', 'Approve')
                            ->sum('total_transfer');

                        $participantPayments = $program->participants_payment()
                            ->where('status', 'Approve')
                            ->sum('amount_fee');

                        $programRealization = $generalPayments + $participantPayments;

                        $programDetails->push([
                            'program_id' => $program->id,
                            'program_name' => $program->program_name,
                            'category_name' => $category->name,
                            'classes_count' => $program->classes()->count(),
                            'participants_count' => $program->classes()
                                ->withCount('participants')
                                ->get()
                                ->sum('participants_count'),
                            'realization' => $programRealization,
                        ]);
                    }
                }
            }

            return $programDetails->toArray();
        } catch (\Exception $e) {
            \Log::error("Error getting TNA program details: " . $e->getMessage());
            return [];
        }
    }
}