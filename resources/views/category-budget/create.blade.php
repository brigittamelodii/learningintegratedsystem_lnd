@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="text-primary"><strong>Insert Program Budget</strong></h3>

        {{-- Display Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error:</strong> Please check your input.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('category-budget.store', ['program_id' => $programId]) }}" method="POST" id="budgetForm">
            @csrf
            <input type="hidden" name="program_id" value="{{ $programId }}">

            <div id="budget-container">
                <div class="budget-item border rounded p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label>Category Fee</label>
                            <select name="category_budget[0][category_fee]" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach (['Meals', 'Hotel', 'Transportation', 'Business Trip Allowance', 'Reward', 'Material', 'Internet', 'Misc', 'Meeting Package', 'Facilitator'] as $fee)
                                    <option value="{{ $fee }}">{{ $fee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Amount (Rp)</label>
                            <input type="text" name="category_budget[0][amount_fee]" class="form-control money-input"
                                required>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-danger btn-sm remove-item"
                                style="display: none;">Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-secondary" id="addBudgetItem">+ Add Budget Item</button>
            </div>

            <button type="submit" class="btn btn-primary">Save Budget</button>
        </form>
    </div>

    <script>
        let itemIndex = 1;

        document.getElementById('addBudgetItem').addEventListener('click', function() {
            const container = document.getElementById('budget-container');

            const newItem = document.createElement('div');
            newItem.className = 'budget-item border rounded p-3 mb-3';
            newItem.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label>Category Fee</label>
                    <select name="category_budget[${itemIndex}][category_fee]" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        @foreach (['Meals', 'Hotel', 'Transportation', 'Business Trip Allowance', 'Reward', 'Material', 'Internet', 'Misc', 'Meeting Package', 'Facilitator'] as $fee)
                            <option value="{{ $fee }}">{{ $fee }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Amount (Rp)</label>
                    <input type="text" name="category_budget[${itemIndex}][amount_fee]" class="form-control money-input" required>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                </div>
            </div>
        `;
            container.appendChild(newItem);
            attachMoneyInput();
            itemIndex++;
            updateRemoveButtons();
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-item')) {
                e.target.closest('.budget-item').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-item');
            if (removeButtons.length === 1) {
                removeButtons[0].style.display = 'none';
            } else {
                removeButtons.forEach(btn => btn.style.display = 'inline-block');
            }
        }

        function attachMoneyInput() {
            document.querySelectorAll('.money-input').forEach(input => {
                input.removeEventListener('input', moneyInputHandler);
                input.addEventListener('input', moneyInputHandler);
            });
        }

        function moneyInputHandler() {
            let value = this.value.replace(/\D/g, '');
            this.value = formatRupiah(value, 'Rp');
        }

        function formatRupiah(angka, prefix) {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix === undefined ? rupiah : (rupiah ? prefix + ' ' + rupiah : '');
        }

        // Format existing inputs on page load
        attachMoneyInput();
        updateRemoveButtons();

        // Saat submit, hapus format Rp dan titik supaya yang dikirim angka murni
        document.getElementById('budgetForm').addEventListener('submit', function(e) {
            document.querySelectorAll('.money-input').forEach(input => {
                let onlyNumbers = input.value.replace(/[^0-9]/g, '');
                input.value = onlyNumbers;
            });
        });
    </script>

@endsection
