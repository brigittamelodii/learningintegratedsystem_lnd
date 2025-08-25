<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Training Operations Monitoring Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 10px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 14px;
            border-left: 4px solid #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: #28a745;
        }

        .text-warning {
            color: #ffc107;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #6c757d;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .summary-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .pic-section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .pic-header {
            background-color: #e9ecef;
            padding: 8px;
            margin: -10px -10px 15px -10px;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            background-color: white;
        }

        .page-break {
            page-break-before: always;
        }

        .progress-bar {
            display: inline-block;
            width: 50px;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            position: relative;
            margin-left: 5px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .progress-success {
            background-color: #28a745;
        }

        .progress-warning {
            background-color: #ffc107;
        }

        .progress-danger {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Training Operations Monitoring Report</h1>
        <p>Generated on {{ date('d M Y H:i:s') }}</p>
        @if ($selectedPic)
            <p><strong>PIC: {{ $selectedPic->name }}</strong></p>
        @else
            <p>All PICs Overview</p>
        @endif
    </div>

    <!-- Summary Section -->
    @if (!$selectedPic)
        <div class="section">
            <div class="section-title">Executive Summary</div>
            <div class="summary-box">
                <div class="summary-row">
                    <span>Total PICs:</span>
                    <strong>{{ count($paymentStats) }}</strong>
                </div>
                <div class="summary-row">
                    <span>Total Programs:</span>
                    <strong>{{ collect($picTransactionDetails)->sum(function ($pic) {return count($pic['programs'] ?? []);}) }}</strong>
                </div>
                <div class="summary-row">
                    <span>Total Classes with Evaluation:</span>
                    <strong>{{ collect($evaluationStats)->sum('total_classes') }}</strong>
                </div>
                @php
                    $totalApproved = collect($paymentStats)->sum(function ($pic) {
                        return $pic['approve']['amount'] ?? 0;
                    });
                    $totalPending = collect($paymentStats)->sum(function ($pic) {
                        return $pic['pending']['amount'] ?? 0;
                    });
                @endphp
                <div class="summary-row">
                    <span>Total Approved Amount:</span>
                    <strong class="text-success">Rp {{ number_format($totalApproved) }}</strong>
                </div>
                <div class="summary-row">
                    <span>Total Pending Amount:</span>
                    <strong class="text-warning">Rp {{ number_format($totalPending) }}</strong>
                </div>
            </div>
        </div>
    @endif

    <!-- Payment Statistics Section -->
    <div class="section">
        <div class="section-title">Payment Statistics Overview</div>

        @if (count($paymentStats) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="20%">PIC Name</th>
                        <th width="12%">Pending</th>
                        <th width="12%">Check by PIC</th>
                        <th width="12%">Check by Manager</th>
                        <th width="12%">Approved</th>
                        <th width="12%">Rejected</th>
                        <th width="20%">Total Approved Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paymentStats as $picId => $stats)
                        <tr>
                            <td>{{ $stats['pic_name'] }}</td>
                            <td class="text-center">
                                <span class="badge badge-warning">{{ $stats['pending']['count'] }}</span><br>
                                <small>Rp {{ number_format($stats['pending']['amount']) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $stats['check_by_pic']['count'] }}</span><br>
                                <small>Rp {{ number_format($stats['check_by_pic']['amount']) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary">{{ $stats['check_by_manager']['count'] }}</span><br>
                                <small>Rp {{ number_format($stats['check_by_manager']['amount']) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">{{ $stats['approve']['count'] }}</span><br>
                                <small>Rp {{ number_format($stats['approve']['amount']) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger">{{ $stats['reject']['count'] }}</span><br>
                                <small>Rp {{ number_format($stats['reject']['amount']) }}</small>
                            </td>
                            <td class="text-right">
                                <strong>Rp {{ number_format($stats['approve']['amount']) }}</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center text-muted">No payment statistics available</p>
        @endif
    </div>

    <!-- Evaluation Statistics Section -->
    <div class="section">
        <div class="section-title">Class Evaluation Summary</div>

        @if (count($evaluationStats) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="25%">PIC Name</th>
                        <th width="15%">Total Classes</th>
                        <th width="20%">Overall Average</th>
                        <th width="40%">Performance Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($evaluationStats as $picId => $evalData)
                        <tr>
                            <td>{{ $evalData['pic_name'] }}</td>
                            <td class="text-center">{{ $evalData['total_classes'] }}</td>
                            <td class="text-center">
                                <strong
                                    class="{{ $evalData['overall_pic_average'] >= 4 ? 'text-success' : ($evalData['overall_pic_average'] >= 3 ? 'text-warning' : 'text-danger') }}">
                                    {{ $evalData['overall_pic_average'] }}/5
                                </strong>
                            </td>
                            <td>
                                @php
                                    $percentage = ($evalData['overall_pic_average'] / 5) * 100;
                                    $colorClass =
                                        $evalData['overall_pic_average'] >= 4
                                            ? 'progress-success'
                                            : ($evalData['overall_pic_average'] >= 3
                                                ? 'progress-warning'
                                                : 'progress-danger');
                                @endphp
                                {{ number_format($percentage, 1) }}%
                                <div class="progress-bar">
                                    <div class="progress-fill {{ $colorClass }}"
                                        style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center text-muted">No evaluation data available</p>
        @endif
    </div>

    <!-- Detailed Class Evaluations (if specific PIC selected) -->
    @if ($selectedPic && isset($evaluationStats[$selectedPic->id]) && !empty($evaluationStats[$selectedPic->id]['classes']))
        <div class="section page-break">
            <div class="section-title">Detailed Class Evaluations - {{ $selectedPic->name }}</div>

            <table>
                <thead>
                    <tr>
                        <th width="25%">Class Name</th>
                        <th width="25%">Program Name</th>
                        <th width="15%">Duration</th>
                        <th width="10%">Materi</th>
                        <th width="10%">Pengajar</th>
                        <th width="10%">Kepanitiaan</th>
                        <th width="10%">Average</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($evaluationStats[$selectedPic->id]['classes'] as $class)
                        <tr>
                            <td>{{ $class['class_name'] }}</td>
                            <td>{{ $class['program_name'] }}</td>
                            <td class="text-center">
                                <small>{{ date('d/m/Y', strtotime($class['start_date'])) }} -
                                    {{ date('d/m/Y', strtotime($class['end_date'])) }}</small>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $class['scores']['Materi'] >= 4 ? 'badge-success' : ($class['scores']['Materi'] >= 3 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $class['scores']['Materi'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $class['scores']['Pengajar'] >= 4 ? 'badge-success' : ($class['scores']['Pengajar'] >= 3 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $class['scores']['Pengajar'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $class['scores']['Kepanitiaan'] >= 4 ? 'badge-success' : ($class['scores']['Kepanitiaan'] >= 3 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $class['scores']['Kepanitiaan'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <strong
                                    class="{{ $class['overall_average'] >= 4 ? 'text-success' : ($class['overall_average'] >= 3 ? 'text-warning' : 'text-danger') }}">
                                    {{ $class['overall_average'] }}
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Program Transaction Details -->
    <div class="section {{ $selectedPic ? '' : 'page-break' }}">
        <div class="section-title">Program Transaction Details</div>

        @if (count($picTransactionDetails) > 0)
            @foreach ($picTransactionDetails as $picId => $picData)
                <div class="pic-section">
                    <div class="pic-header">
                        {{ $picData['pic_name'] }} - {{ count($picData['programs']) }} Programs
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th width="25%">Program Name</th>
                                <th width="12%">Total Budget</th>
                                <th width="12%">General Payments</th>
                                <th width="12%">Participant Payments</th>
                                <th width="12%">Total Approved</th>
                                <th width="12%">Remaining Budget</th>
                                <th width="15%">Utilization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($picData['programs'] as $program)
                                <tr>
                                    <td>{{ $program['program_name'] }}</td>
                                    <td class="text-right">Rp {{ number_format($program['total_budget']) }}</td>
                                    <td class="text-center">
                                        <small>
                                            A: {{ $program['general_payments']['Approve'] ?? 0 }} |
                                            P: {{ $program['general_payments']['Pending'] ?? 0 }} |
                                            R: {{ $program['general_payments']['Reject'] ?? 0 }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <small>
                                            A: {{ $program['participant_payments']['Approve'] ?? 0 }} |
                                            P: {{ $program['participant_payments']['Pending'] ?? 0 }} |
                                            R: {{ $program['participant_payments']['Reject'] ?? 0 }}
                                        </small>
                                    </td>
                                    <td class="text-right">
                                        <strong class="text-success">Rp
                                            {{ number_format($program['total_approved']) }}</strong>
                                    </td>
                                    <td class="text-right">
                                        <span
                                            class="{{ $program['remaining_budget'] < 0 ? 'text-danger' : 'text-muted' }}">
                                            Rp {{ number_format($program['remaining_budget']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong
                                            class="{{ $program['budget_utilization'] > 100 ? 'text-danger' : ($program['budget_utilization'] > 80 ? 'text-warning' : 'text-success') }}">
                                            {{ $program['budget_utilization'] }}%
                                        </strong>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $program['budget_utilization'] > 100 ? 'progress-danger' : ($program['budget_utilization'] > 80 ? 'progress-warning' : 'progress-success') }}"
                                                style="width: {{ min($program['budget_utilization'], 100) }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Summary for this PIC -->
                    @php
                        $picTotalBudget = collect($picData['programs'])->sum('total_budget');
                        $picTotalApproved = collect($picData['programs'])->sum('total_approved');
                        $picTotalRemaining = $picTotalBudget - $picTotalApproved;
                        $picUtilization =
                            $picTotalBudget > 0 ? round(($picTotalApproved / $picTotalBudget) * 100, 2) : 0;
                    @endphp

                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Total Budget:</span>
                            <strong>Rp {{ number_format($picTotalBudget) }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Total Approved:</span>
                            <strong class="text-success">Rp {{ number_format($picTotalApproved) }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Remaining Budget:</span>
                            <strong class="{{ $picTotalRemaining < 0 ? 'text-danger' : 'text-muted' }}">
                                Rp {{ number_format($picTotalRemaining) }}
                            </strong>
                        </div>
                        <div class="summary-row">
                            <span>Overall Utilization:</span>
                            <strong
                                class="{{ $picUtilization > 100 ? 'text-danger' : ($picUtilization > 80 ? 'text-warning' : 'text-success') }}">
                                {{ $picUtilization }}%
                            </strong>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-center text-muted">No transaction details available</p>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Training Operations Monitoring Report - {{ date('d M Y H:i:s') }}</p>
        <p>Generated by Training Management System</p>
    </div>
</body>

</html>
