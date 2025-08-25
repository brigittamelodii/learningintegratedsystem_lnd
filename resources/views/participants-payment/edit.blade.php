@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <a href="{{ route('payments.index') }}" class="text-secondary mb-2 d-inline-block" style="text-decoration: none;">
            ← Back to List
        </a>

        <h3 class="mb-2 text-primary fw-bold">Edit Participant Payment</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <h6 class="fw-semibold mb-2">⚠️ Please fix the following issues:</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li class="small">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('participants-payment.update', $participantsPayment->id) }}" method="POST"
            enctype="multipart/form-data" class="row g-4">
            @csrf
            @method('PUT')

            {{-- Program (readonly) --}}
            <div class="col-md-6">
                <label class="form-label fw-medium">Program Name</label>
                <input type="text" class="form-control shadow-sm"
                    value="{{ $participantsPayment->programs->program_name }}" readonly disabled>
                <input type="hidden" name="program_id" value="{{ $participantsPayment->program_id }}">
            </div>

            {{-- Class (readonly) --}}
            <div class="col-md-6">
                <label class="form-label fw-medium">Class Name</label>
                <input type="text" class="form-control shadow-sm" value="{{ $participantsPayment->classes->class_name }}"
                    readonly disabled>
                <input type="hidden" name="class_id" value="{{ $participantsPayment->class_id }}">
            </div>

            {{-- Participant (readonly) --}}
            <div class="col-md-6">
                <label class="form-label fw-medium">Participant Name</label>
                <input type="text" class="form-control shadow-sm"
                    value="{{ $participantsPayment->participants->participant_name }}" readonly disabled>
                <input type="hidden" name="participants_id" value="{{ $participantsPayment->participants_id }}">
            </div>

            {{-- Category Fee --}}
            <div class="col-md-6">
                <label for="category_fee" class="form-label fw-medium">Category Fee</label>
                <select name="category_fee" class="form-select shadow-sm" required>
                    @foreach (['Meals', 'Transportation', 'Business Trip Allowance', 'Reward', 'Material', 'Internet', 'Meeting Package', 'Misc.', 'Facilitator', 'Hotel'] as $opt)
                        <option value="{{ $opt }}"
                            {{ $participantsPayment->category_fee == $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Amount Fee --}}
            <div class="col-md-6">
                <label for="amount_fee" class="form-label fw-medium">Amount Fee</label>
                <input type="number" step="0.01" name="amount_fee" class="form-control shadow-sm"
                    value="{{ $participantsPayment->amount_fee }}" required>
            </div>

            {{-- Account Name --}}
            <div class="col-md-6">
                <label for="account_name" class="form-label fw-medium">Account Name</label>
                <input type="text" name="account_name" class="form-control shadow-sm"
                    value="{{ $participantsPayment->account_name }}">
            </div>

            {{-- Account Number --}}
            <div class="col-md-6">
                <label for="account_no" class="form-label fw-medium">Account Number</label>
                <input type="text" name="account_no" class="form-control shadow-sm"
                    value="{{ $participantsPayment->account_no }}">
            </div>

            {{-- Approved By User (PIC/Manager/Superadmin) --}}
            @hasrole('superadmin|manager|pic')
                <div class="col-md-6">
                    <label for="approved_by_user_id" class="form-label fw-medium">Edited By (PIC/Manager)</label>
                    <select name="approved_by_user_id" class="form-select shadow-sm">
                        <option value="">-- Select Editor --</option>
                        @foreach ($pics as $user)
                            <option value="{{ $user->id }}"
                                {{ $participantsPayment->approved_by_user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->email }}
                                @if ($user->hasRole('superadmin'))
                                    (Superadmin)
                                @elseif($user->hasRole('manager'))
                                    (Manager)
                                @elseif($user->hasRole('pic'))
                                    (PIC)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            @endrole

            {{-- File Upload --}}
            <div class="col-md-12">
                <label for="file_path" class="form-label fw-medium">Upload New File</label>
                <input type="file" name="file_path" class="form-control shadow-sm">
                <small class="text-muted d-block mt-1">
                    Max 2MB | PDF, DOC, JPG, PNG
                    @if ($participantsPayment->file_path)
                        <br>
                        🔗 <a href="{{ route('participants-payment.showDocument', $participantsPayment->id) }}"
                            class="text-decoration-underline" target="_blank">
                            View Existing Document
                        </a>
                    @endif
                </small>
            </div>

            {{-- Submit --}}
            <div class="col-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">
                    💾 Update Payment
                </button>
            </div>
        </form>
    </div>
@endsection
