@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Edit Final Participant</h3>

        <div class="alert alert-success mb-4">
            <h6><i class="fas fa-check-circle me-2"></i>Peserta Final</h6>
            <p class="mb-0">Peserta ini sudah terkonfirmasi hadir dan berada di tabel final. Anda dapat mengubah nilai test
                dan status.</p>
        </div>

        <form method="POST" action="{{ route('participants.updateFinal', $participant->id) }}">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Data Peserta</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">NIK</label>
                                <input type="text" class="form-control" value="{{ $participant->karyawan_nik }}"
                                    disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" value="{{ $participant->participant_name }}"
                                    disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Posisi</label>
                                <input type="text" class="form-control" value="{{ $participant->participant_position }}"
                                    disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Unit Kerja</label>
                                <input type="text" class="form-control"
                                    value="{{ $participant->participant_working_unit }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Pre Test Score</label>
                                <input type="number" name="pre_test" class="form-control"
                                    value="{{ $participant->pre_test }}" min="0" max="100" step="0.01"
                                    placeholder="Masukkan nilai 0-100">
                                <div class="form-text">Masukkan nilai antara 0-100 (boleh kosong)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Post Test Score</label>
                                <input type="number" name="post_test" class="form-control"
                                    value="{{ $participant->post_test }}" min="0" max="100" step="0.01"
                                    placeholder="Masukkan nilai 0-100">
                                <div class="form-text">Masukkan nilai antara 0-100 (boleh kosong)</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}"
                                    {{ $participant->status === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Status peserta dalam program pelatihan
                        </div>
                    </div>

                    @if (in_array($participant->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                        <div class="alert alert-info">
                            <strong>Info:</strong> Peserta dengan status {{ $participant->status }} dapat dihapus jika
                            diperlukan.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">💾 Save Changes</button>
                <a href="{{ route('participants.byClassIndex', $participant->class_id) }}" class="btn btn-secondary">🔙
                    Back</a>

                @if (in_array($participant->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                    <form
                        action="{{ route('participants.destroyByClass', ['class_id' => $participant->class_id, 'id' => $participant->id]) }}"
                        method="POST" style="display:inline-block;" class="ms-2"
                        onsubmit="return confirm('Yakin ingin menghapus peserta ini? Data akan dihapus permanen.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">🗑️ Delete Participant</button>
                    </form>
                @endif
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validate test scores
            const preTestInput = document.querySelector('input[name="pre_test"]');
            const postTestInput = document.querySelector('input[name="post_test"]');

            function validateScore(input) {
                const value = parseFloat(input.value);
                if (input.value !== '' && (isNaN(value) || value < 0 || value > 100)) {
                    input.setCustomValidity('Nilai harus antara 0-100');
                    input.classList.add('is-invalid');
                } else {
                    input.setCustomValidity('');
                    input.classList.remove('is-invalid');
                }
            }

            preTestInput.addEventListener('input', function() {
                validateScore(this);
            });

            postTestInput.addEventListener('input', function() {
                validateScore(this);
            });
        });
    </script>
@endsection
