@extends('layouts.app')

@section('content')
    <div class="container">
        <h4>Edit Internal Memo</h4>

        <form action="{{ route('internal-letters.update', $letter->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Tanggal Surat</label>
                <input type="date" name="letter_date" class="form-control"
                    value="{{ \Carbon\Carbon::parse($letter->letter_date)->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ $letter->subject }}" required>
            </div>

            <div class="mb-3">
                <label>Program</label>
                <select name="program_id" class="form-select" required>
                    <option value="">-- Pilih Program --</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" {{ $program->id == $letter->program_id ? 'selected' : '' }}>
                            {{ $program->program_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Dokumen Surat (opsional)</label>
                @if ($letter->letter_document)
                    <p class="text-success">Sudah diupload:
                        <a href="{{ asset('storage/' . $letter->letter_document) }}" target="_blank">Lihat File</a>
                    </p>
                @endif
                <input type="file" name="letter_document" class="form-control" accept=".pdf,.doc,.docx">
            </div>

            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="{{ route('letters.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection
