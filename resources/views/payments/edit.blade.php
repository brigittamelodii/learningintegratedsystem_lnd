@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Back to List --}}
        <a href="{{ route('payments.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>
        <div class="card-body">
            <h2 class="mb-3">Edit Payment</h2>

            {{-- Error Handling --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('payments.update', $payment->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    {{-- Program --}}
                    <div class="col-md-6">
                        <label class="form-label">Program</label>
                        <input type="text" class="form-control" value="{{ $payment->program->program_name ?? 'N/A' }}"
                            disabled>
                    </div>

                    {{-- Total Transfer --}}
                    <div class="col-md-6">
                        <label class="form-label">Total Transfer</label>
                        <input type="text" class="form-control"
                            value="{{ number_format($payment->total_transfer, 0, ',', '.') }}" disabled>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    {{-- Category Fee --}}
                    <div class="col-md-6">
                        <label for="category_fee" class="form-label">Category Fee</label>
                        <select name="category_fee" id="category_fee" class="form-select" required>
                            @php
                                $options = [
                                    'Meals',
                                    'Hotel',
                                    'Transportation',
                                    'Business Trip Allowance',
                                    'Reward',
                                    'Material',
                                    'Internet',
                                    'Meeting Package',
                                    'Misc.',
                                    'Facilitator',
                                ];
                            @endphp
                            @foreach ($options as $option)
                                <option value="{{ $option }}"
                                    {{ $payment->category_fee == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Amount Fee --}}
                    <div class="col-md-6">
                        <label for="amount_fee" class="form-label">Amount Fee</label>
                        <input type="number" name="amount_fee" id="amount_fee" class="form-control"
                            value="{{ $payment->amount_fee }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    {{-- Account No --}}
                    <div class="col-md-6">
                        <label for="account_no" class="form-label">Account No</label>
                        <input type="text" name="account_no" id="account_no" class="form-control"
                            value="{{ $payment->account_no }}">
                    </div>

                    {{-- Account Name --}}
                    <div class="col-md-6">
                        <label for="account_name" class="form-label">Account Name</label>
                        <input type="text" name="account_name" id="account_name" class="form-control"
                            value="{{ $payment->account_name }}">
                    </div>
                </div>

                <div class="row mb-3">
                    {{-- PPN Fee --}}
                    <div class="col-md-6">
                        <label for="ppn_fee" class="form-label">PPN Fee</label>
                        <input type="number" name="ppn_fee" id="ppn_fee" class="form-control"
                            value="{{ $payment->ppn_fee }}">
                    </div>

                    {{-- PPH Fee --}}
                    <div class="col-md-6">
                        <label for="pph_fee" class="form-label">PPH Fee</label>
                        <input type="number" name="pph_fee" id="pph_fee" class="form-control"
                            value="{{ $payment->pph_fee }}">
                    </div>
                </div>

                <div class="row mb-3">
                    {{-- Upload Document --}}
                    <div class="col-md-6">
                        <label for="file_path" class="form-label">Upload Document</label>
                        <input type="file" name="file_path" id="file_path" class="form-control">
                        <small class="text-muted">
                            PDF, DOC, JPG, PNG (max: 2MB)
                            @if ($payment->file_path)
                                📎 <a href="{{ route('payments.showDocument', $payment->id) }}" target="_blank">View
                                    Existing Document</a>
                            @endif
                        </small>
                    </div>

                    {{-- PIC --}}
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">PIC</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            @foreach ($picUsers as $pic)
                                <option value="{{ $pic->id }}" {{ $payment->user_id == $pic->id ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary mt-2">Update Payment</button>
                </div>
            </form>
        </div>
    </div>
@endsection
