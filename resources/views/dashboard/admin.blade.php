@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Header with Actions -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>

            <div class="d-flex gap-2">
                <!-- Export Button -->
                <button class="btn btn-primary btn-sm" onclick="exportReport()">
                    <i class="fas fa-download"></i> Export Report
                </button>

                <!-- Filter Controls -->
                <form method="GET" action="{{ route('dashboard.admin') }}" class="d-flex gap-2">
                    <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                        @for ($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>

                    <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}" {{ $selectedMonth == $month ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Revenue
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp {{ number_format($paymentStats['combined_stats']['total_amount'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                    TNA Realization Rate
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ $tnaRealizationStats['overall']['overall_percentage'] }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $tnaRealizationStats['overall']['overall_percentage'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                    Attendance Rate
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ $participantStats['attendance_rate'] }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: {{ $participantStats['attendance_rate'] }}%"></div>
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
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Active TNAs
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $overviewStats['active_tnas'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Analytics Row -->
        <div class="row">
            <!-- TNA Detailed Analysis -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">TNA Budget vs Realization Analysis</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="#" onclick="exportTNAReport()">Export TNA Report</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tnaTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>TNA Year</th>
                                        <th>Min Budget</th>
                                        <th>Realization</th>
                                        <th>Percentage</th>
                                        <th>Remaining</th>
                                        <th>Programs</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tnaRealizationStats['tna_details'] as $tna)
                                        <tr>
                                            <td>{{ $tna['tna_year'] }}</td>
                                            <td>Rp {{ number_format($tna['min_budget'], 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($tna['realization'], 0, ',', '.') }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar 
                                                {{ $tna['percentage'] >= 80 ? 'bg-success' : ($tna['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar" style="width: {{ $tna['percentage'] }}%">
                                                        {{ $tna['percentage'] }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rp {{ number_format($tna['remaining'], 0, ',', '.') }}</td>
                                            <td>{{ count($tna['programs']) }} programs</td>
                                            <td>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="showTNADetails({{ $tna['tna_id'] }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Status Breakdown -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Payment Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="paymentPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="mr-2">
                                <i class="fas fa-circle text-success"></i> Approved
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-warning"></i> Pending
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-info"></i> Under Review
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-danger"></i> Rejected
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Row -->
        <div class="row">
            <!-- Program Performance -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Program Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th>Classes</th>
                                        <th>Participants</th>
                                        <th>Budget Utilization</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tnaRealizationStats['tna_details'] as $tna)
                                        @foreach ($tna['programs'] as $program)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $program['program_name'] }}</div>
                                                </td>
                                                <td>{{ $program['classes_count'] }}</td>
                                                <td>-</td>
                                                <td>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-primary"
                                                            style="width: {{ $program['percentage'] }}%">
                                                        </div>
                                                    </div>
                                                    <small>{{ $program['percentage'] }}%</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Statistics -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">System Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="border-left-primary p-3">
                                    <div class="text-primary font-weight-bold h5">{{ $overviewStats['total_classes'] }}
                                    </div>
                                    <div class="small">Total Classes</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border-left-success p-3">
                                    <div class="text-success font-weight-bold h5">{{ $overviewStats['total_programs'] }}
                                    </div>
                                    <div class="small">Active Programs</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border-left-info p-3">
                                    <div class="text-info font-weight-bold h5">{{ $overviewStats['total_pics'] }}</div>
                                    <div class="small">PICs</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border-left-warning p-3">
                                    <div class="text-warning font-weight-bold h5">
                                        {{ $participantStats['total_participants'] }}</div>
                                    <div class="small">Total Participants</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-weight-bold">Quick Actions</h6>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('classes.create') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-plus"></i> Create New Class
                                    </a>
                                    <a href="{{ route('programs.index') }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-list"></i> Manage Programs
                                    </a>
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-users"></i> User Management
                                    </a>
                                    <a href="{{ route('reports.financial') }}" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-chart-bar"></i> Financial Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Calendar (Admin View) -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Training Schedule Overview</h6>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary"
                                onclick="calendar.changeView('dayGridMonth')">Month</button>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="calendar.changeView('timeGridWeek')">Week</button>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="calendar.changeView('listWeek')">List</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="adminCalendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TNA Details Modal -->
    <div class="modal fade" id="tnaModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">TNA Program Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="tnaDetails">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Management Modal -->
    <div class="modal fade" id="classManagementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Class Management</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="classManagementContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('vendor/fullcalendar/main.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/fullcalendar/main.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>

    <script>
        let calendar;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            $('#tnaTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                        targets: [1, 2, 4],
                        className: 'text-right'
                    },
                    {
                        targets: [6],
                        orderable: false
                    }
                ]
            });

            // Initialize Calendar
            var calendarEl = document.getElementById('adminCalendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                events: @json($trainingCalendar),
                eventClick: function(info) {
                    showClassManagement(info.event.id);
                },
                height: 'auto',
                eventDisplay: 'block'
            });
            calendar.render();

            // Initialize Payment Pie Chart
            var ctx = document.getElementById('paymentPieChart');
            var paymentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Under Review', 'Rejected'],
                    datasets: [{
                        data: [
                            {{ $paymentStats['combined_stats']['approve']['count'] }},
                            {{ $paymentStats['combined_stats']['pending']['count'] }},
                            {{ $paymentStats['combined_stats']['check_by_pic']['count'] + $paymentStats['combined_stats']['check_by_manager']['count'] }},
                            {{ $paymentStats['combined_stats']['reject']['count'] }}
                        ],
                        backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
                        hoverBackgroundColor: ['#218838', '#e0a800', '#138496', '#c82333'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 80,
                },
            });
        });

        function showTNADetails(tnaId) {
            // Show loading
            const modalContent = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
            document.getElementById('tnaDetails').innerHTML = modalContent;
            $('#tnaModal').modal('show');

            // Fetch TNA details
            fetch(`/dashboard/tna-details/${tnaId}`)
                .then(response => response.json())
                .then(data => {
                    let programsTable = '';
                    data.programs.forEach(program => {
                        programsTable += `
                    <tr>
                        <td>${program.program_name}</td>
                        <td>${program.classes_count}</td>
                        <td>Rp ${new Intl.NumberFormat('id-ID').format(program.budget)}</td>
                        <td>Rp ${new Intl.NumberFormat('id-ID').format(program.realization)}</td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar bg-${program.percentage >= 80 ? 'success' : program.percentage >= 50 ? 'warning' : 'danger'}" 
                                     style="width: ${program.percentage}%">${program.percentage}%</div>
                            </div>
                        </td>
                    </tr>
                `;
                    });

                    const detailContent = `
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h5 class="text-primary">Min Budget</h5>
                                <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.tna.min_budget)}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h5 class="text-success">Realization</h5>
                                <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.tna.realization)}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h5 class="text-info">Percentage</h5>
                                <h3>${data.tna.percentage}%</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h5 class="text-warning">Remaining</h5>
                                <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.tna.remaining)}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Program Name</th>
                                <th>Classes</th>
                                <th>Budget</th>
                                <th>Realization</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${programsTable}
                        </tbody>
                    </table>
                </div>
            `;
                    document.getElementById('tnaDetails').innerHTML = detailContent;
                })
                .catch(error => {
                    document.getElementById('tnaDetails').innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error loading TNA details</h6>
                    <p>Please try again later.</p>
                </div>
            `;
                });
        }

        function showClassManagement(classId) {
            const modalContent = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
            document.getElementById('classManagementContent').innerHTML = modalContent;
            $('#classManagementModal').modal('show');

            fetch(`/dashboard/class-details/${classId}`)
                .then(response => response.json())
                .then(data => {
                    const managementContent = `
                <div class="row">
                    <div class="col-md-8">
                        <h5>${data.class.class_name}</h5>
                        <p><strong>Program:</strong> ${data.class.programs.program_name}</p>
                        <p><strong>Batch:</strong> ${data.class.class_batch}</p>
                        <p><strong>Location:</strong> ${data.class.class_loc}</p>
                        <p><strong>Date:</strong> ${data.class.start_date} - ${data.class.end_date}</p>
                        <p><strong>PIC:</strong> ${data.class.programs.user ? data.class.programs.user.name : 'Not assigned'}</p>
                        
                        <div class="btn-group mt-3">
                            <a href="/classes/${classId}/edit" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit Class
                            </a>
                            <a href="/classes/${classId}/participants" class="btn btn-success btn-sm">
                                <i class="fas fa-users"></i> Manage Participants
                            </a>
                            <a href="/classes/${classId}/payments" class="btn btn-warning btn-sm">
                                <i class="fas fa-dollar-sign"></i> Payments
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Participants Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="h4 text-primary">${data.participant_stats.total}</div>
                                    <div class="small">Total Participants</div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="text-success font-weight-bold">${data.participant_stats.present}</div>
                                        <div class="small">Present</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-danger font-weight-bold">${data.participant_stats.absent}</div>
                                        <div class="small">Absent</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                    document.getElementById('classManagementContent').innerHTML = managementContent;
                });
        }

        function exportReport() {
            window.open('/dashboard/export-report?year={{ $selectedYear }}&month={{ $selectedMonth }}', '_blank');
        }

        function exportTNAReport() {
            window.open('/dashboard/export-tna-report?year={{ $selectedYear }}', '_blank');
        }
    </script>
@endpush
