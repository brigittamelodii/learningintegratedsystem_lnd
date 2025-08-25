@extends('layouts.app')

@section('title', 'Training Operations Monitoring')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Training Operations Monitoring</h2>
                        <p class="text-muted">Monitor PIC performance, evaluations, and payment statistics</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('monitoring.export-pdf', request()->query()) }}" class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('monitoring.training-operations') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="user_id" class="form-label">Filter by PIC</label>
                                    <select name="user_id" id="user_id" class="form-select">
                                        <option value="">All PICs</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name ?? ucwords(str_replace(['.', '_'], ' ', Str::before($user->email, '@'))) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="{{ route('monitoring.training-operations') }}"
                                        class="btn btn-outline-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($selectedPic)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Showing data for PIC:
                <strong>{{ $selectedPic->name ?? ucwords(str_replace(['.', '_'], ' ', Str::before($selectedPic->email, '@'))) }}</strong>
            </div>
        @endif

        <!-- Payment Statistics Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Statistics Overview</h5>
                    </div>
                    <div class="card-body">
                        @if (count($paymentStats) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>PIC Name</th>
                                            <th class="text-center">Pending</th>
                                            <th class="text-center">Check by PIC</th>
                                            <th class="text-center">Check by Manager</th>
                                            <th class="text-center">Approved</th>
                                            <th class="text-center">Rejected</th>
                                            <th class="text-end">Total Amount (Approved)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paymentStats as $picId => $stats)
                                            @php
                                                $picUser = $users->firstWhere('id', $picId);
                                                $picName = $stats['pic_name'];

                                                $pending = $stats['pending'] ?? ['count' => 0, 'amount' => 0];
                                                $checkByPic = $stats['check_by_pic'] ?? ['count' => 0, 'amount' => 0];
                                                $checkByManager = $stats['check_by_manager'] ?? [
                                                    'count' => 0,
                                                    'amount' => 0,
                                                ];
                                                $approve = $stats['approve'] ?? ['count' => 0, 'amount' => 0];
                                                $reject = $stats['reject'] ?? ['count' => 0, 'amount' => 0];
                                            @endphp

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('monitoring.pic-detail', $picId) }}">
                                                        {{ $picName }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $isPendingHigh = $pending['count'] > 10;
                                                    @endphp
                                                    <span
                                                        class="badge {{ $isPendingHigh ? 'bg-danger fw-bold' : 'bg-warning' }}">
                                                        {{ $pending['count'] }}
                                                    </span>
                                                    <small
                                                        class="d-block text-muted {{ $isPendingHigh ? 'fw-bold text-danger' : '' }}">
                                                        Rp {{ number_format($pending['amount']) }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $isCheckByPicHigh = $checkByPic['count'] > 10;
                                                    @endphp
                                                    <span
                                                        class="badge {{ $isCheckByPicHigh ? 'bg-danger fw-bold' : 'bg-primary' }}">
                                                        {{ $checkByPic['count'] }}
                                                    </span>
                                                    <small
                                                        class="d-block text-muted {{ $isCheckByPicHigh ? 'fw-bold text-danger' : '' }}">
                                                        Rp {{ number_format($checkByPic['amount']) }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $checkByManager['count'] }}</span>
                                                    <small class="d-block text-muted">Rp
                                                        {{ number_format($checkByManager['amount']) }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">{{ $approve['count'] }}</span>
                                                    <small class="d-block text-muted">Rp
                                                        {{ number_format($approve['amount']) }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger">{{ $reject['count'] }}</span>
                                                    <small class="d-block text-muted">Rp
                                                        {{ number_format($reject['amount']) }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="text-success">Rp
                                                        {{ number_format($approve['amount']) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No payment statistics available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Class Evaluation Summary</h5>
                    </div>
                    <div class="card-body">
                        @if (count($evaluationStats) > 0)
                            <div class="row">
                                @foreach ($evaluationStats as $picId => $evalData)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $evalData['pic_name'] }}</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <small class="text-muted">Total Classes:</small>
                                                    <span class="badge bg-primary">{{ $evalData['total_classes'] }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <small class="text-muted">Overall Average:</small>
                                                    <span
                                                        class="fw-bold {{ $evalData['overall_pic_average'] >= 4 ? 'text-success' : ($evalData['overall_pic_average'] >= 3 ? 'text-warning' : 'text-danger') }}">
                                                        {{ $evalData['overall_pic_average'] }}/5
                                                    </span>
                                                </div>
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar {{ $evalData['overall_pic_average'] >= 4 ? 'bg-success' : ($evalData['overall_pic_average'] >= 3 ? 'bg-warning' : 'bg-danger') }}"
                                                        style="width: {{ ($evalData['overall_pic_average'] / 5) * 100 }}%">
                                                    </div>
                                                </div>
                                                <a href="{{ route('monitoring.pic-detail', $picId) }}"
                                                    class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No evaluation data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Transaction Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Program Transaction Details</h5>
                    </div>
                    <div class="card-body">
                        @if (count($picTransactionDetails) > 0)
                            @foreach ($picTransactionDetails as $picId => $picData)
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user"></i> {{ $picData['pic_name'] }}
                                        <span class="badge bg-secondary ms-2">{{ count($picData['programs']) }}
                                            Programs</span>
                                    </h6>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Program Name</th>
                                                    <th class="text-end">Total Budget</th>
                                                    <th class="text-center">General Payments</th>
                                                    <th class="text-center">Participant Payments</th>
                                                    <th class="text-end">Total Approved</th>
                                                    <th class="text-end">Remaining Budget</th>
                                                    <th class="text-center">Utilization</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($picData['programs'] as $program)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('program.show', $program['program_id']) }}"
                                                                class="text-decoration-none">
                                                                {{ $program['program_name'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">Rp
                                                            {{ number_format($program['total_budget']) }}</td>
                                                        <td class="text-center">
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
                                                        </td>
                                                        <td class="text-center">
                                                            <small class="d-block">
                                                                <span class="text-warning">P:
                                                                    {{ $program['participant_payments']['Pending'] ?? 0 }}</span>
                                                                |
                                                                <span class="text-success">A:
                                                                    {{ $program['participant_payments']['Approve'] ?? 0 }}</span>
                                                                | <span class="text-danger">R:
                                                                    {{ $program['participant_payments']['Reject'] ?? 0 }}</span>
                                                            </small>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="fw-bold text-success">Rp
                                                                {{ number_format($program['total_approved']) }}</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span
                                                                class="{{ $program['remaining_budget'] < 0 ? 'text-danger' : 'text-muted' }}">
                                                                Rp {{ number_format($program['remaining_budget']) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex align-items-center justify-content-center">
                                                                <small
                                                                    class="me-2">{{ $program['budget_utilization'] }}%</small>
                                                                <div class="progress" style="width: 60px; height: 6px;">
                                                                    <div class="progress-bar {{ $program['budget_utilization'] > 100 ? 'bg-danger' : ($program['budget_utilization'] > 80 ? 'bg-warning' : 'bg-success') }}"
                                                                        style="width: {{ min($program['budget_utilization'], 100) }}%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-table fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No transaction details available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        @if (count($paymentStats) > 0 || count($evaluationStats) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Overall Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary">{{ count($paymentStats) }}</h4>
                                        <small class="text-muted">Active PICs</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success">
                                            @php
                                                $totalApproved = 0;
                                                foreach ($paymentStats as $stats) {
                                                    $totalApproved += $stats['approve']['amount'] ?? 0;
                                                }
                                            @endphp
                                            Rp {{ number_format($totalApproved) }}
                                        </h4>
                                        <small class="text-muted">Total Approved Amount</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning">
                                            @php
                                                $totalPending = 0;
                                                foreach ($paymentStats as $stats) {
                                                    $totalPending += $stats['pending']['count'] ?? 0;
                                                    $totalPending += $stats['check_by_pic']['count'] ?? 0;
                                                    $totalPending += $stats['check_by_manager']['count'] ?? 0;
                                                }
                                            @endphp
                                            {{ $totalPending }}
                                        </h4>
                                        <small class="text-muted">Pending Transactions</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info">
                                            @php
                                                $averageRating = 0;
                                                $totalRatings = 0;
                                                foreach ($evaluationStats as $eval) {
                                                    $averageRating += $eval['overall_pic_average'];
                                                    $totalRatings++;
                                                }
                                                $overallAverage =
                                                    $totalRatings > 0 ? round($averageRating / $totalRatings, 2) : 0;
                                            @endphp
                                            {{ $overallAverage }}/5
                                        </h4>
                                        <small class="text-muted">Overall Rating Average</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .progress {
            background-color: #e9ecef;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge {
            font-size: 0.75rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.025);
        }

        .card-title {
            color: #495057;
            font-weight: 600;
        }

        .border-bottom {
            border-bottom: 2px solid #dee2e6 !important;
        }

        .text-decoration-none:hover {
            text-decoration: underline !important;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }

        .card-body .row .col-md-3 {
            border-right: 1px solid #dee2e6;
        }

        .card-body .row .col-md-3:last-child {
            border-right: none;
        }

        @media (max-width: 768px) {
            .card-body .row .col-md-3 {
                border-right: none;
                border-bottom: 1px solid #dee2e6;
                margin-bottom: 1rem;
                padding-bottom: 1rem;
            }

            .card-body .row .col-md-3:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }
        }
    </style>
@endsection
