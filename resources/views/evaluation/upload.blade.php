@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-3">Upload Evaluasi Kelas</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('evaluation.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="class_id" value="{{ $classId }}">

            <div class="mb-3">
                <label for="materi" class="form-label">Materi</label>
                <input type="number" name="eval_scores[]" id="materi" class="form-control" min="1" max="5"
                    required>
            </div>

            <div class="mb-3">
                <label for="pengajar" class="form-label">Pengajar</label>
                <input type="number" name="eval_scores[]" id="pengajar" class="form-control" min="1" max="5"
                    required>
            </div>

            <div class="mb-3">
                <label for="kepanitiaan" class="form-label">Kepanitiaan</label>
                <input type="number" name="eval_scores[]" id="kepanitiaan" class="form-control" min="1"
                    max="5" required>
            </div>

            <div class="mb-3">
                <label for="eval_doc" class="form-label">Upload Dokumen Evaluasi (Excel)</label>
                <input type="file" name="eval_doc" id="eval_doc" class="form-control" accept=".xlsx,.xls">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Evaluasi</button>
        </form>
    </div>
@endsection
