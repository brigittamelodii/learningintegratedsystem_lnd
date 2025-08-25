@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {{-- Card Wrapper --}}
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-primary text-white rounded-top-4">
                        <h4 class="mb-0"> Upload Participants</h4>
                    </div>

                    <div class="card-body p-4">

                        {{-- Success Message --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Success!</strong> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Error Messages --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>There were some problems:</strong>
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Form --}}
                        <form action="{{ route('participants.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Select Class --}}
                            <select name="class_id" class="form-select" required id="classSelect">
                                <option value="" disabled selected>-- Choose a class --</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" data-batch="{{ $class->class_batch }}">
                                        {{ $class->class_name }} - Batch {{ $class->class_batch }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="batch" id="batch">
                            {{-- Upload File --}}
                            <div class="mb-4">
                                <label for="file" class="form-label">Upload Excel File</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                                <div class="form-text">Supported formats: .xlsx, .xls</div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="text-end">
                                <button type="submit" class="btn btn-success px-4 py-2 rounded-3">
                                    <i class="bi bi-upload me-1"></i> Import Participants
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('classSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const batch = selectedOption.getAttribute('data-batch');
            document.getElementById('batch').value = batch;
        });
    </script>

@endsection
