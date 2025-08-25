<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Participant;
use App\Models\ParticipantsTemp;
use App\Models\payments;
use App\Models\ParticipantsPayment;
use App\Models\Tna;
use App\Models\Program;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Services\ParticipantDashboardService as ServicesParticipantDashboardService;
use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private DashboardDataService $dashboardService;

    public function __construct(DashboardDataService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request): JsonResponse|View
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return $this->handleSuperAdminDashboard($request);
        }
        
        if ($user->hasRole(['manager', 'executive'])) {
            return $this->adminDashboard($request);
        }

        if ($user->hasRole('pic')) {
            return $this->picDashboard($request);
        }

        if ($user->hasRole('participant')) {
            return $this->participantDashboard($request);
        }

        abort(403);
    }

    private function handleSuperAdminDashboard(Request $request): JsonResponse|View
    {
        $selectedYear = $request->get('year', now()->year);

        try {
            // Handle specific AJAX requests
            if ($request->ajax()) {
                return $this->handleAjaxRequests($request, $selectedYear);
            }

            // Load dashboard data using optimized service
            $dashboardData = $this->dashboardService->getAllDashboardData($selectedYear);

            // Return view for normal requests
            return view('dashboard.index', array_merge($dashboardData, ['selectedYear' => $selectedYear]));

        } catch (\Exception $e) {
            return $this->handleDashboardError($e, $request, $selectedYear);
        }
    }

    private function handleAjaxRequests(Request $request, int $selectedYear): JsonResponse
    {
        // Handle chart data request
        if ($request->has('chart_data')) {
            $upcomingClasses = $this->dashboardService->getUpcomingClassesChart($selectedYear);
            return response()->json(['upcomingClassesChart' => $upcomingClasses]);
        }

        // Handle classes attendance chart data request
        if ($request->has('classes_attendance_chart')) {
            $classesAttendance = $this->dashboardService->getClassesAttendanceChart($selectedYear);
            return response()->json(['classesAttendanceChart' => $classesAttendance]);
        }

        // Handle general AJAX dashboard data request
        $dashboardData = $this->dashboardService->getAllDashboardData($selectedYear);
        
        return response()->json([
            'success' => true,
            'data' => [
                'overviewStats' => $dashboardData['overviewStats'],
                'participantStats' => $dashboardData['participantStats'],
                'paymentStats' => $dashboardData['paymentStats'],
                'tnaRealizationStats' => $dashboardData['tnaRealizationStats'],
                'classesAttendanceChart' => $dashboardData['classesAttendanceChart'],
            ],
        ], 200, ['Content-Type' => 'application/json']);
    }

    private function handleDashboardError(\Exception $e, Request $request, int $selectedYear): JsonResponse|View
    {
        \Log::error('Dashboard error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());

        // Return error response for AJAX requests
        if ($request->ajax() || $request->get('ajax')) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }

        // Return view with default values for regular requests
        $defaultData = $this->dashboardService->getDefaultDashboardData();
        return view('dashboard.index', array_merge($defaultData, ['selectedYear' => $selectedYear]));
    }

    /**
     * API endpoint for calendar events - updated to use year filter
     */
    public function getCalendarEvents(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', now()->year);
            $events = $this->dashboardService->getCalendarEvents($year);
            
            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('Error in getCalendarEvents: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load calendar events',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get class details for modal
     */
    public function getClassDetails(int $classId): JsonResponse
    {
        try {
            $classDetails = $this->dashboardService->getClassDetails($classId);
            return response()->json($classDetails);
        } catch (\Exception $e) {
            \Log::error('Error in getClassDetails: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load class details',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get optimized participant statistics for a specific class
     */
    public function getClassParticipantStats(int $classId): JsonResponse
    {
        try {
            $finalParticipants = Participant::where('class_id', $classId)->get();
            $tempParticipants = ParticipantsTemp::where('class_id', $classId)->get();
            
            $stats = [
                'total_invited' => $finalParticipants->count() + $tempParticipants->count(),
                'present' => $finalParticipants->where('status', 'Present')->count(),
                'absent' => [
                    'sick' => $finalParticipants->where('status', 'Absent - Sick')->count(),
                    'busy' => $finalParticipants->where('status', 'Absent - Busy')->count(),
                    'maternity' => $finalParticipants->where('status', 'Absent - Maternity')->count(),
                    'business' => $finalParticipants->where('status', 'Absent - Business')->count(),
                    'general' => $finalParticipants->where('status', 'Absent - General')->count(),
                ],
                'invited_pending' => $tempParticipants->where('status', 'Invited')->count(),
            ];
            
            $totalAbsent = array_sum($stats['absent']);
            $stats['total_absent'] = $totalAbsent;
            
            // Calculate percentages
            if ($stats['total_invited'] > 0) {
                $stats['attendance_rate'] = round(($stats['present'] / $stats['total_invited']) * 100, 2);
                $stats['absent_rate'] = round(($totalAbsent / $stats['total_invited']) * 100, 2);
                $stats['pending_rate'] = round(($stats['invited_pending'] / $stats['total_invited']) * 100, 2);
            } else {
                $stats['attendance_rate'] = 0;
                $stats['absent_rate'] = 0;
                $stats['pending_rate'] = 0;
            }
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getClassParticipantStats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load participant statistics'
            ], 500);
        }
    }

    /**
     * Get compact participant summary across all classes for a year
     */
    public function getYearlyParticipantSummary(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', now()->year);
            
            // Get optimized participant stats
            $participantStats = $this->dashboardService->getCompactParticipantStats($year);
            
            // Add additional insights
            $classIds = Classes::whereYear('start_date', $year)->pluck('id');
            $classCount = $classIds->count();
            
            if ($classCount > 0) {
                $avgParticipantsPerClass = round($participantStats['total_invited'] / $classCount, 1);
                $avgAttendancePerClass = round($participantStats['total_participants'] / $classCount, 1);
            } else {
                $avgParticipantsPerClass = 0;
                $avgAttendancePerClass = 0;
            }
            
            return response()->json([
                'success' => true,
                'data' => array_merge($participantStats, [
                    'total_classes' => $classCount,
                    'avg_participants_per_class' => $avgParticipantsPerClass,
                    'avg_attendance_per_class' => $avgAttendancePerClass,
                ])
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getYearlyParticipantSummary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load yearly participant summary'
            ], 500);
        }
    }

    // Role-specific dashboard methods
    public function adminDashboard(Request $request): View
    {   
        $selectedYear = $request->get('year', now()->year);
        return view('dashboard.admin', compact('selectedYear'));
    }

    public function picDashboard(Request $request): View
    {
        $selectedYear = $request->get('year', now()->year);
        $picData = $this->dashboardService->getPicDashboardData($selectedYear);
        
        return view('dashboard.pic', array_merge($picData, ['selectedYear' => $selectedYear]));
    }

    public function participantDashboard(?Request $request = null): View
    {   
        if ($request === null) {
            $request = request();
        }
        
        $user = auth()->user();
        $service = new ServicesParticipantDashboardService($user);
        $data = $service->getDashboardData();

        return view('dashboard.participant', $data);
    }

    public function getParticipantTransactions(): JsonResponse
    {
        try {
            $transactions = $this->dashboardService->getParticipantTransactions();
            
            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getParticipantTransactions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load transactions'
            ], 500);
        }
    }

    /**
     * Debug endpoint to compare old vs new participant calculation
     * Remove this in production
     */
    public function debugParticipantStats(Request $request): JsonResponse
    {
        if (!config('app.debug')) {
            abort(404);
        }
        
        try {
            $year = $request->get('year', now()->year);
            
            // New optimized method
            $newStats = $this->dashboardService->getCompactParticipantStats($year);
            
            // Old method for comparison (simplified)
            $classIds = Classes::whereYear('start_date', $year)->pluck('id');
            $finalParticipants = Participant::whereIn('class_id', $classIds)->get();
            $tempParticipants = ParticipantsTemp::whereIn('class_id', $classIds)->get();
            
            $oldStats = [
                'final_participants_count' => $finalParticipants->count(),
                'temp_participants_count' => $tempParticipants->count(),
                'total_old_way' => $finalParticipants->count() + $tempParticipants->count(),
            ];
            
            return response()->json([
                'year' => $year,
                'new_optimized_stats' => $newStats,
                'old_method_comparison' => $oldStats,
                'performance_note' => 'New method reduces database queries and provides unified view'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}