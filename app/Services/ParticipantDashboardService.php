<?php

namespace App\Services;
use App\Models\Classes;
use App\Models\Participant;
use App\Models\ParticipantsPayment;
use App\Models\ParticipantsTemp;
use App\Models\Program;
use Carbon\Carbon;


class ParticipantDashboardService
{
    private $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function getDashboardData(): array
    {
        try {
            \Log::info('Getting dashboard data for user: ' . $this->user->id);
            
            $classData = $this->getClassData();
            $participantRecords = $this->getParticipantRecords();
            
            \Log::info('Class data count: ' . $classData['allClasses']->count());
            \Log::info('Participant records count: ' . $participantRecords['participant']->count());
            \Log::info('Temp records count: ' . $participantRecords['temp']->count());
            
            return [
                'myStats' => $this->calculateStats($classData, $participantRecords),
                'myAttendanceStats' => $this->getAttendanceStats($participantRecords), // NEW
                'myUpcomingClasses' => $this->getUpcomingClasses($classData['allClasses']),
                'myProgress' => $this->getLearningProgress($classData['classIds'], $participantRecords['participant']),
                'myTrainingCalendar' => $this->getTrainingCalendar($classData['allClasses'], $participantRecords),
                'myRecentActivity' => $this->getRecentActivity($classData['allClasses'], $participantRecords),
                'myTransactionStats' => $this->getTransactionStats($classData['programIds'])
            ];
        } catch (\Exception $e) {
            \Log::error('Participant Dashboard Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->getDefaultData();
        }
    }
    
    private function getClassData(): array
    {
        try {
            // 🔧 PERBAIKAN: Cari user di tabel participants berdasarkan karyawan_nik atau user_id
            $participantClasses = collect();
            $invitedClasses = collect();
            
            // Cari berdasarkan user_id di participants table
            $userParticipants = Participant::where('user_id', $this->user->id)
                ->with(['classes.programs'])
                ->get();
            
            if ($userParticipants->isNotEmpty()) {
                $participantClasses = $userParticipants->pluck('classes')->filter();
                \Log::info('Found participant classes by user_id: ' . $participantClasses->count());
            }
            
            // Cari berdasarkan user_id di participants_temp table
            $userTempParticipants = ParticipantsTemp::where('user_id', $this->user->id)
                ->with(['classes.programs'])
                ->get();
            
            if ($userTempParticipants->isNotEmpty()) {
                $invitedClasses = $userTempParticipants->pluck('classes')->filter();
                \Log::info('Found temp classes by user_id: ' . $invitedClasses->count());
            }
            
            // 🔧 ALTERNATIF: Jika tidak ada data dengan user_id, coba dengan email/NIK
            if ($participantClasses->isEmpty() && $invitedClasses->isEmpty()) {
                // Cari berdasarkan email yang mirip dengan participant_name atau karyawan_nik
                $userEmail = $this->user->email;
                
                $emailParticipants = Participant::where('participant_name', 'LIKE', '%' . $userEmail . '%')
                    ->orWhere('karyawan_nik', 'LIKE', '%' . $userEmail . '%')
                    ->with(['classes.programs'])
                    ->get();
                
                if ($emailParticipants->isNotEmpty()) {
                    $participantClasses = $emailParticipants->pluck('classes')->filter();
                    \Log::info('Found participant classes by email pattern: ' . $participantClasses->count());
                }
                
                $emailTempParticipants = ParticipantsTemp::where('participant_name', 'LIKE', '%' . $userEmail . '%')
                    ->orWhere('karyawan_nik', 'LIKE', '%' . $userEmail . '%')
                    ->with(['classes.programs'])
                    ->get();
                
                if ($emailTempParticipants->isNotEmpty()) {
                    $invitedClasses = $emailTempParticipants->pluck('classes')->filter();
                    \Log::info('Found temp classes by email pattern: ' . $invitedClasses->count());
                }
            }
            
            $allClasses = $participantClasses->merge($invitedClasses)->unique('id');
            $classIds = $allClasses->pluck('id')->toArray();
            
            $programIds = Program::whereHas('classes', function($query) use ($classIds) {
                $query->whereIn('id', $classIds);
            })->pluck('id')->toArray();
            
            \Log::info('Total unique classes: ' . $allClasses->count());
            \Log::info('Program IDs: ' . implode(',', $programIds));
            
            return [
                'participantClasses' => $participantClasses,
                'invitedClasses' => $invitedClasses,
                'allClasses' => $allClasses,
                'classIds' => $classIds,
                'programIds' => $programIds
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getClassData: ' . $e->getMessage());
            return [
                'participantClasses' => collect(),
                'invitedClasses' => collect(),
                'allClasses' => collect(),
                'classIds' => [],
                'programIds' => []
            ];
        }
    }
    
    private function getParticipantRecords(): array
    {
        try {
            // 🔧 PERBAIKAN: Cari participant records dengan multiple criteria
            $participantRecords = collect();
            $tempRecords = collect();
            
            // Cari berdasarkan user_id
            $userParticipants = Participant::where('user_id', $this->user->id)
                ->with('classes')
                ->get();
            
            $userTempParticipants = ParticipantsTemp::where('user_id', $this->user->id)
                ->with('classes')
                ->get();
            
            if ($userParticipants->isNotEmpty() || $userTempParticipants->isNotEmpty()) {
                $participantRecords = $userParticipants;
                $tempRecords = $userTempParticipants;
            } else {
                // Fallback: cari berdasarkan email pattern
                $userEmail = $this->user->email;
                
                $participantRecords = Participant::where('participant_name', 'LIKE', '%' . $userEmail . '%')
                    ->orWhere('karyawan_nik', 'LIKE', '%' . $userEmail . '%')
                    ->with('classes')
                    ->get();
                    
                $tempRecords = ParticipantsTemp::where('participant_name', 'LIKE', '%' . $userEmail . '%')
                    ->orWhere('karyawan_nik', 'LIKE', '%' . $userEmail . '%')
                    ->with('classes')
                    ->get();
            }
            
            \Log::info('Found participant records: ' . $participantRecords->count());
            \Log::info('Found temp records: ' . $tempRecords->count());
            
            return [
                'participant' => $participantRecords,
                'temp' => $tempRecords
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getParticipantRecords: ' . $e->getMessage());
            return [
                'participant' => collect(),
                'temp' => collect()
            ];
        }
    }
    
    private function calculateStats(array $classData, array $participantRecords): array
    {
        try {
            $totalClassesAttended = $participantRecords['participant']
                ->where('status', 'Present')
                ->count();
                
            $totalParticipated = $participantRecords['participant']->count() + 
                               $participantRecords['temp']->count();
                               
            $attendanceRate = $totalParticipated > 0 ? 
                round(($totalClassesAttended / $totalParticipated) * 100, 2) : 0;
                
            $upcomingCount = Classes::where('start_date', '>', now())
                ->where('start_date', '<=', now()->addDays(30))
                ->whereIn('id', $classData['classIds'])
                ->count();
                
            \Log::info('Stats calculated - Attended: ' . $totalClassesAttended . ', Rate: ' . $attendanceRate . '%, Upcoming: ' . $upcomingCount);
                
            return [
                'total_classes' => $totalClassesAttended,
                'attendance_rate' => $attendanceRate,
                'upcoming_classes' => $upcomingCount,
                'certificates' => $totalClassesAttended, // Present = Certificate
            ];
        } catch (\Exception $e) {
            \Log::error('Error in calculateStats: ' . $e->getMessage());
            return [
                'total_classes' => 0,
                'attendance_rate' => 0,
                'upcoming_classes' => 0,
                'certificates' => 0
            ];
        }
    }

    // NEW METHOD: Get detailed attendance statistics
    private function getAttendanceStats(array $participantRecords): array
    {
        try {
            $finalParticipants = $participantRecords['participant'];
            $tempParticipants = $participantRecords['temp'];
            
            // Status breakdown for final participants (confirmed attendees)
            $finalStatusBreakdown = $finalParticipants->groupBy('status')->map->count();
            
            // Status breakdown for temp participants (invitations)
            $tempStatusBreakdown = $tempParticipants->groupBy('status')->map->count();
            
            // Calculate totals
            $totalInvited = $finalParticipants->count() + $tempParticipants->count();
            $totalPresent = $finalStatusBreakdown->get('Present', 0);
            $attendanceRate = $totalInvited > 0 ? round(($totalPresent / $totalInvited) * 100, 2) : 0;
            
            // Detailed status summary
            $statusSummary = [
                // Final participants (confirmed)
                'present' => $finalStatusBreakdown->get('Present', 0),
                'absent_sick' => $finalStatusBreakdown->get('Absent - Sick', 0),
                'absent_busy' => $finalStatusBreakdown->get('Absent - Busy', 0),
                'absent_maternity' => $finalStatusBreakdown->get('Absent - Maternity', 0),
                'absent_business' => $finalStatusBreakdown->get('Absent - Business', 0),
                'absent_general' => $finalStatusBreakdown->get('Absent', 0),
                
                // Temp participants (still in invitation stage)
                'invited' => $tempStatusBreakdown->get('Invited', 0),
                'temp_absent_busy' => $tempStatusBreakdown->get('Absent - Busy', 0),
                'temp_absent_general' => $tempStatusBreakdown->get('Absent', 0),
            ];
            
            \Log::info('Attendance stats calculated', $statusSummary);
            
            return [
                'total_invited' => $totalInvited,
                'total_present' => $totalPresent,
                'attendance_rate' => $attendanceRate,
                'final_participants' => [
                    'total' => $finalParticipants->count(),
                    'status_breakdown' => $finalStatusBreakdown->toArray(),
                ],
                'temp_participants' => [
                    'total' => $tempParticipants->count(),
                    'status_breakdown' => $tempStatusBreakdown->toArray(),
                ],
                'status_summary' => $statusSummary,
                'attendance_breakdown' => [
                    'attended_percentage' => $attendanceRate,
                    'not_attended_percentage' => $totalInvited > 0 ? round((($totalInvited - $totalPresent) / $totalInvited) * 100, 2) : 0,
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getAttendanceStats: ' . $e->getMessage());
            return [
                'total_invited' => 0,
                'total_present' => 0,
                'attendance_rate' => 0,
                'final_participants' => ['total' => 0, 'status_breakdown' => []],
                'temp_participants' => ['total' => 0, 'status_breakdown' => []],
                'status_summary' => [
                    'present' => 0,
                    'absent_sick' => 0,
                    'absent_busy' => 0,
                    'absent_maternity' => 0,
                    'absent_business' => 0,
                    'absent_general' => 0,
                    'invited' => 0,
                    'temp_absent_busy' => 0,
                    'temp_absent_general' => 0,
                ],
                'attendance_breakdown' => [
                    'attended_percentage' => 0,
                    'not_attended_percentage' => 0,
                ]
            ];
        }
    }
    
    private function getUpcomingClasses($allClasses): array
    {
        try {
            if ($allClasses->isEmpty()) {
                \Log::info('No classes available for upcoming check');
                return [];
            }
            
            $upcomingClasses = Classes::where('start_date', '>', now())
                ->where('start_date', '<=', now()->addDays(30))
                ->whereIn('id', $allClasses->pluck('id'))
                ->with(['programs'])
                ->orderBy('start_date')
                ->get();
                
            \Log::info('Found upcoming classes: ' . $upcomingClasses->count());
                
            return $upcomingClasses->map(function ($class) {
                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'program_name' => $class->programs->program_name ?? 'Unknown Program',
                    'start_date' => $class->start_date,
                    'end_date' => $class->end_date,
                    'location' => $class->class_loc ?? 'TBD',
                    'class_batch' => $class->class_batch,
                    'status' => $this->getUserStatusForClass($class->id, [
                        'participant' => Participant::where('user_id', $this->user->id)->get(),
                        'temp' => ParticipantsTemp::where('user_id', $this->user->id)->get()
                    ])['status']
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Error in getUpcomingClasses: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getLearningProgress(array $classIds, $participantRecords): array
    {
        try {
            if (empty($classIds)) {
                return [];
            }
            
            $programCategories = Program::whereHas('classes', function($query) use ($classIds) {
                $query->whereIn('id', $classIds);
            })->with(['category_budget'])->get()
            ->groupBy(function($program) {
                return $program->category_budget->first()->name ?? 'Uncategorized';
            });
            
            $progress = [];
            foreach ($programCategories as $categoryName => $programs) {
                $programIds = $programs->pluck('id')->toArray();
                $categoryClassIds = Classes::whereIn('program_id', $programIds)->pluck('id')->toArray();
                
                $totalClasses = count($categoryClassIds);
                $completedClasses = $participantRecords
                    ->whereIn('class_id', $categoryClassIds)
                    ->where('status', 'Present')
                    ->count();
                    
                $percentage = $totalClasses > 0 ? 
                    round(($completedClasses / $totalClasses) * 100, 2) : 0;
                    
                $progress[$categoryName] = [
                    'total' => $totalClasses,
                    'completed' => $completedClasses,
                    'percentage' => $percentage
                ];
            }
            
            return $progress;
        } catch (\Exception $e) {
            \Log::error('Error in getLearningProgress: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getTrainingCalendar($allClasses, array $participantRecords): array
    {
        try {
            if ($allClasses->isEmpty()) {
                \Log::info('No classes for calendar');
                return [];
            }
            
            $calendar = $allClasses->map(function ($class) use ($participantRecords) {
                $status = $this->getUserStatusForClass($class->id, $participantRecords);
                
                return [
                    'id' => $class->id,
                    'title' => $class->class_name,
                    'start' => $class->start_date,
                    'end' => Carbon::parse($class->end_date)->addDay()->toDateString(),
                    'backgroundColor' => $this->getStatusColor($status['status']),
                    'borderColor' => $this->getStatusColor($status['status']),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'program_name' => $class->programs->program_name ?? 'Unknown Program',
                        'batch' => $class->class_batch,
                        'location' => $class->class_loc,
                        'status' => $status['status'],
                        'source' => $status['source'],
                        'description' => "Status: {$status['status']}"
                    ]
                ];
            })->toArray();
            
            \Log::info('Generated calendar events: ' . count($calendar));
            return $calendar;
        } catch (\Exception $e) {
            \Log::error('Error in getTrainingCalendar: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getRecentActivity($allClasses, array $participantRecords): array
    {
        try {
            if ($allClasses->isEmpty()) {
                return [];
            }
            
            $recentClasses = Classes::whereIn('id', $allClasses->pluck('id'))
                ->where('end_date', '<=', now())
                ->orderBy('end_date', 'desc')
                ->limit(10)
                ->with(['programs'])
                ->get();
                
            return $recentClasses->map(function ($class) use ($participantRecords) {
                $status = $this->getUserStatusForClass($class->id, $participantRecords);
                
                return [
                    'class_id' => $class->id,
                    'date' => $class->end_date,
                    'class_name' => $class->class_name,
                    'program_name' => $class->programs->program_name ?? 'Unknown Program',
                    'status' => $status['status'],
                    'location' => $class->class_loc ?? 'TBD',
                    'pre_test' => $status['pre_test'],
                    'post_test' => $status['post_test'],
                    'feedback_given' => false,
                    'certificate_available' => $status['status'] === 'Present'
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Error in getRecentActivity: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getTransactionStats(array $programIds): array
{
    try {
        // ✅ Ambil semua participant id milik user login
        $participantIds = Participant::where('user_id', $this->user->id)
            ->pluck('id');

        if ($participantIds->isEmpty()) {
            return [
                'pending' => 0,
                'in_review' => 0,
                'accepted' => 0,
                'rejected' => 0,
                'total_amount' => 0
            ];
        }

        // ✅ Ambil semua pembayaran berdasarkan participants_id
        $participantPayments = ParticipantsPayment::whereIn('participants_id', $participantIds)
            ->whereIn('program_id', $programIds) // filter program kalau perlu
            ->get();

        return [
            'pending' => $participantPayments->where('status', 'Pending')->count(),
            'in_review' => $participantPayments->whereIn('status', ['Check by PIC', 'Check by Manager'])->count(),
            'accepted' => $participantPayments->where('status', 'Approve')->count(),
            'rejected' => $participantPayments->where('status', 'Reject')->count(),
            'total_amount' => $participantPayments->sum('amount_fee')
        ];
    } catch (\Exception $e) {
        \Log::error('Error in getTransactionStats: ' . $e->getMessage());
        return [
            'pending' => 0,
            'in_review' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'total_amount' => 0
        ];
    }
}

    
    private function getUserStatusForClass(int $classId, array $participantRecords): array
    {
        $participantRecord = $participantRecords['participant']->where('class_id', $classId)->first();
        $tempRecord = $participantRecords['temp']->where('class_id', $classId)->first();
        
        if ($participantRecord) {
            return [
                'status' => $participantRecord->status,
                'source' => 'participant',
                'pre_test' => $participantRecord->pre_test,
                'post_test' => $participantRecord->post_test
            ];
        }
        
        if ($tempRecord) {
            return [
                'status' => $tempRecord->status,
                'source' => 'temp',
                'pre_test' => $tempRecord->pre_test,
                'post_test' => $tempRecord->post_test
            ];
        }
        
        return [
            'status' => 'Unknown',
            'source' => 'unknown',
            'pre_test' => null,
            'post_test' => null
        ];
    }
    
    private function getStatusColor(string $status): string
    {
        $colors = [
            'Present' => '#28a745',
            'Absent' => '#dc3545',
            'Pending' => '#ffc107',
            'Invited' => '#17a2b8',
            'Unknown' => '#6c757d'
        ];
        
        return $colors[$status] ?? '#6c757d';
    }
    
    private function getDefaultData(): array
    {
        return [
            'myStats' => [
                'total_classes' => 0,
                'attendance_rate' => 0,
                'upcoming_classes' => 0,
                'certificates' => 0,
            ],
            'myAttendanceStats' => [
                'total_invited' => 0,
                'total_present' => 0,
                'attendance_rate' => 0,
                'status_summary' => [
                    'present' => 0,
                    'absent_sick' => 0,
                    'absent_busy' => 0,
                    'absent_maternity' => 0,
                    'absent_business' => 0,
                    'absent_general' => 0,
                    'invited' => 0,
                    'temp_absent_busy' => 0,
                    'temp_absent_general' => 0,
                ],
                'attendance_breakdown' => [
                    'attended_percentage' => 0,
                    'not_attended_percentage' => 0,
                ]
            ],
            'myUpcomingClasses' => [],
            'myProgress' => [],
            'myTrainingCalendar' => [],
            'myRecentActivity' => [],
            'myTransactionStats' => [
                'pending' => 0,
                'in_review' => 0,
                'accepted' => 0,
                'rejected' => 0,
                'total_amount' => 0
            ]
        ];
    }
}