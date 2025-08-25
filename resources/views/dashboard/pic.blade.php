@extends('layouts.app')

@section('title', 'PIC Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">PIC Dashboard</h1>
                <p class="mb-0 text-gray-600">Manage your assigned training programs</p>
            </div>

            <!-- Quick Actions -->
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('dashboard.pic') }}" class="d-flex gap-2">
                    <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                        @for ($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- PIC Performance Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    My Programs
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $picStats['total_programs'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list-alt fa-2x text-gray-300"></i>
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
                                    Active Classes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $picStats['active_classes'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
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
                                    Total Participants
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $picStats['total_participants'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                    Pending Payments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $picStats['pending_payments'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- My Training Calendar -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">My Training Schedule</h6>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary"
                                onclick="picCalendar.changeView('dayGridMonth')">Month</button>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="picCalendar.changeView('timeGridWeek')">Week</button>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="picCalendar.changeView('listWeek')">List</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="picTrainingCalendar"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Tasks & Reminders -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tasks"></i> Tasks & Reminders
                        </h6>
                    </div>
                    <div class="card-body">
                        @if (isset($picTasks) && count($picTasks) > 0)
                            @foreach ($picTasks as $task)
                                <div
                                    class="alert alert-{{ $task['priority'] == 'high' ? 'danger' : ($task['priority'] == 'medium' ? 'warning' : 'info') }} py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="font-weight-bold">{{ $task['title'] }}</small>
                                            <div class="small">{{ $task['description'] }}</div>
                                            <div class="small text-muted">Due: {{ $task['due_date'] }}</div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="markTaskComplete({{ $task['id'] }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>All tasks completed!</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bell"></i> Recent Activities
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @if (isset($picActivities) && count($picActivities) > 0)
                                @foreach ($picActivities as $activity)
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

                <!-- Quick Stats -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie"></i> My Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border-left-success p-2">
                                    <div class="text-success font-weight-bold h6">{{ $picStats['success_rate'] ?? 0 }}%
                                    </div>
                                    <div class="small">Success Rate</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border-left-info p-2">
                                    <div class="text-info font-weight-bold h6">{{ $picStats['avg_rating'] ?? 0 }}/5</div>
                                    <div class="small">Avg Rating</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-left-warning p-2">
                                    <div class="text-warning font-weight-bold h6">
                                        {{ $picStats['completed_classes'] ?? 0 }}</div>
                                    <div class="small">Completed</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-left-primary p-2">
                                    <div class="text-primary font-weight-bold h6">{{ $picStats['upcoming_classes'] ?? 0 }}
                                    </div>
                                    <div class="small">Upcoming</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Programs Management -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">My Programs & Classes</h6>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                            <button type="button" class="btn btn-outline-primary"
                                data-filter="upcoming">Upcoming</button>
                            <button type="button" class="btn btn-outline-primary" data-filter="ongoing">Ongoing</button>
                            <button type="button" class="btn btn-outline-primary"
                                data-filter="completed">Completed</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="picClassesTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th>Class Name</th>
                                        <th>Batch</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Participants</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($picClasses) && count($picClasses) > 0)
                                        @foreach ($picClasses as $class)
                                            <tr data-status="{{ $class['status'] }}">
                                                <td>{{ $class['program_name'] }}</td>
                                                <td>{{ $class['class_name'] }}</td>
                                                <td>{{ $class['batch'] }}</td>
                                                <td>
                                                    {{ date('M d, Y', strtotime($class['start_date'])) }}
                                                    @if ($class['end_date'] != $class['start_date'])
                                                        - {{ date('M d, Y', strtotime($class['end_date'])) }}
                                                    @endif
                                                </td>
                                                <td>{{ $class['location'] }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-primary">{{ $class['participants_count'] }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $class['status'] == 'completed' ? 'success' : ($class['status'] == 'ongoing' ? 'warning' : 'info') }}">
                                                        {{ ucfirst($class['status']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-info"
                                                            onclick="viewClassDetails({{ $class['id'] }})"
                                                            title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-primary"
                                                            onclick="manageParticipants({{ $class['id'] }})"
                                                            title="Manage Participants">
                                                            <i class="fas fa-users"></i>
                                                        </button>
                                                        <button class="btn btn-warning"
                                                            onclick="managePayments({{ $class['id'] }})"
                                                            title="Manage Payments">
                                                            <i class="fas fa-dollar-sign"></i>
                                                        </button>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button"
                                                                class="btn btn-secondary dropdown-toggle"
                                                                data-toggle="dropdown">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item"
                                                                    href="/classes/{{ $class['id'] }}/edit">
                                                                    <i class="fas fa-edit"></i> Edit Class
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="/classes/{{ $class['id'] }}/agenda">
                                                                    <i class="fas fa-list"></i> Manage Agenda
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="/classes/{{ $class['id'] }}/materials">
                                                                    <i class="fas fa-folder"></i> Materials
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item"
                                                                    href="/classes/{{ $class['id'] }}/reports">
                                                                    <i class="fas fa-chart-bar"></i> Reports
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No classes assigned to you yet
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
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Class Management</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
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

    <!-- Participants Management Modal -->
    <div class="modal fade" id="participantsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Participants</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="participantsContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Modal -->
    <div class="modal fade" id="paymentsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Management</h5>
                    <button type="button" class="close" data-dismiss="modal">
                </div>
                <div class="modal-body">
                    <div id="paymentsContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


<link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/fullcalendar/main.min.css') }}" rel="stylesheet">
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 3px solid #dee2e6;
    }

    .timeline-title {
        font-size: 14px;
        margin-bottom: 5px;
    }

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
</style>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    let picCalendar;
    let classesTable;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Calendar
        var calendarEl = document.getElementById('picTrainingCalendar');
        if (calendarEl) {
            picCalendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                events: {!! isset($picTrainingCalendar) ? json_encode($picTrainingCalendar) : '[]' !!},
                eventClick: function(info) {
                    viewClassDetails(info.event.id);
                },
                height: 'auto',
                eventDisplay: 'block'
            });
            picCalendar.render();
        }

        // Initialize DataTable
        const tableElement = document.getElementById('picClassesTable');
        if (tableElement && $.fn.DataTable) {
            classesTable = $('#picClassesTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [3, 'asc']
                ], // Sort by date
                columnDefs: [{
                        targets: [7],
                        orderable: false
                    },
                    {
                        targets: [5],
                        className: 'text-center'
                    },
                    {
                        targets: [6],
                        className: 'text-center'
                    }
                ]
            });
        }

        // Filter buttons
        document.querySelectorAll('[data-filter]').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.remove(
                    'active'));
                // Add active class to clicked button
                this.classList.add('active');

                const filter = this.dataset.filter;
                filterClasses(filter);
            });
        });
    });

    function filterClasses(status) {
        if (classesTable) {
            if (status === 'all') {
                classesTable.search('').columns().search('').draw();
            } else {
                classesTable.column(6).search(status, true, false).draw();
            }
        }
    }

    function viewClassDetails(classId) {
        const modalContent = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
        document.getElementById('classDetailsContent').innerHTML = modalContent;
        $('#classDetailsModal').modal('show');

        // Updated endpoint to PIC-specific
        fetch(`/pic/class-details/${classId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.message || 'Unknown error');
                }

                const detailsContent = `
                <div class="row">
                    <div class="col-md-8">
                        <h5>${data.class.class_name}</h5>
                        <p><strong>Program:</strong> ${data.class.programs ? data.class.programs.program_name : 'N/A'}</p>
                        <p><strong>Batch:</strong> ${data.class.class_batch || 'N/A'}</p>
                        <p><strong>Location:</strong> ${data.class.class_loc || 'TBD'}</p>
                        <p><strong>Date:</strong> ${data.class.start_date} - ${data.class.end_date}</p>
                        
                        ${data.class.description ? `<p><strong>Description:</strong> ${data.class.description}</p>` : ''}
                        
                        <div class="mt-4">
                            <h6>Quick Actions:</h6>
                            <div class="btn-group">
                                <a href="/classes/${classId}/edit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit Class
                                </a>
                                <button class="btn btn-success btn-sm" onclick="manageParticipants(${classId})">
                                    <i class="fas fa-users"></i> Manage Participants
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="managePayments(${classId})">
                                    <i class="fas fa-dollar-sign"></i> Payments
                                </button>
                                <a href="/classes/${classId}/reports" class="btn btn-info btn-sm">
                                    <i class="fas fa-chart-bar"></i> Reports
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Class Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <div class="text-primary font-weight-bold h5">${data.stats.total_participants || 0}</div>
                                        <div class="small">Total</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-success font-weight-bold h5">${data.stats.present || 0}</div>
                                        <div class="small">Present</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="small mb-1">Attendance Rate</div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: ${data.stats.attendance_rate || 0}%">
                                            ${data.stats.attendance_rate || 0}%
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="small mb-2">
                                    <strong>Payment Status:</strong>
                                </div>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-success">${(data.stats.payments && data.stats.payments.approved) || 0}</div>
                                        <div class="small">Approved</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-warning">${(data.stats.payments && data.stats.payments.pending) || 0}</div>
                                        <div class="small">Pending</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-info">${(data.stats.payments && data.stats.payments.review) || 0}</div>
                                        <div class="small">Review</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${(data.agenda && data.agenda.length > 0) ? `
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Training Agenda</h6>
                                    </div>
                                    <div class="card-body">
                                        ${data.agenda.map((item, index) => `
                                        <div class="mb-2">
                                            <strong>${index + 1}. ${item.topic}</strong>
                                            <br><small class="text-muted">${item.duration} - ${item.description || ''}</small>
                                        </div>
                                    `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                    </div>
                </div>
            `;
                document.getElementById('classDetailsContent').innerHTML = detailsContent;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('classDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error loading class details</h6>
                    <p>${error.message || 'Please try again later.'}</p>
                </div>
            `;
            });
    }

    function manageParticipants(classId) {
        const modalContent = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading participants...</span>
            </div>
        </div>
    `;
        document.getElementById('participantsContent').innerHTML = modalContent;
        $('#participantsModal').modal('show');

        // Updated endpoint to PIC-specific
        fetch(`/pic/class-participants/${classId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.message || 'Unknown error');
                }

                let participantsTable = '';
                if (data.participants && data.participants.length > 0) {
                    participantsTable = `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>NIK</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.participants.map(participant => `
                                        <tr>
                                            <td>${participant.name}</td>
                                            <td>${participant.email}</td>
                                            <td>${participant.nik || 'N/A'}</td>
                                            <td>
                                                <span class="badge badge-${participant.type === 'final' ? 'success' : 'info'}">
                                                    ${participant.type.toUpperCase()}
                                                </span>
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm" 
                                                        onchange="updateParticipantStatus(${participant.id}, this.value)">
                                                    <option value="Invited" ${participant.status === 'Invited' ? 'selected' : ''}>Invited</option>
                                                    <option value="Present" ${participant.status === 'Present' ? 'selected' : ''}>Present</option>
                                                    <option value="Absent - Sick" ${participant.status === 'Absent - Sick' ? 'selected' : ''}>Absent - Sick</option>
                                                    <option value="Absent - Busy" ${participant.status === 'Absent - Busy' ? 'selected' : ''}>Absent - Busy</option>
                                                    <option value="Absent - Business" ${participant.status === 'Absent - Business' ? 'selected' : ''}>Absent - Business</option>
                                                    <option value="Absent - Maternity" ${participant.status === 'Absent - Maternity' ? 'selected' : ''}>Absent - Maternity</option>
                                                    <option value="Absent - General" ${participant.status === 'Absent - General' ? 'selected' : ''}>Absent - General</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="removeParticipant(${participant.id})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                } else {
                    participantsTable = '<div class="text-center text-muted py-4">No participants found</div>';
                }

                const participantsContent = `
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" onclick="addParticipant(${classId})">
                        <i class="fas fa-plus"></i> Add Participant
                    </button>
                    <button class="btn btn-info btn-sm" onclick="importParticipants(${classId})">
                        <i class="fas fa-upload"></i> Import from Excel
                    </button>
                </div>
                ${participantsTable}
            `;
                document.getElementById('participantsContent').innerHTML = participantsContent;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('participantsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error loading participants</h6>
                    <p>${error.message || 'Please try again later.'}</p>
                </div>
            `;
            });
    }

    function managePayments(classId) {
        const modalContent = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading payments...</span>
            </div>
        </div>
    `;
        document.getElementById('paymentsContent').innerHTML = modalContent;
        $('#paymentsModal').modal('show');

        // Updated endpoint to PIC-specific
        fetch(`/pic/class-payments/${classId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.message || 'Unknown error');
                }

                let paymentsTable = '';
                if (data.payments && data.payments.length > 0) {
                    paymentsTable = `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.payments.map(payment => `
                                        <tr>
                                            <td>
                                                <span class="badge badge-${payment.type === 'general' ? 'primary' : 'success'}">
                                                    ${payment.type.toUpperCase()}
                                                </span>
                                            </td>
                                            <td>${payment.description}</td>
                                            <td>Rp ${new Intl.NumberFormat('id-ID').format(payment.amount)}</td>
                                            <td>
                                                <span class="badge badge-${getPaymentStatusBadge(payment.status)}">
                                                    ${payment.status}
                                                </span>
                                            </td>
                                            <td>${new Date(payment.created_at).toLocaleDateString('id-ID')}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewPaymentDetails(${payment.id}, '${payment.type}')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                ${payment.status === 'Check by PIC' ? `
                                                <button class="btn btn-sm btn-success" onclick="approvePayment(${payment.id}, '${payment.type}')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectPayment(${payment.id}, '${payment.type}')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            ` : ''}
                                            </td>
                                        </tr>
                                    `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                } else {
                    paymentsTable = '<div class="text-center text-muted py-4">No payments found</div>';
                }

                const paymentsContent = `
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" onclick="addPayment(${classId})">
                        <i class="fas fa-plus"></i> Add Payment Request
                    </button>
                </div>
                ${paymentsTable}
            `;
                document.getElementById('paymentsContent').innerHTML = paymentsContent;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('paymentsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error loading payments</h6>
                    <p>${error.message || 'Please try again later.'}</p>
                </div>
            `;
            });
    }

    function getPaymentStatusBadge(status) {
        switch (status) {
            case 'Approve':
                return 'success';
            case 'Reject':
                return 'danger';
            case 'Pending':
                return 'warning';
            case 'Check by PIC':
            case 'Check by Manager':
                return 'info';
            default:
                return 'secondary';
        }
    }

    function updateParticipantStatus(participantId, status) {
        fetch(`/pic/update-participant-status/${participantId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Participant status updated!');
                } else {
                    showAlert('error', data.message || 'Error updating status. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error updating status. Please try again.');
            });
    }

    function markTaskComplete(taskId) {
        fetch(`/pic/complete-task/${taskId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Task marked as complete!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert('error', data.message || 'Error completing task.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error completing task. Please try again.');
            });
    }

    // Placeholder functions - implement these based on your needs
    function addParticipant(classId) {
        showAlert('info', 'Add Participant functionality to be implemented');
        // You can implement a form modal here or redirect to add participant page
    }

    function importParticipants(classId) {
        showAlert('info', 'Import Participants functionality to be implemented');
        // You can implement file upload functionality here
    }

    function removeParticipant(participantId) {
        if (confirm('Are you sure you want to remove this participant?')) {
            showAlert('info', 'Remove Participant functionality to be implemented');
            // Implement participant removal logic here
        }
    }

    function addPayment(classId) {
        showAlert('info', 'Add Payment functionality to be implemented');
        // You can implement a form modal here or redirect to add payment page
    }

    function viewPaymentDetails(paymentId, type) {
        showAlert('info', `View Payment Details functionality to be implemented for ${type} payment #${paymentId}`);
        // You can implement a detailed payment view modal here
    }

    function approvePayment(paymentId, type) {
        if (confirm('Are you sure you want to approve this payment?')) {
            showAlert('info', `Approve Payment functionality to be implemented for ${type} payment #${paymentId}`);
            // Implement payment approval logic here
        }
    }

    function rejectPayment(paymentId, type) {
        if (confirm('Are you sure you want to reject this payment?')) {
            showAlert('info', `Reject Payment functionality to be implemented for ${type} payment #${paymentId}`);
            // Implement payment rejection logic here
        }
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' :
            type === 'error' ? 'alert-danger' :
            type === 'warning' ? 'alert-warning' : 'alert-info';

        const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;

        // Insert alert at the top of the content
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
</script>
