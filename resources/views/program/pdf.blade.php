<!DOCTYPE html>
<html>

<head>
    <title>Laporan Program</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11.5px;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 50px;
            margin-right: 15px;
        }
        h2 {
            margin: 0;
        }
        .section-title {
            font-weight: bold;
            background-color: #e3f2fd;
            padding: 8px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f8f9fa;
            text-align: center;
        }

        .class-block {
            margin-bottom: 30px;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #666;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <img src="{{ public_path('img/image.png') }}" alt="Logo">
        <div>
            <h2 class="text-center">Laporan Program Pelatihan</h2>
            <p><small>Tanggal Cetak: {{ now()->format('d/m/Y') }}</small></p>
        </div>
    </div>

    {{-- Detail Program --}}
    <div class="section-title">Informasi Program</div>
    <table>
        <tbody>
            <tr>
                <td><strong>Program Name</strong></td>
                <td>{{ $program->program_name }}</td>
            </tr>
            <tr>
                <td><strong>Location</strong></td>
                <td>{{ $program->program_loc }}</td>
            </tr>
            <tr>
                <td><strong>Duration</strong></td>
                <td>{{ $program->program_duration }}</td>
            </tr>
            <tr>
                <td><strong>Training Program</strong></td>
                <td>{{ $program->training_program->tp_name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>PIC</strong></td>
                <td>{{ $program->user ? ucwords(str_replace('.', ' ', Str::before($program->user->email, '@'))) : '-' }}
                </td>
            </tr>
            <tr>
                <td><strong>Facilitator</strong></td>
                <td>{{ $program->facilitator_name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>BI Code</strong></td>
                <td>{{ $program->bi_code }}</td>
            </tr>
            <tr>
                <td><strong>Program Unit Initiator</strong></td>
                <td>{{ $program->program_unit_int }}</td>
            </tr>
            <tr>
                <td><strong>Program Type</strong></td>
                <td>{{ $program->program_type }}</td>
            </tr>
            <tr>
                <td><strong>Activity Type</strong></td>
                <td>{{ $program->program_act_type }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Budget --}}
    <div class="section-title">Budget vs Realization</div>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Budget</th>
                <th>Realization</th>
                <th>Remaining</th>
            </tr>
        </thead>
        <tbody>
            @php
                $realizations = [];
                $totalBudget = 0;
                $totalRealization = 0;

                foreach ($genPayment->where('status', 'Approve') as $payment) {
                    $cat = $payment->category_fee ?? '-';
                    $realizations[$cat] = ($realizations[$cat] ?? 0) + $payment->amount_fee;
                }

                foreach ($partPayment->where('status', 'Approve') as $payment) {
                    $cat = $payment->category_fee ?? '-';
                    $realizations[$cat] = ($realizations[$cat] ?? 0) + $payment->amount_fee;
                }

                $budgetCats = $program->category_budget->pluck('category_fee', 'category_fee')->all();
                $allCats = collect($budgetCats)->merge(array_keys($realizations))->unique()->values();
            @endphp

            @foreach ($allCats as $cat)
                @php
                    $budget = $program->category_budget->firstWhere('category_fee', $cat)->amount_fee ?? 0;
                    $real = $realizations[$cat] ?? 0;
                    $remaining = $budget - $real;
                    $totalBudget += $budget;
                    $totalRealization += $real;
                @endphp
                <tr>
                    <td>{{ $cat }}</td>
                    <td>Rp{{ number_format($budget, 2, ',', '.') }}</td>
                    <td>Rp{{ number_format($real, 2, ',', '.') }}</td>
                    <td>Rp{{ number_format($remaining, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr style="font-weight: bold;">
                <td>Total</td>
                <td>Rp{{ number_format($totalBudget, 2, ',', '.') }}</td>
                <td>Rp{{ number_format($totalRealization, 2, ',', '.') }}</td>
                <td>Rp{{ number_format($totalBudget - $totalRealization, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Classes --}}
    <div class="section-title">Classes & Participants</div>
    @forelse ($program->classes as $index => $class)
        <div class="class-block">
            <p><strong>Class {{ $index + 1 }}:</strong> {{ $class->class_name }} - Batch {{ $class->class_batch }}
            </p>
            <table>
                <tr>
                    <td><strong>Start Date</strong></td>
                    <td>{{ $class->start_date }}</td>
                    <td><strong>End Date</strong></td>
                    <td>{{ $class->end_date }}</td>
                </tr>
            </table>

            @if ($class->participants->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Karyawan NIK</th>
                            <th>Participant Name</th>
                            <th>Position</th>
                            <th>Working Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($class->participants as $pIndex => $participant)
                            <tr>
                                <td class="text-center">{{ $pIndex + 1 }}</td>
                                <td>{{ $participant->karyawan_nik ?? '-' }}</td>
                                <td>{{ $participant->participant_name ?? '-' }}</td>
                                <td>{{ $participant->participant_position ?? '-' }}</td>
                                <td>{{ $participant->participant_working_unit ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted"><em>No participants in this class.</em></p>
            @endif
        </div>
    @empty
        <p class="text-muted"><em>No classes found.</em></p>
    @endforelse

    {{-- Evaluasi --}}
    <div class="section-title">Evaluasi Program</div>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Rata-rata Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allEvaluations as $eval)
                <tr>
                    <td>{{ $eval->eval_cat }}</td>
                    <td class="text-center">{{ number_format($averageScores[$eval->id] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
