<div class="modal-header bg-primary text-white">
    <h5 class="modal-title" id="participantPaymentsLabel">Participant Payment Details</h5>
    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <table class="table table-bordered mb-2">
        <tr>
            <th>Program Name</th>
            <td>{{ $participantPayments->programs->program_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Class Name</th>
            <td>{{ $participantPayments->classes->class_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Date</th>
            <td>{{ \Carbon\Carbon::parse($participantPayments->created_at)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Remarks</th>
            <td>{{ $participantPayments->remarks ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Account Name</th>
            <td>{{ $participantPayments->account_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Account No.</th>
            <td>{{ $participantPayments->account_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Created By</th>
            <td>{{ $participantPayments->user->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if ($participantPayments->status == 'Approve')
                    <span class="badge bg-success">{{ $participantPayments->status }}</span>
                @elseif($participantPayments->status == 'Reject')
                    <span class="badge bg-danger">{{ $participantPayments->status }}</span>
                @elseif(str_contains($participantPayments->status, 'Check'))
                    <span class="badge bg-warning text-dark">{{ $participantPayments->status }}</span>
                @else
                    <span class="badge bg-secondary">{{ $participantPayments->status }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Participant NIK</th>
                <th>Participant Name</th>
                <th>Position</th>
                <th>Working Unit</th>
                <th>Category Fee</th>
                <th>Amount Fee</th>
                @role('manager|superadmin|pic')
                    <th>Remaining Budget</th>
                @endrole
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $participantPayments->participants->karyawan_nik ?? 'N/A' }}</td>
                <td>{{ $participantPayments->participants->participant_name ?? 'N/A' }}</td>
                <td>{{ $participantPayments->participants->participant_position ?? 'N/A' }}</td>
                <td>{{ $participantPayments->participants->participant_working_unit ?? 'N/A' }}</td>
                <td>{{ $participantPayments->category_fee ?? 'N/A' }}</td>
                <td>Rp{{ number_format($participantPayments->amount_fee ?? 0, 0, ',', '.') }}</td>
                @role('manager|superadmin|pic')
                    <td>
                        Rp{{ number_format($remainingBudget ?? 0, 0, ',', '.') }}
                    </td>
                @endrole
            </tr>
        </tbody>
    </table>

    <div class="mb-3">
        <h6 class="fw-bold">Document:</h6>
        @if ($participantPayments->file_path)
            <iframe src="{{ route('participants-payment.showDocument', $participantPayments->id) }}" width="100%"
                height="600px" style="border: 1px solid #ccc; border-radius: 8px;">
            </iframe>
        @else
            <p class="text-danger fst-italic">No document available.</p>
        @endif
    </div>

</div>
