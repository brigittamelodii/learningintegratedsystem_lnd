<div class="modal-header bg-primary text-white">
    <h5 class="modal-title" id="generalPaymentsLabel">Transaction View</h5>
    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <table class="table table-borderless mb-2">
        <tr>
            <th>Program Name</th>
            <td>{{ $payment->program->program_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Date</th>
            <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Remarks</th>
            <td>{{ $payment->remarks ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Account Name</th>
            <td>{{ $payment->account_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Account No.</th>
            <td>{{ $payment->account_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Created By</th>
            <td>{{ $payment->user->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if ($payment->status == 'Approve')
                    <span class="badge bg-success">{{ $payment->status }}</span>
                @elseif($payment->status == 'Reject')
                    <span class="badge bg-danger">{{ $payment->status }}</span>
                @elseif(str_contains($payment->status, 'Check'))
                    <span class="badge bg-warning text-dark">{{ $payment->status }}</span>
                @else
                    <span class="badge bg-secondary">{{ $payment->status }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Category</th>
                <th>Amount Fee</th>
                <th>PPN</th>
                <th>PPH</th>
                <th>Total Transfer</th>
                <th>Remaining Budget</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->category_fee ?? 'N/A' }}</td>
                <td>Rp{{ number_format($payment->amount_fee ?? 0, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($payment->ppn_fee ?? 0, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($payment->pph_fee ?? 0, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($payment->total_transfer ?? 0, 0, ',', '.') }}</td>
                <td>
                    Rp{{ number_format($remainingBudget ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="mb-3">
        <h6 class="fw-bold">Document:</h6>
        @if ($payment->file_path)
            {{-- Check if it's stored as blob or file path --}}
            @if (is_string($payment->file_path) &&
                    !str_starts_with($payment->file_path, '/') &&
                    !str_starts_with($payment->file_path, 'http'))
                {{-- It's a blob stored in database --}}
                <iframe src="{{ route('payments.showDocument', $payment->id) }}" width="100%" height="600px"
                    style="border: 1px solid #ccc; border-radius: 8px;">
                </iframe>
            @else
                {{-- It's a file path --}}
                <iframe src="{{ route('payments.showDocument', $payment->id) }}" width="100%" height="600px"
                    style="border: 1px solid #ccc; border-radius: 8px;">
                </iframe>
            @endif
        @else
            <p class="text-danger fst-italic">No document available.</p>
        @endif
    </div>
</div>
