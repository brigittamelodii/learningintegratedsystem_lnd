@extends('layouts.app')

@section('content')
    <div class="container-fluid my-1">
        <a href="{{ route('classes.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>

        <div class="card shadow rounded-4 mx-3" style="height: auto;">
            <div class="card-header bg-warning text-dark rounded-top-4 py-2">
                <h5 class="mb-0"><i class="bi bi-files me-2"></i>Duplicate Class</h5>
            </div>
            <div class="card-body px-4 pt-3 pb-2">
                <form method="POST" action="{{ route('classes.store') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Hidden original class_name --}}
                    <input type="hidden" name="class_name" value="{{ $originalClass->class_name }}">

                    {{-- Hidden program ID --}}
                    <input type="hidden" name="program_id" value="{{ $originalClass->program_id }}">

                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input type="text" class="form-control form-control-sm"
                            value="{{ $originalClass->programs->program_name }}" disabled>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="class_batch" class="form-label">New Batch</label>
                            <input type="number" name="class_batch" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">New Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">New End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="class_doc" class="form-label">Upload New Document (Optional)</label>
                        <input type="file" name="class_doc" class="form-control form-control-sm"
                            accept=".pdf,.doc,.docx">
                        <small class="text-muted">Format: PDF, DOC, DOCX</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning btn-sm px-4">
                            <i class="bi bi-files me-1"></i> Duplicate Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
