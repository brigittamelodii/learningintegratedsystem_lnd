@extends('layouts.app')

@section('content')
    <div class="container">
        <h4>Create External Memo</h4>

        <form action="{{ route('external-letters.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label>Tanggal Surat</label>
                <input type="date" name="letter_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Program</label>
                <select name="program_id" class="form-select">
                    <option value="">-- Pilih Program --</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->program_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Inisial Penerima</label>
                <input type="text" name="recipient_initial" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Upload Dokumen (Opsional)</label>
                <input type="file" name="letter_document" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Surat</button>
        </form>
    </div>
@endsection
