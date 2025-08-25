@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Edit Temporary Participant</h3>

        {{-- Status Information --}}
        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-info-circle me-2"></i>Penjelasan Status:</h6>
            <ul class="mb-0">
                <li><strong>Present:</strong> Peserta hadir, akan dipindah ke tabel final</li>
                <li><strong>Absent-Busy, Absent:</strong> Tidak hadir tapi tetap dihitung dalam attendance rate</li>
                <li><strong>Absent-Sick, Absent-Maternity, Absent-Business:</strong> Undangan dibatalkan, peserta dihapus
                    dari temp</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('participants.updateTemp', $participant->participants_id) }}">
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

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" id="statusSelect" required>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" {{ $participant->status === $status ? 'selected' : '' }}
                                    data-description="
                                        @if ($status === 'Present') Peserta hadir, akan dipindah ke tabel final
                                        @elseif(in_array($status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                                            Undangan dibatalkan, peserta dihapus dari temp
                                        @elseif(in_array($status, ['Absent - Busy', 'Absent']))
                                            Tidak hadir tapi tetap dihitung dalam attendance rate
                                        @else
                                            Masih dalam status undangan @endif
                                    ">
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" id="statusDescription">
                            @if ($participant->status === 'Present')
                                ✅ Peserta hadir, akan dipindah ke tabel final
                            @elseif(in_array($participant->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                                🗑️ Undangan dibatalkan, peserta dihapus dari temp
                            @elseif(in_array($participant->status, ['Absent - Busy', 'Absent']))
                                ⚠️ Tidak hadir tapi tetap dihitung dalam attendance rate
                            @else
                                📨 Masih dalam status undangan
                            @endif
                        </div>
                    </div>

                    @if (in_array($participant->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                        <div class="alert alert-warning">
                            <strong>Perhatian:</strong> Jika Anda mengkonfirmasi status ini, peserta akan dihapus dari
                            daftar temporary dan tidak akan dihitung dalam attendance rate.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success"
                    onclick="return confirm('Apakah Anda yakin ingin menyimpan perubahan status ini?')">
                    💾 Save
                </button>
                <a href="{{ route('participants.byClassIndex', $participant->class_id) }}" class="btn btn-secondary">🔙
                    Back</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('statusSelect');
            const statusDescription = document.getElementById('statusDescription');

            statusSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const description = selectedOption.getAttribute('data-description').trim();
                const status = this.value;

                let icon = '📨';
                if (status === 'Present') {
                    icon = '✅';
                } else if (['Absent - Sick', 'Absent - Maternity', 'Absent - Business'].includes(status)) {
                    icon = '🗑️';
                } else if (['Absent - Busy', 'Absent'].includes(status)) {
                    icon = '⚠️';
                }

                statusDescription.innerHTML = icon + ' ' + description;
            });
        });
    </script>
@endsection
