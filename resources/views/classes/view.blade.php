@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <a href="{{ route('classes.index') }}" class="text-secondary mb-2 d-inline-block" style="text-decoration: none;">
            ← Back to List
        </a>
        <div class="mb-4">
            <h2 class="fw-bold text-primary">View Class: {{ $class->class_name }}</h2>
        </div>

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-2">
            {{-- Class Details --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white fw-semibold">
                        Class Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-muted small">Program</div>
                                <div class="fw-semibold">{{ $class->programs->program_name }}</div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-muted small">Class Name</div>
                                <div class="fw-semibold">{{ $class->class_name }}</div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-muted small">Location</div>
                                <div class="fw-semibold">
                                    @if ($class->class_loc)
                                        <a href="{{ $class->class_loc }}" target="_blank" class="text-decoration-none">
                                            {{ $class->class_loc }}
                                        </a>
                                    @else
                                        <span>TBA</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-muted small">Start Date</div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($class->start_date)->format('d M Y') }}
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-muted small">End Date</div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($class->end_date)->format('d M Y') }}
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-muted small">Batch</div>
                                <div class="fw-semibold">{{ $class->class_batch }}</div>
                            </div>

                            <div class="col-6">
                                <div class="text-muted small">Class Document</div>
                                <div class="fw-semibold">
                                    @if ($class->class_doc)
                                        <a href="{{ asset('storage/' . $class->class_doc) }}"
                                            class="btn btn-sm btn-outline-success" target="_blank">
                                            📄 Download
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">No document available</span>
                                    @endif
                                </div>
                            </div>

                            @role('participant')
                                <div class="col-6 mb-3">
                                    <div class="text-muted small">Your Attendance Status</div>
                                    <div>
                                        @php
                                            $userId = auth()->id();
                                            $participant = $class->participants->where('user_id', $userId)->first();
                                            $participantTemp = $class->participantstemp
                                                ->where('user_id', $userId)
                                                ->first();
                                        @endphp

                                        @if ($participantTemp)
                                            Invited - Please attend the class first
                                        @elseif ($participant)
                                            {{ $participant->status }}
                                        @else
                                            Not registered
                                        @endif
                                    </div>
                                </div>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>

            {{-- Agenda Details --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-2">
                    <div class="card-header bg-secondary text-white fw-semibold">
                        Agenda Details
                    </div>
                    <div class="card-body">
                        @if ($class->agenda->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Materi</th>
                                            <th>Time</th>
                                            <th>File</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($class->agenda as $index => $agenda)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $agenda->materi_name }}</td>
                                                <td>{{ $agenda->materi_duration }}</td>
                                                <td>
                                                    @if ($agenda->file_path)
                                                        <a href="{{ asset('storage/' . $agenda->file_path) }}"
                                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                                            📄
                                                        </a>
                                                    @else
                                                        <span class="badge bg-secondary">No file</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No agenda available for this class.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @role('pic|manager|executive|superadmin')
            {{-- Participants + Evaluasi --}}
            <div class="card shadow-sm mt-1">
                <div class="card-header bg-info text-white fw-semibold">
                    Participants ({{ $class->participants->count() }})
                </div>
                <div class="card-body table-responsive">
                    @if ($class->participants->isNotEmpty())
                        <table class="table table-bordered table-sm align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>NIK</th>
                                    <th>Participant Name</th>
                                    <th>Position</th>
                                    <th>Working Unit</th>
                                    <th>Attendance Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allParticipants as $participant)
                                    <tr>
                                        <td>{{ $participant->karyawan_nik }}</td>
                                        <td>{{ $participant->participant_name }}</td>
                                        <td>{{ $participant->participant_position }}</td>
                                        <td>{{ $participant->participant_working_unit }}</td>
                                        <td>{{ $participant->status ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No participants registered for this class.</p>
                    @endif
                </div>
            </div>

            {{-- evaluation class --}}
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark fw-semibold">
                    <div class="d-flex justify-content-between align-items-center">
                        Rata-rata Evaluasi Kelas
                        <div class="text-end">
                            <a href="{{ route('evaluation.form', ['class' => $class->id]) }}"
                                class="btn btn-sm btn-success text-white">
                                Add Evaluation
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm align-middle text-nowrap">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th>Rata-rata Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allEvaluations as $eval)
                                <tr>
                                    <td>{{ $eval->eval_cat }}</td>
                                    <td>{{ $eval->eval_desc }}</td>
                                    <td class="text-center">
                                        {{ number_format($averageScores[$eval->id] ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endrole

    @endsection
