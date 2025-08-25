@extends('layouts.app')
@section('content')
    <div class="container">

        <!-- Back Button in Top-Left -->
        <a href="{{ route('program.index') }}" class="text-secondary mb-2 d-inline-block" style="text-decoration: none;">
            ← Back to List
        </a>

        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-2 text-primary fw-bold">Detail Program Pelatihan</h2>
            <a href="{{ route('program.export.pdf', $program->id) }}" class="btn btn-success mb-3">
                Export PDF
            </a>
        </div>

        <div class="row">
            <!-- Left Column: Program Detail -->
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Program Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Program Name:</strong> {{ $program->program_name }}</p>
                        <p><strong>Program Location:</strong> {{ $program->program_loc }}</p>
                        <p><strong>Program Duration:</strong> {{ $program->program_duration }}</p>
                        <p><strong>Training Program:</strong> {{ $program->training_program->tp_name ?? '-' }}</p>
                        <p><strong>PIC:</strong>
                            {{ $program->pic ? ucwords(str_replace('.', ' ', Str::before($program->pic->email, '@'))) : '-' }}
                        </p>
                        <p><strong>BI Code:</strong> {{ $program->bi_code }}</p>
                        <p><strong>Program Unit Initiator:</strong> {{ $program->program_unit_int }}</p>
                        <p><strong>Program Type:</strong> {{ $program->program_type }}</p>
                        <p><strong>Activity Type:</strong> {{ $program->program_act_type }}</p>
                        <p><strong>Facilitator:</strong>
                            @if (!empty($program->facilitator_name))
                                {{ $program->facilitator_name }}
                            @else
                                <em>No facilitators found.</em>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Category Budget -->
            <div class="col md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Budget VS Realization</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalBudget = 0;
                            $totalRealization = 0;

                            // Gabungkan semua realization (general + participant) berdasarkan category_fee
                            $realizations = [];
                            foreach ($genPayment->where('status', 'Approve') as $payment) {
                                $category = $payment->category_fee ?? '-';
                                $realizations[$category] = ($realizations[$category] ?? 0) + $payment->amount_fee;
                            }

                            foreach ($partPayment->where('status', 'Approve') as $payment) {
                                $category = $payment->category_fee ?? '-';
                                $realizations[$category] = ($realizations[$category] ?? 0) + $payment->amount_fee;
                            }

                            // Ambil semua kategori unik dari budget dan realization
                            $budgetCategories = $program->category_budget->pluck('category_fee', 'category_fee')->all();
                            $realizationCategories = array_keys($realizations);
                            $allCategories = collect($budgetCategories)
                                ->merge($realizationCategories)
                                ->unique()
                                ->values();
                        @endphp

                        @if ($allCategories->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Category</th>
                                            <th>Budget</th>
                                            <th>Realization</th>
                                            <th>Remaining Budget</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allCategories as $category)
                                            @php
                                                $budgetAmount =
                                                    $program->category_budget->firstWhere('category_fee', $category)
                                                        ->amount_fee ?? 0;
                                                $realizationAmount = $realizations[$category] ?? 0;
                                                $remaining = $budgetAmount - $realizationAmount;

                                                $totalBudget += $budgetAmount;
                                                $totalRealization += $realizationAmount;
                                            @endphp
                                            <tr>
                                                <td>{{ $category }}</td>
                                                <td>Rp{{ number_format($budgetAmount, 2, ',', '.') }}</td>
                                                <td
                                                    class="{{ $realizationAmount <= $budgetAmount ? 'text-success' : 'text-danger' }}">
                                                    Rp{{ number_format($realizationAmount, 2, ',', '.') }}
                                                </td>
                                                <td class="{{ $remaining >= 0 ? 'text-success' : 'text-danger' }}">
                                                    Rp{{ number_format($remaining, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="fw-bold">
                                        @php
                                            $totalRemaining = $totalBudget - $totalRealization;
                                            $percentage =
                                                $totalBudget > 0 ? ($totalRealization / $totalBudget) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>Total</td>
                                            <td>Rp{{ number_format($totalBudget, 2, ',', '.') }}</td>
                                            <td
                                                class="{{ $totalRealization <= $totalBudget ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($totalRealization, 2, ',', '.') }}
                                            </td>
                                            <td class="{{ $totalRemaining >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($totalRemaining, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="text-end mt-2">
                                    @if ($totalBudget > 0)
                                        @if ($totalRealization <= $totalBudget)
                                            <span class="text-success fw-semibold">
                                                {{ number_format($percentage, 2, ',', '.') }}% of budget used
                                            </span>
                                        @else
                                            <span class="text-danger fw-semibold">
                                                Overbudget by {{ number_format($percentage - 100, 2, ',', '.') }}%
                                            </span>
                                        @endif
                                    @endif
                                </div>

                            </div>
                        @else
                            <p><em>No budget or realization data available.</em></p>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Classes & Participants</h5>
            </div>
            <div class="card-body">
                @if ($classes->isNotEmpty())
                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" id="classTab" role="tablist">
                        @foreach ($classes as $index => $class)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $index }}"
                                    data-bs-toggle="tab" data-bs-target="#content-{{ $index }}" type="button"
                                    role="tab" aria-controls="content-{{ $index }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    {{ $class->class_name ?? 'Class' }} - Batch {{ $class->class_batch ?? '-' }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Tab Contents -->
                    <div class="tab-content" id="classTabContent">
                        @foreach ($classes as $index => $class)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                id="content-{{ $index }}" role="tabpanel"
                                aria-labelledby="tab-{{ $index }}">
                                <div class="mb-3">
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Batch:</strong> {{ $class->class_batch ?? '-' }}
                                        </div>
                                        <div class="col-md-4"><strong>Start Date:</strong>
                                            {{ $class->start_date ?? '-' }}</div>
                                        <div class="col-md-4"><strong>End Date:</strong> {{ $class->end_date ?? '-' }}
                                        </div>
                                    </div>

                                    <p class="mt-3"><strong>Participants:</strong></p>

                                    @if ($class->participants->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Karyawan NIK</th>
                                                        <th>Participant Name</th>
                                                        <th>Position</th>
                                                        <th>Working Unit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($class->participants as $participant)
                                                        <tr>
                                                            <td>{{ $participant->karyawan_nik ?? '-' }}</td>
                                                            <td>{{ $participant->participant_name ?? '-' }}</td>
                                                            <td>{{ $participant->participant_position ?? '-' }}</td>
                                                            <td>{{ $participant->participant_working_unit ?? '-' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p><em>No participants.</em></p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $classes->links() }}
                    </div>
                @else
                    <p><em>No classes found.</em></p>
                @endif
            </div>
        </div>

        {{-- eval --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Program Evaluation</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm align-middle text-nowrap">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Kategori</th>
                            <th>Rata-rata Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allEvaluations as $eval)
                            <tr>
                                <td>{{ $eval->eval_cat }}</td>
                                <td class="text-center">
                                    {{ number_format($averageScores[$eval->id] ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada data evaluasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endsection
