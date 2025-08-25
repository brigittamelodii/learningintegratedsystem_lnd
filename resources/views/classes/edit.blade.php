@extends('layouts.app')
@section('content')
    <div class="container mt-2">
        <a href="{{ route('classes.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>
        {{-- Title --}}
        <h2 class="mb-2 text-primary"><strong>Edit Class: {{ $class->class_name }}</strong>
        </h2>

        {{-- Success Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Edit Class Form --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <strong>Class Information</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('classes.update', $class->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="class">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Program</label>
                            <input type="text" class="form-control bg-light" value="{{ $class->programs->program_name }}"
                                disabled>
                            <input type="hidden" name="program_id" value="{{ $class->program_id }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Class Name</label>
                            <input type="text" name="class_name" class="form-control"
                                value="{{ old('class_name', $class->class_name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Batch</label>
                            <input type="text" name="class_batch" class="form-control"
                                value="{{ old('class_batch', $class->class_batch) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ old('start_date', $class->start_date) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ old('end_date', $class->end_date) }}" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Class Document (optional)</label>
                            <input type="file" name="class_doc" class="form-control" accept=".pdf,.doc,.docx">
                            @if ($class->class_doc)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Current: <a href="{{ asset('storage/' . $class->class_doc) }}" target="_blank"><i
                                                class="bi bi-file-earmark-text me-1"></i>Download</a>
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">
                        Update Class
                    </button>
                </form>
            </div>
        </div>

        {{-- Agenda Saat Ini --}}
        {{-- Agenda --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <strong>Agenda</strong>
            </div>
            <div class="card-body">
                {{-- Update Agenda --}}
                @if ($class->agenda->count())
                    <form method="POST" action="{{ route('classes.updateAgenda', $class->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @foreach ($class->agenda as $agenda)
                            <div class="row g-3 align-items-center mb-3">
                                <input type="hidden" name="agenda_ids[]" value="{{ $agenda->id }}">

                                <div class="col-md-4">
                                    <small class="text-muted">Nama Materi</small>
                                    <input type="text" name="materi_name[]" class="form-control"
                                        value="{{ $agenda->materi_name }}" required>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Durasi (HH:MM)</small>
                                    <input type="time" name="materi_duration[]" class="form-control"
                                        value="{{ $agenda->materi_duration }}" required>
                                </div>
                                <div class="col-md-5">
                                    <small class="d-block mt-1">
                                        <a href="{{ asset('storage/' . $agenda->file_path) }}" target="_blank">
                                            <i class="bi bi-eye me-1"></i>Lihat File
                                        </a>
                                    </small>
                                    <input type="file" name="file_path[]" class="form-control">
                                    @if ($agenda->file_path)
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary mt-1">
                            Update Agenda
                        </button>
                    </form>
                @else
                    <p class="text-muted">Tidak ada agenda saat ini.</p>
                @endif

                <hr class="my-4">

                {{-- Tambah Agenda Baru --}}
                <h6 class="mb-3 text-success"><i class="bi bi-plus-circle me-1"></i>Tambah Agenda Baru</h6>

                @if ($errors->has('error'))
                    <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                @endif

                <form method="POST" action="{{ route('classes.storeAgenda', $class->id) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div id="agenda-container">
                        <div class="agenda-item row g-3 mb-3">
                            <div class="col-md-5">
                                <input type="text" name="materi_name[]" class="form-control"
                                    placeholder="Nama Materi" required>
                            </div>
                            <div class="col-md-3">
                                <input type="time" name="materi_duration[]" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <input type="file" name="file_path[]" class="form-control"
                                    accept=".pdf,.docx,.xlsx,.jpg,.png">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="submit" class="btn btn-success btn-sm">
                            Save New Agenda
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addAgendaRow()">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Baris
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function addAgendaRow() {
            const container = document.getElementById('agenda-container');
            const newRow = `
            <div class="agenda-item row g-3 mb-3">
                <div class="col-md-5">
                    <input type="text" name="materi_name[]" class="form-control" placeholder="Nama Materi" required>
                </div>
                <div class="col-md-3">
                    <input type="time" name="materi_duration[]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <input type="file" name="file_path[]" class="form-control" accept=".pdf,.docx,.xlsx,.jpg,.png">
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', newRow);
        }
    </script>
@endsection
