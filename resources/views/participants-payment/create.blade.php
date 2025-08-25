<div class="modal-body">
    {{-- Notifikasi sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Sukses!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('participants-payment.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Create Payment</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Program & Class --}}
                    <div class="mb-3">
                        <label class="form-label">Select Program & Class</label>
                        <select class="form-select select2" name="class_id" id="classDropdown" required>
                            <option value="">-- Select --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    [{{ $class->programs->program_name }}] {{ $class->class_name }} - Batch
                                    {{ $class->class_batch }}
                                    ({{ \Carbon\Carbon::parse($class->start_date)->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kiri --}}
                    <div class="col-md-6">
                        {{-- Category Fee --}}
                        <div class="mb-3">
                            <label class="form-label">Category Fee</label>
                            <select name="category_fee" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                @foreach (['Meals', 'Transportation', 'Business Trip Allowance', 'Internet'] as $opt)
                                    <option value="{{ $opt }}"
                                        {{ old('category_fee') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_fee')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Amount Fee --}}
                        <div class="mb-3">
                            <label class="form-label">Amount Fee</label>
                            <input type="number" step="0.01" name="amount_fee" class="form-control"
                                value="{{ old('amount_fee') }}" required>
                            @error('amount_fee')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- File Upload --}}
                        <div class="mb-3">
                            <label class="form-label">Upload File (PDF, DOC, DOCX, JPG, PNG)</label>
                            <input type="file" name="file_path" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.png" required>
                            @error('file_path')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Kanan --}}
                    <div class="col-md-6">
                        {{-- Account Name --}}
                        <div class="mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="account_name" class="form-control"
                                value="{{ old('account_name') }}" maxlength="255">
                            @error('account_name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Account Number --}}
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_no" class="form-control"
                                value="{{ old('account_no') }}" maxlength="255">
                            @error('account_no')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Participant Selection (hanya untuk superadmin/pic) --}}
                        @hasrole('superadmin|pic')
                            <div class="mb-3">
                                <label class="form-label">Participant <span class="text-danger">*</span></label>
                                <select name="participant_id" class="form-select select2" required>
                                    <option value="">-- Select Participant --</option>
                                    @foreach ($participants as $participant)
                                        <option value="{{ $participant->id }}"
                                            {{ old('participant_id') == $participant->id ? 'selected' : '' }}>
                                            {{ $participant->participant_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('participant_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- User ID Selection (opsional untuk superadmin) --}}
                            @if ($isSuperadmin)
                                <div class="mb-3">
                                    <label class="form-label">Assign to PIC (Optional)</label>
                                    <select name="user_id" class="form-select select2">
                                        <option value="">-- Auto Assign to Program PIC --</option>
                                        @foreach ($users as $user)
                                            @if ($user->hasRole('pic'))
                                                <option value="{{ $user->id }}"
                                                    {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace(['.', '_'], ' ', Str::before($user->email, '@'))) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        @endrole
                    </div>
                </div>

                {{-- Status (default Pending) --}}
                <input type="hidden" name="status" value="Pending">
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Payment</button>
            </div>
        </div>
    </form>
</div>
