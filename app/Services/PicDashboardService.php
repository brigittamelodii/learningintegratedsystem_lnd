<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Participant;
use App\Models\ParticipantsTemp;
use App\Models\payments;
use App\Models\ParticipantsPayment;
use App\Models\Program;
use App\Models\User;
use App\Models\Tna;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PicDashboardService
{
    private $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    /**
     * Get all dashboard data for PIC
     */
    public function getDashboardData(int $selectedYear): array
    {
        try {
            \Log::info("Getting PIC dashboard data for user: {$this->user->id}, year: {$selectedYear}");
            
            return [
                'picStats' => $this->getPicStats($selectedYear),
                'picTrainingCalendar' => $this->getPicTrainingCalendar($selectedYear),
                'picTasks' => $this->getPicTasks(),
                'picActivities' => $this->getPicActivities(),
                'picClasses' => $this->getPicClasses($selectedYear),
            ];
        } catch (\Exception $e) {
            \Log::error('PIC Dashboard Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->getDefaultData();
        }
    }
    
    /**
     * Get PIC statistics
     */
    private function getPicStats(int $selectedYear): array
    {
        try {
            // Get programs where user is PIC
            $picPrograms = Program::where('user_id', $this->user->id)->get();
            $programIds = $picPrograms->pluck('id')->toArray();
            
            if (empty($programIds)) {
                return $this->getDefaultStats();
            }
            
            // Get classes for PIC's programs in selected year
            $picClasses = Classes::whereIn('program_id', $programIds)
                ->whereYear('start_date', $selectedYear)
                ->get();
            
            $classIds = $picClasses->pluck('id')->toArray();
            
            // Calculate statistics
            $totalPrograms = $picPrograms->count();
            $totalClasses = $picClasses->count();
            
            // Active classes (ongoing or upcoming)
            $activeClasses = $picClasses->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count();
            
            // Completed classes
            $completedClasses = $picClasses->where('end_date', '<', now())->count();
            
            // Upcoming classes (within next 30 days)
            $upcomingClasses = $picClasses->where('start_date', '>', now())
                ->where('start_date', '<=', now()->addDays(30))
                ->count();
            
            // Total participants across all PIC's classes
            $totalParticipants = 0;
            $presentCount = 0;
            
            if (!empty($classIds)) {
                $participants = Participant::whereIn('class_id', $classIds)->get();
                $tempParticipants = ParticipantsTemp::whereIn('class_id', $classIds)->get();
                
                $totalParticipants = $participants->count() + $tempParticipants->count();
                $presentCount = $participants->where('status', 'Present')->count();
            }
            
            // Calculate success rate (attendance rate)
            $successRate = $totalParticipants > 0 ? 
                round(($presentCount / $totalParticipants) * 100, 2) : 0;
            
            // Pending payments
            $pendingPayments = 0;
            if (!empty($programIds)) {
                $generalPending = payments::whereIn('program_id', $programIds)
                    ->whereIn('status', ['Pending', 'Check by PIC'])
                    ->count();
                    
                $participantPending = ParticipantsPayment::whereIn('program_id', $programIds)
                    ->whereIn('status', ['Pending', 'Check by PIC'])
                    ->count();
                    
                $pendingPayments = $generalPending + $participantPending;
            }
            
            // Calculate average rating (mock data for now - could be from feedback system)
            $avgRating = $this->calculateAverageRating($classIds);
            
            return [
                'total_programs' => $totalPrograms,
                'active_classes' => $activeClasses,
                'total_participants' => $totalParticipants,
                'pending_payments' => $pendingPayments,
                'success_rate' => $successRate,
                'avg_rating' => $avgRating,
                'completed_classes' => $completedClasses,
                'upcoming_classes' => $upcomingClasses,
                'total_classes' => $totalClasses,
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getPicStats: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Get PIC training calendar
     */
    private function getPicTrainingCalendar(int $selectedYear): array
    {
        try {
            // Get programs where user is PIC
            $programIds = Program::where('user_id', $this->user->id)->pluck('id')->toArray();
            
            if (empty($programIds)) {
                return [];
            }
            
            $classes = Classes::whereIn('program_id', $programIds)
                ->whereYear('start_date', $selectedYear)
                ->with(['programs'])
                ->get();
            
            $calendar = [];
            foreach ($classes as $class) {
                $startDate = Carbon::parse($class->start_date);
                $endDate = Carbon::parse($class->end_date);
                
                // Determine class status color
                $backgroundColor = $this->getClassStatusColor($class);
                
                $calendar[] = [
                    'id' => $class->id,
                    'title' => $class->class_name,
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->addDay()->toDateString(),
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $backgroundColor,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'program_name' => $class->programs->program_name ?? 'Unknown Program',
                        'batch' => $class->class_batch,
                        'location' => $class->class_loc,
                        'participants_count' => $this->getClassParticipantsCount($class->id),
                        'status' => $this->getClassStatus($class)
                    ]
                ];
            }
            
            return $calendar;
            
        } catch (\Exception $e) {
            \Log::error('Error in getPicTrainingCalendar: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get PIC tasks and reminders
     */
    private function getPicTasks(): array
    {
        try {
            $tasks = [];
            
            // Get programs where user is PIC
            $programIds = Program::where('user_id', $this->user->id)->pluck('id')->toArray();
            
            if (empty($programIds)) {
                return [];
            }
            
            // Task 1: Pending payment approvals
            $pendingPayments = payments::whereIn('program_id', $programIds)
                ->where('status', 'Check by PIC')
                ->count();
                
            $pendingParticipantPayments = ParticipantsPayment::whereIn('program_id', $programIds)
                ->where('status', 'Check by PIC')
                ->count();
            
            $totalPendingPayments = $pendingPayments + $pendingParticipantPayments;
            
            if ($totalPendingPayments > 0) {
                $tasks[] = [
                    'id' => 'payment_approval',
                    'title' => 'Payment Approvals',
                    'description' => "{$totalPendingPayments} payments waiting for your approval",
                    'priority' => 'high',
                    'due_date' => 'ASAP',
                    'type' => 'payment'
                ];
            }
            
            // Task 2: Upcoming classes preparation
            $upcomingClasses = Classes::whereIn('program_id', $programIds)
                ->where('start_date', '>', now())
                ->where('start_date', '<=', now()->addDays(7))
                ->count();
                
            if ($upcomingClasses > 0) {
                $tasks[] = [
                    'id' => 'class_preparation',
                    'title' => 'Class Preparation',
                    'description' => "{$upcomingClasses} classes starting within 7 days",
                    'priority' => 'medium',
                    'due_date' => 'This week',
                    'type' => 'preparation'
                ];
            }
            
            // Task 3: Missing participant confirmations
            $classesWithPendingParticipants = Classes::whereIn('program_id', $programIds)
                ->where('start_date', '>', now())
                ->whereHas('participantsTemp', function($query) {
                    $query->where('status', 'Invited');
                })
                ->count();
                
            if ($classesWithPendingParticipants > 0) {
                $tasks[] = [
                    'id' => 'participant_confirmation',
                    'title' => 'Participant Confirmations',
                    'description' => "{$classesWithPendingParticipants} classes have unconfirmed participants",
                    'priority' => 'medium',
                    'due_date' => 'Before class starts',
                    'type' => 'participants'
                ];
            }
            
            // Task 4: Classes without agenda
            $classesWithoutAgenda = Classes::whereIn('program_id', $programIds)
                ->where('start_date', '>', now())
                ->whereDoesntHave('agenda')
                ->count();
                
            if ($classesWithoutAgenda > 0) {
                $tasks[] = [
                    'id' => 'missing_agenda',
                    'title' => 'Missing Training Agenda',
                    'description' => "{$classesWithoutAgenda} upcoming classes need training agenda",
                    'priority' => 'low',
                    'due_date' => 'Before class starts',
                    'type' => 'agenda'
                ];
            }
            
            return $tasks;
            
        } catch (\Exception $e) {
            \Log::error('Error in getPicTasks: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get PIC recent activities
     */
    private function getPicActivities(): array
    {
        try {
            $activities = [];
            
            // Get programs where user is PIC
            $programIds = Program::where('user_id', $this->user->id)->pluck('id')->toArray();
            
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
    
    /**
     * Get PIC classes with detailed information
     */
    private function getPicClasses(int $selectedYear): array
    {
        try {
            // Get programs where user is PIC
            $programIds = Program::where('user_id', $this->user->id)->pluck('id')->toArray();
            
            if (empty($programIds)) {
                return [];
            }
            
            $classes = Classes::whereIn('program_id', $programIds)
                ->whereYear('start_date', $selectedYear)
                ->with(['programs'])
                ->orderBy('start_date', 'desc')
                ->get();
            
            $classesData = [];
            foreach ($classes as $class) {
                $participantsCount = $this->getClassParticipantsCount($class->id);
                $status = $this->getClassStatus($class);
                
                $classesData[] = [
                    'id' => $class->id,
                    'program_name' => $class->programs->program_name ?? 'Unknown Program',
                    'class_name' => $class->class_name,
                    'batch' => $class->class_batch,
                    'start_date' => $class->start_date,
                    'end_date' => $class->end_date,
                    'location' => $class->class_loc ?? 'TBD',
                    'participants_count' => $participantsCount,
                    'status' => $status,
                    'program_id' => $class->program_id
                ];
            }
            
            return $classesData;
            
        } catch (\Exception $e) {
            \Log::error('Error in getPicClasses: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get class details for modal
     */
    public function getClassDetails(int $classId): array
    {
        try {
            $class = Classes::with([
                'programs.user',
                'participants',
                'participantsTemp',
                'agenda'
            ])->findOrFail($classId);
            
            // Verify PIC access
            if ($class->programs->user_id !== $this->user->id) {
                throw new \Exception('Access denied: You are not the PIC for this class');
            }
            
            $participants = $class->participants;
            $tempParticipants = $class->participantsTemp;
            
            $participantStats = [
                'total_participants' => $participants->count() + $tempParticipants->count(),
                'present' => $participants->where('status', 'Present')->count(),
                'absent' => $participants->where('status', '!=', 'Present')->where('status', '!=', 'Invited')->count(),
                'pending' => $tempParticipants->where('status', 'Invited')->count(),
                'attendance_rate' => 0
            ];
            
            if ($participantStats['total_participants'] > 0) {
                $participantStats['attendance_rate'] = round(
                    ($participantStats['present'] / $participantStats['total_participants']) * 100, 
                    2
                );
            }
            
            // Get payment stats for this class
            $paymentStats = $this->getClassPaymentStats($class->program_id);
            
            return [
                'class' => $class,
                'stats' => array_merge($participantStats, ['payments' => $paymentStats]),
                'agenda' => $class->agenda ? $class->agenda->toArray() : []
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getClassDetails: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get class participants for management
     */
    public function getClassParticipants(int $classId): array
    {
        try {
            $class = Classes::with(['programs', 'participants', 'participantsTemp'])->findOrFail($classId);
            
            // Verify PIC access
            if ($class->programs->user_id !== $this->user->id) {
                throw new \Exception('Access denied: You are not the PIC for this class');
            }
            
            $participants = [];
            
            // Add final participants
            foreach ($class->participants as $participant) {
                $participants[] = [
                    'id' => $participant->id,
                    'name' => $participant->participant_name,
                    'email' => $participant->participant_email ?? 'N/A',
                    'nik' => $participant->karyawan_nik,
                    'status' => $participant->status,
                    'type' => 'final',
                    'pre_test' => $participant->pre_test,
                    'post_test' => $participant->post_test,
                    'created_at' => $participant->created_at
                ];
            }
            
            // Add temp participants
            foreach ($class->participantsTemp as $participant) {
                $participants[] = [
                    'id' => $participant->id,
                    'name' => $participant->participant_name,
                    'email' => $participant->participant_email ?? 'N/A',
                    'nik' => $participant->karyawan_nik,
                    'status' => $participant->status,
                    'type' => 'temp',
                    'pre_test' => $participant->pre_test,
                    'post_test' => $participant->post_test,
                    'created_at' => $participant->created_at
                ];
            }
            
            return [
                'class' => $class,
                'participants' => $participants
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getClassParticipants: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get class payments for management
     */
    public function getClassPayments(int $classId): array
    {
        try {
            $class = Classes::with(['programs'])->findOrFail($classId);
            
            // Verify PIC access
            if ($class->programs->user_id !== $this->user->id) {
                throw new \Exception('Access denied: You are not the PIC for this class');
            }
            
            $payments = [];
            
            // Get general payments for this program
            $generalPayments = payments::where('program_id', $class->program_id)->get();
            foreach ($generalPayments as $payment) {
                $payments[] = [
                    'id' => $payment->id,
                    'type' => 'general',
                    'description' => $payment->description ?? 'General Payment',
                    'amount' => $payment->total_transfer ?? $payment->amount_fee,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at
                ];
            }
            
            // Get participant payments for this program
            $participantPayments = ParticipantsPayment::where('program_id', $class->program_id)
                ->with(['user'])
                ->get();
            foreach ($participantPayments as $payment) {
                $payments[] = [
                    'id' => $payment->id,
                    'type' => 'participant',
                    'description' => 'Participant Payment - ' . ($payment->user->name ?? 'Unknown'),
                    'amount' => $payment->amount_fee,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at
                ];
            }
            
            // Sort by created_at desc
            usort($payments, function($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });
            
            return [
                'class' => $class,
                'payments' => $payments
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getClassPayments: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // Private helper methods
    
    private function getDefaultStats(): array
    {
        return [
            'total_programs' => 0,
            'active_classes' => 0,
            'total_participants' => 0,
            'pending_payments' => 0,
            'success_rate' => 0,
            'avg_rating' => 0,
            'completed_classes' => 0,
            'upcoming_classes' => 0,
            'total_classes' => 0,
        ];
    }
    
    private function getClassParticipantsCount(int $classId): int
    {
        $finalCount = Participant::where('class_id', $classId)->count();
        $tempCount = ParticipantsTemp::where('class_id', $classId)->count();
        return $finalCount + $tempCount;
    }
    
    private function getClassStatus($class): string
    {
        $now = now();
        $startDate = Carbon::parse($class->start_date);
        $endDate = Carbon::parse($class->end_date);
        
        if ($endDate < $now) {
            return 'completed';
        } elseif ($startDate <= $now && $endDate >= $now) {
            return 'ongoing';
        } else {
            return 'upcoming';
        }
    }
    
    private function getClassStatusColor($class): string
    {
        $status = $this->getClassStatus($class);
        
        switch ($status) {
            case 'completed':
                return '#28a745'; // Green
            case 'ongoing':
                return '#ffc107'; // Yellow
            case 'upcoming':
                return '#007bff'; // Blue
            default:
                return '#6c757d'; // Gray
        }
    }
    
    private function calculateAverageRating(array $classIds): float
    {
        // Mock implementation - replace with actual feedback/rating system
        // For now, return a random rating between 4.0-5.0
        if (empty($classIds)) {
            return 0;
        }
        
        // This could be calculated from a feedback/rating table
        return round(4.0 + (mt_rand(0, 100) / 100), 1);
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
    
    private function getClassPaymentStats(int $programId): array
    {
        $generalPayments = payments::where('program_id', $programId)->get();
        $participantPayments = ParticipantsPayment::where('program_id', $programId)->get();
        
        $approved = $generalPayments->where('status', 'Approve')->count() + 
                   $participantPayments->where('status', 'Approve')->count();
        
        $pending = $generalPayments->where('status', 'Pending')->count() + 
                  $participantPayments->where('status', 'Pending')->count();
        
        $review = $generalPayments->whereIn('status', ['Check by PIC', 'Check by Manager'])->count() + 
                 $participantPayments->whereIn('status', ['Check by PIC', 'Check by Manager'])->count();
        
        return [
            'approved' => $approved,
            'pending' => $pending,
            'review' => $review
        ];
    }
    
    private function getDefaultData(): array
    {
        return [
            'picStats' => $this->getDefaultStats(),
            'picTrainingCalendar' => [],
            'picTasks' => [],
            'picActivities' => [],
            'picClasses' => [],
        ];
    }
}