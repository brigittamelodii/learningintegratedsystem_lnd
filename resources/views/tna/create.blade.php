@extends('layouts.app')

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}

        {{-- Tombol lanjut ke Category --}}
        <a href="{{ route('category.store', ['tna_id' => session('tna_id')]) }}" class="btn btn-primary mt-2">
            Lanjut Buat Category & Training Program
        </a>
    </div>
@endif

@section('content')
    <div class="container mt-1">
        <a href="{{ route('tna.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>
        <h2 class="mb-2" style="color:hsl(216, 98%, 52%)"><strong>Create TNA</strong></h2>

        <form method="POST" action="{{ route('tna.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="col-md-12">
                <div class="mb-2 d-flex align-items-center">
                    <label for="tna_year" class="form-label me-3" style="width: 160px;">Tahun:</label>
                    <input type="text" class="form-control" name="tna_year" id="tna_year">
                </div>
            </div>
            <div class="mb-2">
                <label for="tna_document" class="form-label">Input Training Plan:</label>
                <input type="file" class="form-control" name="tna_document" id="tna_document">
            </div>

            <div class="mb-2">
                <label for="tna_min_budget" class="form-label">Minimum Investment Projection Based on Regulator
                    Statement:</label>
                <input type="text" class="form-control" name="tna_min_budget" id="tna_min_budget">
            </div>

            <div class="mb-2">
                <label for="tna_remarks" class="form-label">TNA Remarks</label>
                <textarea class="form-control" name="tna_remarks" id="tna_remarks" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 Save</button>
        </form>
    </div>
@endsection
