@extends('layouts.app')

@section('content')
    <div class="container">
        <h4>Edit External Memo</h4>

        <form action="{{ route('external-letters.update', $letter->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Tanggal Surat</label>
                <input type="date" name="letter_date" class="form-control" value="{{ $letter->letter_date }}" required>
            </div>

            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ $letter->subject }}" required>
            </div>

            <div class="mb-3">
                <label>Inisial Penerima</label>
                <input type="text" name="recipient_initial" class="form-control" value="{{ $letter->recipient_initial }}"
                    required>
            </div>

            <div class="mb-3">
                <label>Program (Opsional)</label>
                <select name="program_id" class="form-select">
                    <option value="">-- Pilih Program --</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" {{ $letter->program_id == $program->id ? 'selected' : '' }}>
                            {{ $program->program_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Dokumen (opsional, isi jika ingin mengganti)</label>
                <input type="file" name="letter_document" class="form-control" accept=".pdf,.doc,.docx">
                @if ($letter->letter_document)
                    <p class="mt-2">Dokumen saat ini:
                        <a href="{{ asset('storage/' . $letter->letter_document) }}" target="_blank">View</a>
                    </p>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Update Surat</button>
        </form>
    </div>
@endsection
