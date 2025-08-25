@extends('layouts.app')

@section('title', 'My Training Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Welcome Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Welcome, {{ Auth::user()->email }}!</h1>
                <p class="mb-0 text-gray-600">Here's your training overview</p>
            </div>

            <!-- Quick Actions -->
            <div class="btn-group">
                <a href="{{ route('profile.edit') ?? '#' }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-user-edit"></i> Update Profile
                </a>
            </div>
        </div>

        <!-- Personal Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Classes Attended
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $myStats['total_classes'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Attendance Rate
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ $myStats['attendance_rate'] ?? 0 }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $myStats['attendance_rate'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Upcoming Classes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $myStats['upcoming_classes'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-plus fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Certificates Earned
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $myStats['certificates'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-award fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- My Training Schedule -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">My Training Schedule</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-view="all">All</button>
                            <button type="button" class="btn btn-outline-primary" data-view="upcoming">Upcoming</button>
                            <button type="button" class="btn btn-outline-primary" data-view="completed">Completed</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="myTrainingCalendar" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Upcoming Classes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clock"></i> Upcoming Classes
                        </h6>
                    </div>
                    <div class="card-body">
                        @if (isset($myUpcomingClasses) && count($myUpcomingClasses) > 0)
                            @foreach ($myUpcomingClasses as $class)
                                <div class="card border-left-info mb-3">
                                    <div class="card-body py-3">
                                        <h6 class="font-weight-bold text-primary">{{ $class['class_name'] }}</h6>
                                        <p class="small mb-1">
                                            <i class="fas fa-calendar"></i>
                                            {{ date('M d, Y', strtotime($class['start_date'])) }}
                                        </p>
                                        <p class="small mb-1">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $class['location'] }}
                                        </p>
                                        <p class="small mb-2">
                                            <i class="fas fa-tag"></i>
                                            {{ $class['program_name'] }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>
                                                {{ $class['status'] }}
                                            </span>
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="showClassDetails({{ $class['id'] }})">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <p>No upcoming classes</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- My Transaction Progress -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-money-bill-wave"></i> Transaction Progress
                        </h6>
                    </div>
                    <div class="card-body">
                        @if (isset($myTransactionStats))
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="card border-left-warning">
                                        <div class="card-body py-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                {{ $myTransactionStats['pending'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="card border-left-info">
                                        <div class="card-body py-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Review
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                {{ $myTransactionStats['in_review'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-left-success">
                                        <div class="card-body py-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Accepted
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                {{ $myTransactionStats['accepted'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-left-danger">
                                        <div class="card-body py-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                {{ $myTransactionStats['rejected'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="text-center">
                                <div class="text-xs font-weight-bold text-gray-800 text-uppercase mb-1">Total Amount</div>
                                <div class="h5 mb-0 font-weight-bold text-primary">
                                    Rp {{ number_format($myTransactionStats['total_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                                <p>No transactions yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history"></i> Recent Training Activity
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Class Name</th>
                                        <th>Program</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th>Pre Test</th>
                                        <th>Post Test</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($myRecentActivity) && count($myRecentActivity) > 0)
                                        @foreach ($myRecentActivity as $activity)
                                            <tr>
                                                <td>{{ date('M d, Y', strtotime($activity['date'])) }}</td>
                                                <td>{{ $activity['class_name'] }}</td>
                                                <td>{{ $activity['program_name'] }}</td>
                                                <td>
                                                    <span>
                                                        {{ $activity['status'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $activity['location'] }}</td>
                                                <td>
                                                    @if ($activity['pre_test'])
                                                        <span>{{ $activity['pre_test'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($activity['post_test'])
                                                        <span>{{ $activity['post_test'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-info"
                                                            onclick="viewClassDetails({{ $activity['class_id'] }})">
                                                            View Details
                                                        </button>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No training activity found
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Details Modal -->
    <div class="modal fade" id="classDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Class Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div id="classDetailsContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        function getBadgeClass($status)
        {
            switch ($status) {
                case 'Present':
                    return 'success';
                case 'Invited':
                    return 'info';
                case 'Absent - Sick':
                case 'Absent - Busy':
                case 'Absent - Maternity':
                case 'Absent - Business':
                case 'Absent':
                    return 'danger';
                default:
                    return 'warning';
            }
        }
    @endphp
@endsection


<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .progress-sm {
        height: 0.5rem;
    }

    .badge-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    #myTrainingCalendar {
        font-size: 14px;
    }

    .fc-event {
        cursor: pointer;
    }

    .fc-event:hover {
        opacity: 0.8;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 300px;
    }

    /* Loading animation */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Print styles */
    @media print {

        .btn,
        .modal,
        .navbar {
            display: none !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }

        .page-break {
            page-break-before: always;
        }
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            margin-bottom: 0.25rem;
        }

        .chart-container {
            height: 250px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let myCalendar;
    let attendanceChart;

    // Global variables for data
    const trainingData = @json($myTrainingCalendar ?? []);
    const attendanceData = @json($myAttendanceStats ?? null);

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Calendar
        initializeCalendar();

        // Initialize Attendance Chart
        initializeAttendanceChart();

        // Initialize event listeners
        initializeEventListeners();

        // Initialize tooltips and popovers
        initializeBootstrapComponents();

        // Check for saved theme preference
        checkThemePreference();
    });

    function initializeCalendar() {
        const calendarEl = document.getElementById('myTrainingCalendar');

        if (calendarEl && trainingData) {
            myCalendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'listMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                events: trainingData,
                eventClick: function(info) {
                    showClassDetails(info.event.id);
                },
                height: 'auto',
                eventDisplay: 'block',
                eventDidMount: function(info) {
                    // Add tooltip to events
                    info.el.setAttribute('title', info.event.extendedProps.description || '');

                    // Add status class for styling
                    const status = info.event.extendedProps.status;
                    if (status) {
                        info.el.classList.add(`event-${status.toLowerCase().replace(/\s+/g, '-')}`);
                    }
                },
                loading: function(bool) {
                    const loader = document.getElementById('calendar-loader');
                    if (loader) {
                        loader.style.display = bool ? 'block' : 'none';
                    }
                }
            });

            myCalendar.render();
        }
    }

    function initializeAttendanceChart() {
        const attendanceCtx = document.getElementById('attendanceChart');

        if (attendanceCtx && attendanceData && attendanceData.total_invited > 0) {
            const statusSummary = attendanceData.status_summary || {};

            attendanceChart = new Chart(attendanceCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Present',
                        'Invited',
                        'Absent - Busy',
                        'Absent',
                        'Absent - Sick',
                        'Absent - Maternity',
                        'Absent - Business'
                    ],
                    datasets: [{
                        data: [
                            statusSummary.present || 0,
                            statusSummary.invited || 0,
                            (statusSummary.absent_busy || 0) + (statusSummary.temp_absent_busy ||
                                0),
                            (statusSummary.absent_general || 0) + (statusSummary
                                .temp_absent_general || 0),
                            statusSummary.absent_sick || 0,
                            statusSummary.absent_maternity || 0,
                            statusSummary.absent_business || 0
                        ],
                        backgroundColor: [
                            '#28a745', // Present - Green
                            '#17a2b8', // Invited - Blue
                            '#ffc107', // Absent Busy - Yellow
                            '#dc3545', // Absent - Red
                            '#36b9cc', // Absent Sick - Light Blue
                            '#6f42c1', // Absent Maternity - Purple
                            '#343a40' // Absent Business - Dark
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(
                                        1) : 0;
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function initializeEventListeners() {
        // View filter buttons
        document.querySelectorAll('[data-view]').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                const view = this.dataset.view;
                filterTrainingView(view);
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + P for print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printSchedule();
            }

            // Escape to close modals
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) bsModal.hide();
                });
            }
        });

        // Handle offline/online status
        window.addEventListener('online', function() {
            showAlert('success', 'Connection restored. You are back online.');
        });

        window.addEventListener('offline', function() {
            showAlert('warning', 'You are currently offline. Some features may not be available.');
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (alert.classList.contains('alert-dismissible')) {
                    alert.remove();
                }
            });
        }, 5000);
    }

    function initializeBootstrapComponents() {
        // Initialize tooltips if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }
    }

    function checkThemePreference() {
        const savedTheme = localStorage.getItem('dashboard-theme');
        if (savedTheme) {
            document.body.classList.add(savedTheme);
        }
    }

    function filterTrainingView(view) {
        if (!myCalendar || !trainingData) return;

        let filteredEvents = [...trainingData];
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        switch (view) {
            case 'upcoming':
                filteredEvents = filteredEvents.filter(event => new Date(event.start) >= today);
                break;
            case 'completed':
                filteredEvents = filteredEvents.filter(event => new Date(event.end || event.start) < today);
                break;
            case 'all':
            default:
                break;
        }

        myCalendar.removeAllEvents();
        myCalendar.addEventSource(filteredEvents);
    }

    function showClassDetails(classId) {
        const modalContent = `
            <div class="text-center py-5">
                <div class="loading-spinner"></div>
                <p class="mt-2 text-muted">Loading class details...</p>
            </div>
        `;

        document.getElementById('classDetailsContent').innerHTML = modalContent;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('classDetailsModal'));
        modal.show();

        // Find class data
        const classData = trainingData.find(event => event.id == classId);

        if (classData) {
            setTimeout(() => {
                const detailsContent = `
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary">${classData.title}</h5>
                            <div class="mb-3">
                                <p class="mb-1"><strong><i class="fas fa-tag text-primary"></i> Program:</strong> ${classData.extendedProps.program_name || 'N/A'}</p>
                                <p class="mb-1"><strong><i class="fas fa-layer-group text-info"></i> Batch:</strong> ${classData.extendedProps.batch || 'N/A'}</p>
                                <p class="mb-1"><strong><i class="fas fa-map-marker-alt text-danger"></i> Location:</strong> ${classData.extendedProps.location || 'TBD'}</p>
                                <p class="mb-1"><strong><i class="fas fa-calendar text-success"></i> Date:</strong> ${formatDate(classData.start)} ${classData.end ? '- ' + formatDate(classData.end) : ''}</p>
                                <p class="mb-1"><strong><i class="fas fa-info-circle text-secondary"></i> Status:</strong> 
                                    <span>
                                        ${classData.extendedProps.status || 'Unknown'}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-cogs"></i> Actions</h6>
                                </div>
                                <div class="card-body text-center">
                                    <button class="btn btn-primary btn-sm mb-2 w-100" onclick="viewFullClassDetails(${classId})">
                                        <i class="fas fa-eye"></i> View Full Details
                                    </button>
                                    
                                    <button class="btn btn-info btn-sm w-100" onclick="addToCalendar(${classId})">
                                        <i class="fas fa-calendar-plus"></i> Add to Calendar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('classDetailsContent').innerHTML = detailsContent;
            }, 500); // Simulate loading time
        } else {
            setTimeout(() => {
                document.getElementById('classDetailsContent').innerHTML = `
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h6>Class not found</h6>
                        <p class="mb-0">Unable to load class details.</p>
                    </div>
                `;
            }, 500);
        }
    }

    function getBadgeClass(status) {
        const statusMap = {
            'Present': 'success',
            'Invited': 'info',
            'Absent - Sick': 'danger',
            'Absent - Busy': 'warning',
            'Absent - Maternity': 'purple',
            'Absent - Business': 'dark',
            'Absent': 'danger'
        };
        return statusMap[status] || 'secondary';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function viewClassDetails(classId) {
        showClassDetails(classId);
    }

    function viewFullClassDetails(classId) {
        window.location.href = `/class/${classId}/view`;
    }

    function downloadCertificate(classId) {
        const loadingAlert = showAlert('info', 'Preparing your certificate for download...');

        // Simulate certificate generation
        setTimeout(() => {
            if (loadingAlert) loadingAlert.remove();

            // Here you would typically make an AJAX call to generate/download the certificate
            // For now, we'll show a success message
            showAlert('success', 'Certificate downloaded successfully!');

            // Example: window.open(`/certificates/download/${classId}`, '_blank');
        }, 2000);
    }

    function addToCalendar(classId) {
        const classData = trainingData.find(event => event.id == classId);
        if (!classData) {
            showAlert('error', 'Class data not found');
            return;
        }

        // Create calendar event URL (Google Calendar format)
        const startDate = new Date(classData.start).toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        const endDate = classData.end ? new Date(classData.end).toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z' :
            startDate;

        const calendarUrl =
            `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(classData.title)}&dates=${startDate}/${endDate}&details=${encodeURIComponent(classData.extendedProps.description || '')}&location=${encodeURIComponent(classData.extendedProps.location || '')}`;

        window.open(calendarUrl, '_blank');
        showAlert('info', 'Opening Google Calendar...');
    }

    function showAlert(type, message) {
        // Remove existing alerts of the same type
        document.querySelectorAll(`.alert-${type === 'error' ? 'danger' : type}`).forEach(alert => {
            alert.remove();
        });

        const alertClass = type === 'error' ? 'alert-danger' : `alert-${type}`;
        const iconClass = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle',
            'warning': 'fas fa-exclamation-triangle'
        } [type] || 'fas fa-info-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${iconClass} mr-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const alertElement = document.createElement('div');
        alertElement.innerHTML = alertHtml;
        document.body.appendChild(alertElement.firstElementChild);

        // Auto-dismiss success and info alerts
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                const alert = document.querySelector(`.alert-${type === 'error' ? 'danger' : type}`);
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        return document.querySelector(`.alert-${type === 'error' ? 'danger' : type}`);
    }

    function printSchedule() {
        // Add print-specific content
        const printContent = `
            <div class="print-header">
                <h2>Training Schedule - ${new Date().toLocaleDateString()}</h2>
                <p>Generated for: {{ Auth::user()->email }}</p>
            </div>
        `;

        const printDiv = document.createElement('div');
        printDiv.innerHTML = printContent;
        printDiv.style.display = 'none';
        printDiv.className = 'print-only';

        document.body.appendChild(printDiv);

        window.print();

        // Remove print content after printing
        setTimeout(() => {
            document.body.removeChild(printDiv);
        }, 1000);
    }

    // Export functions for global access
    window.showClassDetails = showClassDetails;
    window.viewClassDetails = viewClassDetails;
    window.downloadCertificate = downloadCertificate;
    window.printSchedule = printSchedule;
</script>
