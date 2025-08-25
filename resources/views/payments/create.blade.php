@if ($errors->has('debug'))
    <div class="alert alert-danger">
        <strong>Error:</strong> {{ $errors->first('debug') }}
    </div>
@endif

{{-- Display validation errors --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="modal-body">
    <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data" id="createPaymentForm">
        @csrf
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Create General Payment</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Program Selection - Full Width --}}
                    <div class="col-12 mb-3">
                        <label for="program_id" class="form-label fw-semibold">Program <span
                                class="text-danger">*</span></label>
                        <select name="program_id" class="form-select select2" id="program_id" required>
                            <option value="">-- Select Program --</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program['program_id'] }}"
                                    data-remaining="{{ $program['remaining'] }}"
                                    {{ old('program_id') == $program['program_id'] ? 'selected' : '' }}>
                                    {{ $program['label'] }} (Remaining:
                                    {{ number_format($program['remaining'], 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('program_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Left Column --}}
                    <div class="col-md-6">
                        {{-- Category Fee --}}
                        <div class="mb-3">
                            <label for="category_fee" class="form-label fw-semibold">Category Fee <span
                                    class="text-danger">*</span></label>
                            <select name="category_fee" id="category_fee" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach (['Meals', 'Hotel', 'Transportation', 'Business Trip Allowance', 'Reward', 'Material', 'Internet', 'Meeting Package', 'Misc.', 'Facilitator'] as $opt)
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

                        {{-- Beneficiary --}}
                        <div class="mb-3">
                            <label for="account_name" class="form-label fw-semibold">Beneficiary</label>
                            <input type="text" name="account_name" id="account_name" class="form-control"
                                value="{{ old('account_name') }}" placeholder="Enter beneficiary name">
                            @error('account_name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PPN --}}
                        <div class="mb-3">
                            <label for="ppn_fee" class="form-label fw-semibold">PPN (Tax)</label>
                            <input type="number" name="ppn_fee" id="ppn_fee" class="form-control" step="0.01"
                                value="{{ old('ppn_fee', 0) }}" min="0">
                            @error('ppn_fee')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PIC --}}
                        <div class="mb-3">
                            <label for="created_by_pics_id" class="form-label fw-semibold">Assigned to (PIC)</label>
                            <select name="created_by_pics_id" id="created_by_pics_id" class="form-select">
                                <option value="">-- Select PIC --</option>
                                @foreach ($pics as $pic)
                                    <option value="{{ $pic->id }}"
                                        {{ old('created_by_pics_id') == $pic->id ? 'selected' : '' }}>
                                        {{ ucwords(str_replace(['.', '_'], ' ', Str::before($pic->email, '@'))) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('created_by_pics_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="col-md-6">
                        {{-- Amount --}}
                        <div class="mb-3">
                            <label for="amount_fee" class="form-label fw-semibold">Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="amount_fee" id="amount_fee" class="form-control" step="0.01"
                                value="{{ old('amount_fee', 0) }}" min="0" required>
                            @error('amount_fee')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Account Number --}}
                        <div class="mb-3">
                            <label for="account_no" class="form-label fw-semibold">Account Number</label>
                            <input type="text" name="account_no" id="account_no" class="form-control"
                                value="{{ old('account_no') }}" placeholder="Enter account number">
                            @error('account_no')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PPH --}}
                        <div class="mb-3">
                            <label for="pph_fee" class="form-label fw-semibold">PPH (Tax)</label>
                            <input type="number" name="pph_fee" id="pph_fee" class="form-control" step="0.01"
                                value="{{ old('pph_fee', 0) }}" min="0">
                            @error('pph_fee')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Upload File --}}
                        <div class="mb-3">
                            <label for="file_path" class="form-label fw-semibold">Upload File (max 2MB)</label>
                            <input type="file" name="file_path" id="file_path" class="form-control"
                                accept=".pdf,.doc,.docx,.jpg,.png">
                            <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, JPG, PNG (max:
                                2MB)</small>
                            @error('file_path')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Total Transfer - Full Width --}}
                <div class="mb-3">
                    <label for="total_transfer" class="form-label fw-semibold">Total Transfer</label>
                    <input type="text" class="form-control bg-light text-end fw-semibold" id="total_transfer"
                        name="total_transfer" readonly placeholder="Rp 0">
                    <small class="form-text text-muted">Calculated as: Amount + PPN - PPH</small>
                </div>

                {{-- Hidden status field --}}
                <input type="hidden" name="status" value="Pending">
            </div>

            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Payment</button>
            </div>
        </div>
    </form>
</div>
