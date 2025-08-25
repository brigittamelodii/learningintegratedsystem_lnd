@extends('layouts.app')

@section('title', 'PIC Detail - ' . $pic->name)

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">PIC Performance Detail</h2>
                        <p class="text-muted">Detailed performance analysis for
                            {{ ucwords(str_replace(['.', '_'], ' ', Str::before($pic->email, '@'))) }}</p>

                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('monitoring.export-pdf', ['pic_id' => $pic->id]) }}"
                            class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Export PDF
                        </a>
                        <a href="{{ route('monitoring.training-operations') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PIC Overview Cards -->
        <div class="row mb-4">
            @php
                $picStats = $paymentStats[$pic->id] ?? [];
                $picEvalStats = $evaluationStats[$pic->id] ?? [];
                $picTransactions = $picTransactionDetails[$pic->id] ?? [];

                $totalPrograms = count($picTransactions['programs'] ?? []);
                $totalPayments =
                    ($picStats['pending']['count'] ?? 0) +
                    ($picStats['approve']['count'] ?? 0) +
                    ($picStats['reject']['count'] ?? 0);
                $approvedAmount = $picStats['approve']['amount'] ?? 0;
                $overallAverage = $picEvalStats['overall_pic_average'] ?? 0;
            @endphp

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="h2 text-primary mb-1">{{ $totalPrograms }}</div>
                        <p class="card-text text-muted">Total Programs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="h2 text-info mb-1">{{ $picEvalStats['total_classes'] ?? 0 }}</div>
                        <p class="card-text text-muted">Total Classes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="h2 text-success mb-1">{{ $totalPayments }}</div>
                        <p class="card-text text-muted">Total Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <div
                            class="h2 {{ $overallAverage >= 4 ? 'text-success' : ($overallAverage >= 3 ? 'text-warning' : 'text-danger') }} mb-1">
                            {{ $overallAverage }}/5
                        </div>
                        <p class="card-text text-muted">Overall Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status Breakdown -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Status Breakdown</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($picStats))
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded">
                                        <div class="h4 text-warning mb-1">{{ $picStats['pending']['count'] }}</div>
                                        <small class="text-muted">Pending</small>
                                        <div class="small text-muted">Rp
                                            {{ number_format($picStats['pending']['amount']) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded">
                                        <div class="h4 text-info mb-1">{{ $picStats['check_by_pic']['count'] }}</div>
                                        <small class="text-muted">Check by PIC</small>
                                        <div class="small text-muted">Rp
                                            {{ number_format($picStats['check_by_pic']['amount']) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded">
                                        <div class="h4 text-primary mb-1">{{ $picStats['check_by_manager']['count'] }}
                                        </div>
                                        <small class="text-muted">Check by Manager</small>
                                        <div class="small text-muted">Rp
                                            {{ number_format($picStats['check_by_manager']['amount']) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded bg-light-success">
                                        <div class="h4 text-success mb-1">{{ $picStats['approve']['count'] }}</div>
                                        <small class="text-muted">Approved</small>
                                        <div class="small text-muted">Rp
                                            {{ number_format($picStats['approve']['amount']) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded">
                                        <div class="h4 text-danger mb-1">{{ $picStats['reject']['count'] }}</div>
                                        <small class="text-muted">Rejected</small>
                                        <div class="small text-muted">Rp {{ number_format($picStats['reject']['amount']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No payment data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Evaluations Detail -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Class Evaluation Details</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($picEvalStats) && !empty($picEvalStats['classes']))
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Program Name</th>
                                            <th>Class Name</th>
                                            <th>Duration</th>
                                            <th class="text-center">Materi</th>
                                            <th class="text-center">Pengajar</th>
                                            <th class="text-center">Kepanitiaan</th>
                                            <th class="text-center">Overall Average</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($picEvalStats['classes'] as $class)
                                            <tr>
                                                <td>{{ $class['program_name'] }}</td>
                                                <td>{{ $class['class_name'] }}</td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $class['start_date'] }} - {{ $class['end_date'] }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge {{ $class['scores']['Materi'] >= 4 ? 'bg-success' : ($class['scores']['Materi'] >= 3 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $class['scores']['Materi'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge {{ $class['scores']['Pengajar'] >= 4 ? 'bg-success' : ($class['scores']['Pengajar'] >= 3 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $class['scores']['Pengajar'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge {{ $class['scores']['Kepanitiaan'] >= 4 ? 'bg-success' : ($class['scores']['Kepanitiaan'] >= 3 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $class['scores']['Kepanitiaan'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="fw-bold {{ $class['overall_average'] >= 4 ? 'text-success' : ($class['overall_average'] >= 3 ? 'text-warning' : 'text-danger') }}">
                                                        {{ $class['overall_average'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No evaluation data available for this PIC</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Transaction Details -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Program Transaction Details</h5>
                    </div>
                    <div class="card-body">
                        @if (!empty($picTransactions) && !empty($picTransactions['programs']))
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Program Name</th>
                                            <th class="text-end">Total Budget</th>
                                            <th class="text-center">General Payments</th>
                                            <th class="text-center">Participant Payments</th>
                                            <th class="text-end">Total Approved</th>
                                            <th class="text-end">Remaining Budget</th>
                                            <th class="text-center">Budget Utilization</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($picTransactions['programs'] as $program)
                                            <tr>
                                                <td>
                                                    <div class="fw-medium">{{ $program['program_name'] }}</div>
                                                </td>
                                                <td class="text-end">
                                                    <strong>Rp {{ number_format($program['total_budget']) }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <strong>
                                                        <small class="d-block">
                                                            <span class="text-warning">P:
                                                                {{ $program['general_payments']['Pending'] ?? 0 }}</span>
                                                            |
                                                            <span class="text-success">A:
                                                                {{ $program['general_payments']['Approve'] ?? 0 }}</span>
                                                            |
                                                            <span class="text-danger">R:
                                                                {{ $program['general_payments']['Reject'] ?? 0 }}</span>
                                                        </small>
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <strong>
                                                        <small class="d-block">
                                                            <span class="text-warning">P:
                                                                {{ $program['participant_payments']['Pending'] ?? 0 }}</span>
                                                            |
                                                            <span class="text-success">A:
                                                                {{ $program['participant_payments']['Approve'] ?? 0 }}</span>
                                                            |
                                                            <span class="text-danger">R:
                                                                {{ $program['participant_payments']['Reject'] ?? 0 }}</span>
                                                        </small>
                                                    </strong>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-success">
                                                        Rp {{ number_format($program['total_approved']) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span
                                                        class="{{ $program['remaining_budget'] < 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        Rp {{ number_format($program['remaining_budget']) }}
                                                    </span>
                                                    @if ($program['remaining_budget'] < 0)
                                                        <small class="d-block text-danger">Over Budget!</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="me-2">
                                                            <strong
                                                                class="{{ $program['budget_utilization'] > 100 ? 'text-danger' : ($program['budget_utilization'] > 80 ? 'text-warning' : 'text-success') }}">
                                                                {{ $program['budget_utilization'] }}%
                                                            </strong>
                                                        </div>
                                                        <div class="progress" style="width: 80px; height: 8px;">
                                                            <div class="progress-bar {{ $program['budget_utilization'] > 100 ? 'bg-danger' : ($program['budget_utilization'] > 80 ? 'bg-warning' : 'bg-success') }}"
                                                                style="width: {{ min($program['budget_utilization'], 100) }}%">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('program.show', $program['program_id']) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Row -->
                            @php
                                $totalBudgetSum = collect($picTransactions['programs'])->sum('total_budget');
                                $totalApprovedSum = collect($picTransactions['programs'])->sum('total_approved');
                                $totalRemainingSum = $totalBudgetSum - $totalApprovedSum;
                                $totalUtilization =
                                    $totalBudgetSum > 0 ? round(($totalApprovedSum / $totalBudgetSum) * 100, 2) : 0;
                            @endphp

                            <div class="border-top pt-3 mt-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted mb-1">Total Budget</h6>
                                            <h5 class="mb-0">Rp {{ number_format($totalBudgetSum) }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted mb-1">Total Approved</h6>
                                            <h5 class="mb-0 text-success">Rp {{ number_format($totalApprovedSum) }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted mb-1">Remaining Budget</h6>
                                            <h5 class="mb-0 {{ $totalRemainingSum < 0 ? 'text-danger' : 'text-muted' }}">
                                                Rp {{ number_format($totalRemainingSum) }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted mb-1">Overall Utilization</h6>
                                            <h5
                                                class="mb-0 {{ $totalUtilization > 100 ? 'text-danger' : ($totalUtilization > 80 ? 'text-warning' : 'text-success') }}">
                                                {{ $totalUtilization }}%
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-table fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No transaction details available for this PIC</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-light-success {
            background-color: #d1f2eb !important;
        }

        .progress {
            background-color: #e9ecef;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .badge {
            font-size: 0.75rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .border-top {
            border-top: 2px solid #dee2e6 !important;
        }
    </style>
@endsection
