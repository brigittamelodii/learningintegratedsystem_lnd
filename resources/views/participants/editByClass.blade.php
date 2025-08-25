@extends('layouts.app')

@section('content')
    <div class="container">
        <br>
        <h3>Edit Participants for Class: <strong>{{ $class->class_name }}</strong></h3>

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Status Information --}}
        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-info-circle me-2"></i>Penjelasan Status:</h6>
            <ul class="mb-0">
                <li><strong>Present:</strong> Peserta hadir, akan dipindah ke tabel final</li>
                <li><strong>Absent-Busy, Absent:</strong> Tidak hadir tapi tetap dihitung dalam attendance rate</li>
                <li><strong>Absent-Sick, Absent-Maternity, Absent-Business:</strong> Undangan dibatalkan, tidak dihitung
                    dalam attendance rate</li>
            </ul>
        </div>

        {{-- Import Section --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-upload me-2"></i>
                    Import Peserta dari File Excel
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('participants.import.final-scores', $class->id) }}" method="POST"
                    enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Format File yang Diperlukan:</h6>
                        <ul class="mb-0">
                            <li><strong>Header kolom:</strong> <code>nama, nik, position, working_unit, pre_test, post_test,
                                    status</code></li>
                            <li><strong>Format file:</strong> Excel (.xlsx, .xls) atau CSV</li>
                            <li><strong>Ukuran maksimal:</strong> 2MB</li>
                            <li><strong>Nilai test:</strong> 0-100 (boleh kosong)</li>
                            <li><strong>Status:</strong> Present, Absent, Absent - Sick, Absent - Busy, Absent - Maternity,
                                Absent - Business</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-10">
                            <div class="mb-3">
                                <label for="file" class="form-label">
                                    <i class="fas fa-file-excel me-1"></i>
                                    Pilih File Excel/CSV <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="file" name="file" id="file"
                                        class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv"
                                        required>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-upload me-1"></i>
                                        <span id="btnText">Upload & Import</span>
                                    </button>
                                </div>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Format yang didukung: .xlsx, .xls, .csv (Maks: 2MB)
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress indicator --}}
                    <div class="mt-3" id="progressContainer" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 100%">
                                Sedang memproses file...
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Manual Edit Form --}}
        <form method="POST" action="{{ route('participants.updateByClass', $class->id) }}">
            @csrf
            @method('PUT')

            {{-- Temporary Participants Table --}}
            @if ($tempParticipants->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">🕗 Invited Participants (Temp Table)</h5>
                        <small class="text-muted">
                            Peserta dengan status Present akan dipindah ke final table.
                            Status Absent-Sick, Absent-Maternity, Absent-Business akan menghapus undangan.
                            Status Absent-Busy dan Absent tetap di temp untuk perhitungan attendance rate.
                        </small>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('participants.temp.export', $class->id) }}" class="btn btn-success mb-3">
                            📤 Export Peserta Sementara (Excel)
                        </a>

                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>NIK</th>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tempParticipants as $p)
                                        <tr
                                            class="{{ $p->status === 'Present'
                                                ? 'table-success'
                                                : (in_array($p->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business'])
                                                    ? 'table-danger'
                                                    : (in_array($p->status, ['Absent - Busy', 'Absent'])
                                                        ? 'table-warning'
                                                        : '')) }}">
                                            <td>{{ $p->karyawan_nik }}</td>
                                            <td>{{ $p->participant_name }}</td>
                                            <td>{{ $p->participant_position }}</td>
                                            <td>{{ $p->participant_working_unit }}</td>
                                            <td>
                                                <select name="participants[{{ $p->participants_id }}][status]"
                                                    class="form-select status-select" data-current="{{ $p->status }}">
                                                    @foreach ($statusOptions as $status)
                                                        <option value="{{ $status }}"
                                                            {{ $p->status == $status ? 'selected' : '' }}>
                                                            {{ $status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden"
                                                    name="participants[{{ $p->participants_id }}][source]" value="temp">
                                                <small class="text-muted d-block mt-1 status-help">
                                                    @if ($p->status === 'Present')
                                                        ✅ Akan dipindah ke final table
                                                    @elseif(in_array($p->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                                                        🗑️ Undangan akan dibatalkan
                                                    @elseif(in_array($p->status, ['Absent - Busy', 'Absent']))
                                                        ⚠️ Tetap dihitung dalam attendance rate
                                                    @else
                                                        📨 Masih dalam status undangan
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <a href="{{ route('participants.editTemp', ['id' => $p->participants_id]) }}"
                                                    class="btn btn-warning btn-sm">✏ Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Final Participants Table --}}
            @if ($finalParticipants->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">✅ Final Participants (Attended)</h5>
                        <small class="text-muted">Peserta yang telah hadir dan bisa diinput nilai test</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>NIK</th>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Unit</th>
                                        <th>Pre Test</th>
                                        <th>Post Test</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($finalParticipants as $p)
                                        <tr class="table-success">
                                            <td>{{ $p->karyawan_nik }}</td>
                                            <td>{{ $p->participant_name }}</td>
                                            <td>{{ $p->participant_position }}</td>
                                            <td>{{ $p->participant_working_unit }}</td>
                                            <td>
                                                <input type="number" name="participants[{{ $p->id }}][pre_test]"
                                                    value="{{ old("participants.$p->id.pre_test", $p->pre_test) }}"
                                                    class="form-control" min="0" max="100" step="0.01"
                                                    placeholder="0-100">
                                            </td>
                                            <td>
                                                <input type="number" name="participants[{{ $p->id }}][post_test]"
                                                    value="{{ old("participants.$p->id.post_test", $p->post_test) }}"
                                                    class="form-control" min="0" max="100" step="0.01"
                                                    placeholder="0-100">
                                            </td>
                                            <td>
                                                <select name="participants[{{ $p->id }}][status]"
                                                    class="form-select">
                                                    @foreach ($statusOptions as $status)
                                                        <option value="{{ $status }}"
                                                            {{ $p->status == $status ? 'selected' : '' }}>
                                                            {{ $status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="participants[{{ $p->id }}][source]"
                                                    value="main">
                                            </td>
                                            <td>
                                                <a href="{{ route('participants.editFinal', ['id' => $p->id]) }}"
                                                    class="btn btn-warning btn-sm">✏ Edit</a>
                                                @if (in_array($p->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                                                    <form
                                                        action="{{ route('participants.destroyByClass', ['class_id' => $class->id, 'id' => $p->id]) }}"
                                                        method="POST" style="display:inline-block;" class="mt-1"
                                                        onsubmit="return confirm('Yakin ingin menghapus peserta ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm">🗑</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Submit and Back Buttons --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">💾 Save All Changes</button>
                <a href="{{ route('participants.index') }}" class="btn btn-secondary">🔙 Back</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('importForm');
            const fileInput = document.getElementById('file');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const progressContainer = document.getElementById('progressContainer');

            // File validation
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Check file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File terlalu besar! Maksimal ukuran file adalah 2MB.');
                        this.value = '';
                        return;
                    }

                    // Check file type
                    const allowedTypes = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv'
                    ];

                    const fileName = file.name.toLowerCase();
                    const fileExtension = fileName.split('.').pop();
                    const allowedExtensions = ['xlsx', 'xls', 'csv'];

                    if (!allowedExtensions.includes(fileExtension)) {
                        alert('Format file tidak didukung! Gunakan file Excel (.xlsx, .xls) atau CSV.');
                        this.value = '';
                        return;
                    }

                    console.log('File valid:', file.name, 'Size:', (file.size / 1024 / 1024).toFixed(2) +
                        ' MB');
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                if (!file) {
                    e.preventDefault();
                    alert('Silakan pilih file terlebih dahulu!');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                btnText.textContent = 'Sedang memproses...';
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + btnText.textContent;
                progressContainer.style.display = 'block';

                console.log('Starting import process...');
            });

            // Status change handler for temp participants
            const statusSelects = document.querySelectorAll('.status-select');
            statusSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    const newStatus = this.value;
                    const row = this.closest('tr');
                    const helpText = row.querySelector('.status-help');

                    // Remove existing classes
                    row.classList.remove('table-success', 'table-danger', 'table-warning');

                    // Update visual feedback and help text
                    if (newStatus === 'Present') {
                        row.classList.add('table-success');
                        helpText.innerHTML = '✅ Akan dipindah ke final table';
                    } else if (['Absent - Sick', 'Absent - Maternity', 'Absent - Business']
                        .includes(newStatus)) {
                        row.classList.add('table-danger');
                        helpText.innerHTML = '🗑️ Undangan akan dibatalkan';
                    } else if (['Absent - Busy', 'Absent'].includes(newStatus)) {
                        row.classList.add('table-warning');
                        helpText.innerHTML = '⚠️ Tetap dihitung dalam attendance rate';
                    } else if (newStatus === 'Invited') {
                        helpText.innerHTML = '📨 Masih dalam status undangan';
                    }
                });
            });

            // Auto hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (alert.classList.contains('alert-success')) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                });
            }, 5000);
        });
    </script>
@endsection
