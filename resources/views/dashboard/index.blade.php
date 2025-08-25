@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
            <div class="mb-0">
                <h2 class="text-center text-primary fw-bold" style="margin-top: 10px">Welcome back,
                    {{ Auth::user()->email ?? 'User' }}!
                </h2>
            </div>

            <!-- Year Filter -->
            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('dashboard.index') }}" class="d-flex align-items-center gap-2"
                    id="filterForm">
                    <label for="yearFilter" class="form-label mb-0 text-sm font-weight-bold text-gray-600">Year:</label>
                    <select name="year" class="form-select form-select-sm" id="yearFilter" style="min-width: 100px;">
                        @for ($year = date('Y'); $year >= 2015; $year--)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                    <div id="filterLoading" class="d-none">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100 clickable-card"
                    onclick="navigateToPage('{{ route('classes.index') }}')">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-uppercase text-muted small font-weight-bold mb-2">
                                    Total Classes
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalClasses">
                                    {{ $overviewStats['total_classes'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-primary text-white rounded-circle">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100 clickable-card"
                    onclick="navigateToPage('{{ route('program.index') }}')">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-uppercase text-muted small font-weight-bold mb-2">
                                    Total Programs
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalPrograms">
                                    {{ $overviewStats['total_programs'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-success text-white rounded-circle">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100 clickable-card"
                    onclick="navigateToPage('{{ route('classes.index') }}?upcoming=1')">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-uppercase text-muted small font-weight-bold mb-2">
                                    Upcoming Classes
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800" id="upcomingClasses">
                                    {{ $overviewStats['upcoming_classes'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-info text-white rounded-circle">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100 clickable-card"
                    onclick="navigateToPage('{{ route('participants.index') }}')">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-uppercase text-muted small font-weight-bold mb-2">
                                    Total Participants
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalParticipants">
                                    {{ $participantStats['total_participants'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-warning text-white rounded-circle">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-4">
            <!-- TNA Realization -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4 mt-8 clickable-card"
                    onclick="navigateToPage('{{ route('tna.index') }}')">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar me-2"></i>TNA Realization
                        </h6>
                    </div>
                    <div class="card-body" id="tnaStatsContent">
                        @if (isset($tnaRealizationStats) && !empty($tnaRealizationStats['overall']))
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small font-weight-bold">Overall Progress</span>
                                    <span class="small font-weight-bold text-info">
                                        {{ $tnaRealizationStats['overall']['overall_percentage'] }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $tnaRealizationStats['overall']['overall_percentage'] }}%"
                                        id="tnaProgressBar">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 small">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Total Budget</span>
                                        <span class="font-weight-bold" id="totalBudget">
                                            Rp
                                            {{ number_format($tnaRealizationStats['overall']['total_min_budget'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Realization</span>
                                        <span class="font-weight-bold text-info" id="totalRealization">
                                            Rp
                                            {{ number_format($tnaRealizationStats['overall']['total_realization'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Remaining</span>
                                        <span class="font-weight-bold text-success" id="totalRemaining">
                                            Rp
                                            {{ number_format($tnaRealizationStats['overall']['total_remaining'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-2x text-gray-300 mb-2"></i>
                                <p class="text-muted mb-1">No TNA data available for {{ $selectedYear ?? date('Y') }}
                                </p>
                                <small class="text-muted">TNA statistics will appear when data is available</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Overview -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4 mt-8 clickable-card"
                    onclick="navigateToPage('{{ route('payments.index') }}')">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 font-weight-bold text-primary">
                            <i class="fas fa-credit-card me-2"></i>Payment Overview
                        </h6>
                    </div>
                    <div class="card-body" id="paymentStatsContent">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Amount</span>
                                <span class="h5 font-weight-bold text-primary mb-0" id="totalAmount">
                                    Rp
                                    {{ number_format($paymentStats['combined_stats']['total_amount'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <div class="small text-success font-weight-bold mb-1" id="approvedPayments">
                                        {{ $paymentStats['combined_stats']['approve']['count'] }}
                                    </div>
                                    <div class="small text-success">Approved</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                    <div class="small text-warning font-weight-bold mb-1" id="pendingPayments">
                                        {{ $paymentStats['combined_stats']['pending']['count'] }}
                                    </div>
                                    <div class="small text-warning">Pending</div>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                    <div class="small text-primary font-weight-bold mb-1" id="pendingPayments">
                                        {{ $paymentStats['combined_stats']['check_by_pic']['count'] + $paymentStats['combined_stats']['check_by_manager']['count'] }}
                                    </div>
                                    <div class="small text-primary">Under Review</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Calendar Section -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 font-weight-bold text-primary">Training Calendar</h5>
                                <small class="text-muted">View and manage your training schedule</small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="changeCalendarView('dayGridMonth')">
                                    <i class="fas fa-th me-1"></i>Month
                                </button>
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="changeCalendarView('timeGridWeek')">
                                    <i class="fas fa-calendar-week me-1"></i>Week
                                </button>
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="changeCalendarView('listWeek')">
                                    <i class="fas fa-list me-1"></i>List
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="calendar" style="min-height: 500px;"></div>
                    </div>
                </div>


            </div>

            <!-- Statistics Sidebar -->
            <div class="col-xl-4 col-lg-5">
                <!-- Participant Statistics -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 font-weight-bold text-primary">Participant Statistics</h6>
                                <small class="text-muted">Total Invited:
                                    {{ $participantStats['total_invited'] ?? 0 }}</small>
                            </div>
                            <div class="text-end">
                                <span class="h6 text-success mb-0">{{ $participantStats['attendance_rate'] ?? 0 }}%</span>
                                <small class="text-muted d-block">Attendance</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-3" id="participantStatsContent">
                        @if (($participantStats['total_invited'] ?? 0) > 0)
                            <!-- Summary Cards - More Compact -->
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                        <div class="h6 mb-0 text-info font-weight-bold">
                                            {{ $participantStats['total_invited'] ?? 0 }}</div>
                                        <div class="small text-info">Invited</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                        <div class="h6 mb-0 text-success font-weight-bold">
                                            {{ $participantStats['summary_totals']['total_present'] ?? 0 }}</div>
                                        <div class="small text-success">Present</div>
                                        <div class="small text-muted">
                                            {{ $participantStats['attendance_breakdown']['attended_percentage'] ?? 0 }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                                        <div class="h6 mb-0 text-danger font-weight-bold">
                                            {{ $participantStats['summary_totals']['total_absent'] ?? 0 }}</div>
                                        <div class="small text-danger">Absent</div>
                                        <div class="small text-muted">
                                            {{ ($participantStats['summary_totals']['total_absent'] ?? 0) > 0 && ($participantStats['total_invited'] ?? 0) > 0 ? round(($participantStats['summary_totals']['total_absent'] / $participantStats['total_invited']) * 100, 2) : 0 }}%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Breakdown - Compact Grid -->
                            <div class="mb-0">
                                <h6 class="font-weight-bold text-dark mb-2 small">Status Breakdown</h6>

                                <!-- Compact Status Items -->
                                <div class="row g-1">
                                    <div class="col-6">
                                        <div
                                            class="d-flex justify-content-between align-items-center px-2 py-1 bg-success rounded-pill small text-white">
                                            <span><i class="fas fa-check fa-xs me-1"></i>Present</span>
                                            <span
                                                class="badge bg-white text-success small">{{ $participantStats['status_summary']['present'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div
                                            class="d-flex justify-content-between align-items-center px-2 py-1 bg-info rounded-pill small text-white">
                                            <span><i class="fas fa-envelope fa-xs me-1"></i>Invited</span>
                                            <span
                                                class="badge bg-white text-info small">{{ $participantStats['status_summary']['invited'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-1">
                                        <div
                                            class="d-flex justify-content-between align-items-center px-2 py-1 bg-warning rounded-pill small text-white">
                                            <span><i class="fas fa-briefcase fa-xs me-1"></i>Busy</span>
                                            <span
                                                class="badge bg-white text-warning small">{{ $participantStats['status_summary']['absent_busy'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-1">
                                        <div
                                            class="d-flex justify-content-between align-items-center px-2 py-1 bg-secondary rounded-pill small text-white">
                                            <span><i class="fas fa-heart fa-xs me-1"></i>Sick</span>
                                            <span
                                                class="badge bg-white text-secondary small">{{ $participantStats['status_summary']['absent_sick'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    @if (($participantStats['status_summary']['absent_business'] ?? 0) > 0)
                                        <div class="col-6 mt-1">
                                            <div
                                                class="d-flex justify-content-between align-items-center px-2 py-1 bg-primary rounded-pill small text-white">
                                                <span><i class="fas fa-building fa-xs me-1"></i>Business</span>
                                                <span
                                                    class="badge bg-white text-primary small">{{ $participantStats['status_summary']['absent_business'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if (($participantStats['status_summary']['absent_maternity'] ?? 0) > 0)
                                        <div class="col-6 mt-1">
                                            <div
                                                class="d-flex justify-content-between align-items-center px-2 py-1 bg-danger rounded-pill small text-white">
                                                <span><i class="fas fa-baby fa-xs me-1"></i>Maternity</span>
                                                <span
                                                    class="badge bg-white text-danger small">{{ $participantStats['status_summary']['absent_maternity'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Compact Empty State -->
                            <div class="text-center py-3">
                                <i class="fas fa-users fa-lg text-gray-300 mb-2"></i>
                                <h6 class="text-muted font-weight-bold mb-1 small">No participants found</h6>
                                <p class="text-muted small mb-2">Participants will appear once invited to classes.</p>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshParticipantStats()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bell"></i> Recent Activities
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @if (isset($recentActivities) && count($recentActivities) > 0)
                                @foreach ($recentActivities as $activity)
                                    <div class="timeline-item mb-3">
                                        <div
                                            class="timeline-marker bg-{{ $activity['type'] == 'success' ? 'success' : ($activity['type'] == 'warning' ? 'warning' : 'info') }}">
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                            <p class="small mb-1">{{ $activity['description'] }}</p>
                                            <small class="text-muted">{{ $activity['time'] }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p class="small">No recent activities</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!-- Class Details Modal -->
        <div class="modal fade" id="classModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Class Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="classDetails"></div>
                    </div>
                </div>
            </div>
        </div>
    @endsection


    <style>
        /* Clickable Card Styles */
        .clickable-card {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .clickable-card:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 0.75rem 3rem rgba(0, 0, 0, 0.15) !important;
            background: linear-gradient(145deg, #ffffff, #f8f9fc);
        }

        .clickable-card:active {
            transform: translateY(-1px) scale(1.01);
            transition: all 0.1s ease;
        }

        .clickable-card .icon-shape {
            transition: all 0.2s ease;
        }

        .clickable-card:hover .icon-shape {
            transform: scale(1.1);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
        }

        .clickable-card .text-gray-800 {
            transition: color 0.2s ease;
        }

        .clickable-card:hover .text-gray-800 {
            color: #4e73df !important;
        }

        /* Custom Icon Shapes */
        .icon {
            width: 3rem;
            height: 3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .icon-shape {
            padding: 12px;
            text-align: center;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Card Improvements */
        .card {
            border: none;
            border-radius: 0.75rem;
            transition: all 0.15s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 1.25rem;
        }

        /* Calendar Styles */
        .fc {
            font-family: inherit !important;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #dee2e6 !important;
        }

        .fc-button-primary {
            background-color: #4e73df !important;
            border-color: #4e73df !important;
        }

        .fc-button-primary:hover {
            background-color: #2e59d9 !important;
            border-color: #2e59d9 !important;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
        }

        /* Progress Bars */
        .progress {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 0.5rem;
        }

        /* Legend for chart */
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
        }

        /* Loading States */
        .calendar-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }

        .calendar-loading::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4e73df;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 1000;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Background Opacity Classes */
        .bg-opacity-10 {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
        }

        .bg-success.bg-opacity-10 {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(var(--bs-danger-rgb), 0.1) !important;
        }

        .bg-info.bg-opacity-10 {
            background-color: rgba(var(--bs-info-rgb), 0.1) !important;
        }

        .bg-warning.bg-opacity-10 {
            background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .fc-toolbar {
                flex-direction: column;
                gap: 10px;
            }

            .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
            }

            .card-body {
                padding: 1rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .h3 {
                font-size: 1.5rem;
            }

            .h4 {
                font-size: 1.25rem;
            }
        }

        /* Utility Classes */
        .gap-2 {
            gap: 0.5rem !important;
        }

        .gap-3 {
            gap: 1rem !important;
        }

        .gap-4 {
            gap: 1.5rem !important;
        }

        /* Text Colors */
        .text-gray-300 {
            color: #d1d3e2 !important;
        }

        .text-gray-600 {
            color: #858796 !important;
        }

        .text-gray-800 {
            color: #5a5c69 !important;
        }
    </style>



    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let calendar;
        let currentYear = {{ $selectedYear }};
        let classesAttendanceChart;

        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            initializeClassesAttendanceChart();
            setupFilterHandlers();
        });

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: '' // Remove default buttons since we have custom ones
                },
                events: {
                    url: '{{ route('dashboard.calendar-events') }}',
                    extraParams: () => ({
                        year: currentYear
                    }),
                    failure: () => showAlert('error', 'Failed to load calendar events.')
                },
                eventClick: info => showClassDetails(info.event.id),
                height: 'auto',
                contentHeight: 500,
                eventDisplay: 'block',
                dayMaxEvents: 3,
                moreLinkClick: 'popover',
                loading: isLoading => {
                    calendarEl.classList.toggle('calendar-loading', isLoading);
                },
                initialDate: `${currentYear}-01-01`
            });

            calendar.render();
        }

        function initializeClassesAttendanceChart() {
            const ctx = document.getElementById('classesAttendanceChart');
            if (!ctx || {{ ($participantStats['total_participants'] ?? 0) === 0 ? 'true' : 'false' }}) return;

            // Load initial data
            loadClassesAttendanceData();
        }

        function loadClassesAttendanceData() {
            const params = new URLSearchParams({
                year: currentYear,
                classes_attendance_chart: 1
            });

            fetch(`{{ route('dashboard.index') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.classesAttendanceChart && data.classesAttendanceChart.length > 0) {
                        renderClassesAttendanceChart(data.classesAttendanceChart);
                    } else {
                        // If no data, show empty state
                        const canvas = document.getElementById('classesAttendanceChart');
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        // Draw "No Data" message
                        ctx.font = '16px Arial';
                        ctx.fillStyle = '#6c757d';
                        ctx.textAlign = 'center';
                        ctx.fillText('No class data available', canvas.width / 2, canvas.height / 2);
                    }
                })
                .catch(error => {
                    console.error('Error loading classes attendance data:', error);
                });
        }

        function renderClassesAttendanceChart(classesData) {
            const ctx = document.getElementById('classesAttendanceChart');

            // Destroy existing chart if it exists
            if (classesAttendanceChart) {
                classesAttendanceChart.destroy();
            }

            // Prepare data for stacked bar chart
            const labels = classesData.map(classData => {
                // Truncate long class names for better display
                const className = classData.class_name.length > 15 ?
                    classData.class_name.substring(0, 15) + '...' :
                    classData.class_name;
                return `${className}\n(${classData.start_date})`;
            });

            const datasets = [{
                    label: 'Present',
                    data: classesData.map(classData => classData.attendance.present || 0),
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Invited',
                    data: classesData.map(classData => classData.attendance.invited || 0),
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    borderWidth: 1
                },
                {
                    label: 'Absent - Busy',
                    data: classesData.map(classData => classData.attendance.absent_busy || 0),
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    borderWidth: 1
                },
                {
                    label: 'Absent - General',
                    data: classesData.map(classData => classData.attendance.absent_general || 0),
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    borderWidth: 1
                },
                {
                    label: 'Absent - Sick',
                    data: classesData.map(classData => classData.attendance.absent_sick || 0),
                    backgroundColor: '#36b9cc',
                    borderColor: '#36b9cc',
                    borderWidth: 1
                },
                {
                    label: 'Absent - Business',
                    data: classesData.map(classData => classData.attendance.absent_business || 0),
                    backgroundColor: '#6f42c1',
                    borderColor: '#6f42c1',
                    borderWidth: 1
                },
                {
                    label: 'Absent - Maternity',
                    data: classesData.map(classData => classData.attendance.absent_maternity || 0),
                    backgroundColor: '#36b9cc',
                    borderColor: '#36b9cc',
                    borderWidth: 1
                }
            ];

            // Filter out datasets with all zero values
            const filteredDatasets = datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            classesAttendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: filteredDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // We're using custom legend below the chart
                        },
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    const index = tooltipItems[0].dataIndex;
                                    const classData = classesData[index];
                                    return `${classData.class_name}\n${classData.program_name}\nBatch: ${classData.class_batch}\nDate: ${classData.start_date}`;
                                },
                                label: function(context) {
                                    const total = classesData[context.dataIndex].total_participants;
                                    const percentage = total > 0 ? ((context.parsed.y / total) * 100).toFixed(
                                        1) : 0;
                                    return `${context.dataset.label}: ${context.parsed.y} (${percentage}%)`;
                                },
                                afterBody: function(tooltipItems) {
                                    const index = tooltipItems[0].dataIndex;
                                    return `Total Participants: ${classesData[index].total_participants}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        function setupFilterHandlers() {
            document.getElementById('yearFilter').addEventListener('change', function() {
                currentYear = this.value;
                updateDashboard();
            });
        }

        function updateDashboard() {
            showFilterLoading(true);

            // Add loading state to cards
            const cards = document.querySelectorAll('.card-body');
            cards.forEach(card => {
                card.style.opacity = '0.6';
                card.style.pointerEvents = 'none';
            });

            // Update calendar
            if (calendar) {
                calendar.gotoDate(`${currentYear}-01-01`);
                calendar.refetchEvents();
            }

            // Update classes attendance chart
            loadClassesAttendanceData();

            // Fetch updated statistics
            const params = new URLSearchParams({
                year: currentYear,
                ajax: 1
            });

            fetch(`{{ route('dashboard.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    // First check if the response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error('Invalid response format: ' + text.substring(0, 100));
                        });
                    }
                    return response.json();
                })
                .then(response => {
                    if (response && response.data) {
                        updateStatistics(response.data);
                    } else {
                        throw new Error(response?.message || 'Invalid response format');
                    }
                })
                .catch(error => {
                    console.error('Error updating dashboard:', error);
                    showAlert('error', 'Failed to update dashboard: ' + error.message);
                })
                .finally(() => {
                    showFilterLoading(false);
                    cards.forEach(card => {
                        card.style.opacity = '1';
                        card.style.pointerEvents = 'auto';
                    });
                });
        }

        function updateStatistics(data) {
            // Update overview stats
            if (data.overviewStats) {
                const stats = data.overviewStats;
                updateElement('totalClasses', stats.total_classes);
                updateElement('totalPrograms', stats.total_programs);
                updateElement('upcomingClasses', stats.upcoming_classes);
            }

            // Update participant statistics
            if (data.participantStats) {
                const stats = data.participantStats;
                updateElement('totalParticipants', stats.total_participants);

                const attendanceRate = stats.attendance_rate || 0;
                const attendanceBar = document.getElementById('attendanceBar');
                if (attendanceBar) {
                    attendanceBar.style.width = attendanceRate + '%';
                }

                if (stats.status_summary) {
                    const summary = stats.status_summary;
                    updateElement('presentCount', summary.present);
                    updateElement('invitedCount', summary.invited);

                    const absentTotal = (summary.absent_sick || 0) + (summary.absent_busy || 0) +
                        (summary.absent_maternity || 0) + (summary.absent_business || 0) +
                        (summary.absent_general || 0);
                    updateElement('absentCount', absentTotal);
                }
            }

            // Update payment statistics
            if (data.paymentStats && data.paymentStats.combined_stats) {
                const stats = data.paymentStats.combined_stats;

                const totalAmountEl = document.getElementById('totalAmount');
                if (totalAmountEl) {
                    totalAmountEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(stats.total_amount || 0);
                }

                updateElement('approvedPayments', stats.approve?.count || 0);
                updateElement('pendingPayments', stats.pending?.count || 0);

                const reviewCount = (stats.check_by_pic?.count || 0) + (stats.check_by_manager?.count || 0);
                const reviewEl = document.getElementById('reviewPayments');
                if (reviewEl) {
                    reviewEl.textContent = reviewCount + ' payments';
                }
            }

            // Update TNA statistics
            if (data.tnaRealizationStats && data.tnaRealizationStats.overall) {
                const stats = data.tnaRealizationStats.overall;

                const percentage = stats.overall_percentage || 0;
                const progressBar = document.getElementById('tnaProgressBar');
                if (progressBar) {
                    progressBar.style.width = percentage + '%';
                }

                const formatAmount = amount => 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
                const totalBudgetEl = document.getElementById('totalBudget');
                const realizationEl = document.getElementById('totalRealization');
                const remainingEl = document.getElementById('totalRemaining');

                if (totalBudgetEl) totalBudgetEl.textContent = formatAmount(stats.total_min_budget);
                if (realizationEl) realizationEl.textContent = formatAmount(stats.total_realization);
                if (remainingEl) remainingEl.textContent = formatAmount(stats.total_remaining);
            }
        }

        function updateElement(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value || 0;
            }
        }

        function showFilterLoading(show) {
            const loadingEl = document.getElementById('filterLoading');
            loadingEl.classList.toggle('d-none', !show);
        }

        function changeCalendarView(viewName) {
            if (calendar) {
                calendar.changeView(viewName);

                // Update button states
                document.querySelectorAll('.btn-group .btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                event.target.classList.add('active');
            }
        }

        function showClassDetails(classId) {
            const modalContent = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading class details...</p>
            </div>
        `;
            document.getElementById('classDetails').innerHTML = modalContent;

            const modal = new bootstrap.Modal(document.getElementById('classModal'));
            modal.show();

            fetch(`/dashboard/class-details/${classId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch class details');
                    return response.json();
                })
                .then(data => {
                    const detailsContent = `
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">${data.class.class_name}</h5>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted">Program</small>
                                            <div class="fw-bold">${data.class.programs?.program_name || 'N/A'}</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">Batch</small>
                                            <div class="fw-bold">${data.class.class_batch || 'N/A'}</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">Location</small>
                                            <div class="fw-bold">${data.class.class_loc || 'N/A'}</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">PIC</small>
                                            <div class="fw-bold">${data.class.programs?.user?.email || 'Not assigned'}</div>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted">Duration</small>
                                            <div class="fw-bold">${data.class.start_date} - ${data.class.end_date}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-users me-2"></i>Participants
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="h2 text-primary mb-2">${data.participant_stats.total}</div>
                                    <div class="small text-muted mb-3">Total Participants</div>
                                    
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="bg-success bg-opacity-10 rounded p-2">
                                                <div class="h5 text-success mb-1">${data.participant_stats.present}</div>
                                                <div class="small text-success">Present</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-danger bg-opacity-10 rounded p-2">
                                                <div class="h5 text-danger mb-1">${data.participant_stats.absent}</div>
                                                <div class="small text-danger">Absent</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    document.getElementById('classDetails').innerHTML = detailsContent;
                })
                .catch(error => {
                    console.error('Error fetching class details:', error);
                    document.getElementById('classDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <h6>Error</h6>
                        <p>Unable to load class details. Please try again.</p>
                    </div>
                `;
                });
        }

        function refreshParticipantStats() {
            const content = document.getElementById('participantStatsContent');
            content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Refreshing participant data...</p>
            </div>
        `;

            setTimeout(() => location.reload(), 1500);
        }

        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : `alert-${type}`;
            const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

            const container = document.querySelector('.container-fluid');
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = alertHtml;
            container.insertBefore(alertDiv.firstElementChild, container.firstChild);

            setTimeout(() => {
                const alert = container.querySelector(`.${alertClass}`);
                if (alert) alert.remove();
            }, 5000);
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (calendar) calendar.updateSize();
            if (classesAttendanceChart) classesAttendanceChart.resize();
        });
    </script>
    <script>
        function navigateToPage(url) {
            window.location.href = url;
        }
    </script>
