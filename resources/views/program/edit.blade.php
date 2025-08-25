@extends('layouts.app')
{{-- Notifikasi error --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Update failed!</strong> Ada kesalahan saat menyimpan data:
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@section('content')
    <div class="container mt-2">
        <a href="{{ route('program.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>

        <h2 class="mb-2 text-primary"><strong>Edit Program</strong></h2>

        <form action="{{ route('program.update', $program->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="program_name" class="form-label">Program Name</label>
                    <input type="text" name="program_name" class="form-control"
                        value="{{ old('program_name', $program->program_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="program_loc" class="form-label">Program Location</label>
                    <input type="text" name="program_loc" class="form-control"
                        value="{{ old('program_loc', $program->program_loc) }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="program_duration" class="form-label">Duration (HH:MM)</label>
                    <input type="time" name="program_duration" class="form-control"
                        value="{{ old('program_duration', \Carbon\Carbon::createFromFormat('H:i:s', $program->program_duration)->format('H:i')) }}"
                        required>
                </div>
                <div class="col-md-4">
                    <label for="tp_id" class="form-label">Training Program</label>
                    <select name="tp_id" class="form-select" required>
                        <option value="">-- Choose Training Program --</option>
                        @foreach ($training_programs as $tp)
                            <option value="{{ $tp->id }}"
                                {{ old('tp_id', $program->tp_id) == $tp->id ? 'selected' : '' }}>
                                {{ $tp->tp_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="user_id" class="form-label">PIC</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Choose PIC --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ $program->pic_id == $user->id ? 'selected' : '' }}>
                                {{ ucwords(str_replace('.', ' ', Str::before($user->email, '@'))) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="bi_code" class="form-label">BI Code</label>
                    <select name="bi_code" class="form-select" required>
                        <option value="">-- Choose BI Code --</option>
                        @foreach ($category_bi as $bi)
                            <option value="{{ $bi->bi_code }}"
                                {{ old('bi_code', $program->bi_code) == $bi->bi_code ? 'selected' : '' }}>
                                {{ $bi->bi_code }} - {{ $bi->bi_desc }}
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="program_type" class="form-label">Program Type</label>
                    <select name="program_type" class="form-select" required>
                        <option value="">-- Select Program Type --</option>
                        <option value="Training Activity"
                            {{ old('program_type', $program->program_type) == 'Training Activity' ? 'selected' : '' }}>
                            Training Activity</option>
                        <option value="Others"
                            {{ old('program_type', $program->program_type) == 'Others' ? 'selected' : '' }}>Others</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="program_act_type" class="form-label">Activity Type</label>
                    <select name="program_act_type" class="form-select" required>
                        <option value="">-- Select Activity Type --</option>
                        <option value="Internal"
                            {{ old('program_act_type', $program->program_act_type) == 'Internal' ? 'selected' : '' }}>
                            Internal</option>
                        <option value="External"
                            {{ old('program_act_type', $program->program_act_type) == 'External' ? 'selected' : '' }}>
                            External</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="program_unit_int" class="form-label">Unit Initiator</label>
                <input type="text" name="program_unit_int" class="form-control"
                    value="{{ old('program_unit_int', $program->program_unit_int) }}" required>
            </div>
            <div class="mb-3">
                <label for="program_remarks" class="form-label">Remarks</label>
                <textarea name="program_remarks" class="form-control" rows="3">{{ old('program_remarks', $program->program_remarks) }}</textarea>
            </div>
            <hr>
            <div class="mb-4">
                <h5 class="text-secondary">💰 Existing Category Budget</h5>
                @foreach ($program->category_budget as $index => $budget)
                    <div class="card mb-3 existing-budget-item">
                        <div class="card-body row g-3">
                            <input type="hidden" name="category_budget[{{ $index }}][id]"
                                value="{{ $budget->id }}">
                            <div class="col-md-6">
                                <label class="form-label">Category Fee</label>
                                <select name="category_budget[{{ $index }}][category_fee]" class="form-select"
                                    required>
                                    <option value="">-- Select Category --</option>
                                    @foreach (['Meals', 'Hotel', 'Transportation', 'Business Trip Allowance', 'Reward', 'Material', 'Internet', 'Misc', 'Meeting Package'] as $option)
                                        <option value="{{ $option }}"
                                            {{ old("category_budget.$index.category_fee", $budget->category_fee) == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01"
                                    name="category_budget[{{ $index }}][amount_fee]" class="form-control"
                                    value="{{ old("category_budget.$index.amount_fee", $budget->amount_fee) }}" required>
                            </div>
                        </div>
                    </div>
                @endforeach
                <button type="button" class="btn btn-outline-success mb-1" onclick="addNewBudget()">➕ Add New
                    Budget
                </button>
            </div>

            <div class="mb-4">
                <div id="new-budget-container"></div>
                {{-- Template for JS clone --}}
                <template id="new-budget-template">
                    <div class="card mb-3 new-budget-item">
                        <div class="card-body row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Category Fee</label>
                                <select name="category_budget_new[__INDEX__][category_fee]" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach (['Meals', 'Hotel', 'Transportation', 'Business Trip Allowance', 'Reward', 'Material', 'Internet', 'Misc', 'Meeting Package'] as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="category_budget_new[__INDEX__][amount_fee]"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100"
                                    onclick="removeBudget(this)">Delete</button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">💾 Update Program</button>
            </div>
        </form>
    </div>
@endsection
{{-- JS for adding new budget --}}
<script>
    let budgetIndex = 0;

    function addNewBudget() {
        const template = document.getElementById('new-budget-template').innerHTML;
        const newHtml = template.replace(/__INDEX__/g, budgetIndex);
        document.getElementById('new-budget-container').insertAdjacentHTML('beforeend', newHtml);
        budgetIndex++;
    }

    function removeBudget(button) {
        const item = button.closest('.new-budget-item');
        item.remove();
    };
</script>
