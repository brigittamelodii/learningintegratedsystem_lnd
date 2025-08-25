@extends('layouts.app')

@push('styles')
    <style>
        .table-sm td,
        .table-sm th {
            vertical-align: middle;
        }

        .nav-tabs .nav-link.active {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .nav-tabs .nav-link {
            font-weight: 500;
        }
    </style>
@endpush

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

@section('content')
    <!-- Modal kosong -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modalPaymentContent"></div>
        </div>
    </div>

    <!-- Bulk Action Confirmation Modal (HARUS DI LUAR paymentModal) -->
    <div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="bulkActionForm" method="POST">
                @csrf
                @method('POST')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkActionModalLabel">Bulk Action Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning!</strong> This action cannot be undone.
                        </div>
                        <p id="bulkActionMessage"></p>
                        <div class="mb-3">
                            <label for="bulkRemarks" class="form-label">Remarks (optional)</label>
                            <textarea class="form-control" name="remarks" id="bulkRemarks" rows="3" placeholder="Add any additional notes..."></textarea>
                        </div>
                        <div id="selectedItemsList" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="bulkActionConfirmBtn">Confirm Action</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container-fluid px-3">
        <h2 class="text-center text-primary fw-bold mb-4" style="margin-top: 10px">Transaction Management</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4" id="paymentsTab" role="tablist">
            @php
                $allowedTabsForParticipant = ['Pending', 'Approve', 'Reject'];
                $allowedTabsForExec = ['Check by Manager', 'Approve', 'Reject'];
                $allowedTabsForManager = ['Check by PIC', 'Approve', 'Reject'];
                $allowedTabsForPIC = ['Pending', 'Approve', 'Reject'];
                $allowedTabsForSuperAdmin = ['Pending', 'Check by PIC', 'Check by Manager', 'Approve', 'Reject'];
            @endphp

            @foreach (['Pending', 'Check by PIC', 'Check by Manager', 'Approve', 'Reject'] as $statusLabel)
                @php
                    $tabId = strtolower(str_replace(' ', '', $statusLabel));
                    $currentTab = request('tab', 'pending');

                    $isParticipant = auth()->user()->hasRole('participant');
                    $isAllowedForParticipant = in_array($statusLabel, $allowedTabsForParticipant);

                    $isPIC = auth()->user()->hasRole('pic');
                    $isAllowedForPIC = in_array($statusLabel, $allowedTabsForPIC);

                    $isManager = auth()->user()->hasRole('manager');
                    $isAllowedForManager = in_array($statusLabel, $allowedTabsForExec);

                    $isExec = auth()->user()->hasRole('executive');
                    $isAllowedForExec = in_array($statusLabel, $allowedTabsForExec);

                    $isSuperAdmin = auth()->user()->hasRole('superadmin');
                    $isAllowedForSuperAdmin = in_array($statusLabel, $allowedTabsForSuperAdmin);
                @endphp

                @if (
                    ($isParticipant && $isAllowedForParticipant) ||
                        ($isPIC && $isAllowedForPIC) ||
                        ($isManager && $isAllowedForManager) ||
                        ($isExec && $isAllowedForExec) ||
                        ($isSuperAdmin && $isAllowedForSuperAdmin))
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentTab === $tabId ? 'active text-dark' : 'text-muted' }}"
                            href="?tab={{ $tabId }}" role="tab">

                            @if ($statusLabel === 'Pending')
                                ⏳
                            @elseif ($statusLabel === 'Check by PIC' || $statusLabel === 'Check by Manager')
                                🔍
                            @elseif ($statusLabel === 'Approve')
                                ✅
                            @elseif ($statusLabel === 'Reject')
                                ❌
                            @endif
                            {{ $statusLabel }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="tab-content" id="paymentsTabContent">
            @foreach (['Pending', 'Check by PIC', 'Check by Manager', 'Approve', 'Reject'] as $statusLabel)
                @php
                    $tabId = strtolower(str_replace(' ', '', $statusLabel));
                    $currentTab = request('tab', 'pending');
                @endphp
                <div class="tab-pane fade {{ $currentTab === $tabId ? 'show active' : '' }}" id="{{ $tabId }}"
                    role="tabpanel">
                    @php
                        $canViewGeneral = auth()
                            ->user()
                            ->hasAnyRole(['manager', 'executive', 'pic', 'superadmin']);
                    @endphp

                    <div class="row">
                        {{-- Participants Payments --}}
                        <div class="{{ $canViewGeneral ? 'col-md-7' : 'col-12' }} mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 class="fw-bold">Participants Payments - {{ $statusLabel }}</h5>
                                <div class="d-flex gap-2">
                                    @if ($statusLabel === 'Check by Manager')
                                        <div class="dropdown">
                                            <button class="btn btn-outline-success btn-sm dropdown-toggle shadow-sm"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-check-double"></i> Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item btnSelectAllParticipant" href="#"
                                                        data-tab="{{ $tabId }}">
                                                        <i class="fas fa-check-square"></i> Select All
                                                    </a></li>
                                                <li><a class="dropdown-item btnDeselectAllParticipant" href="#"
                                                        data-tab="{{ $tabId }}">
                                                        <i class="fas fa-square"></i> Deselect All
                                                    </a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item btnBulkApproveParticipant" href="#"
                                                        data-tab="{{ $tabId }}">
                                                        <i class="fas fa-thumbs-up text-success"></i> Approve Selected
                                                    </a></li>
                                                <li><a class="dropdown-item btnBulkRejectParticipant" href="#"
                                                        data-tab="{{ $tabId }}">
                                                        <i class="fas fa-thumbs-down text-danger"></i> Reject Selected
                                                    </a></li>
                                            </ul>
                                        </div>
                                    @endif
                                    <button class="btn btn-primary btn-sm btnOpenPartPaymentCreate shadow-sm">Create
                                        Transaction</button>
                                </div>
                            </div>
                            <table id="participantPaymentsTable-{{ $tabId }}"
                                class="table table-bordered table-hover table-sm align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        @if ($statusLabel === 'Check by Manager')
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input selectAllParticipantCheckbox"
                                                    data-tab="{{ $tabId }}">
                                            </th>
                                        @endif
                                        <th>Program</th>
                                        <th>Batch</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                        <th>Information</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($participantPayments))
                                        @foreach ($participantPayments as $pp)
                                            @php
                                                $normalizedStatus = strtolower(str_replace(' ', '', $pp->status));
                                                $currentUser = auth()->user();
                                                $showPayment = true;

                                                // Participant hanya melihat payment miliknya
                                                if ($isParticipant) {
                                                    $participantId = $pp->participants->id ?? null;
                                                    $showPayment =
                                                        $currentUser->participant_id == $participantId ||
                                                        ($pp->participants->user_id ?? null) == $currentUser->id;
                                                }

                                                // PIC hanya melihat payment dari program yang dia handle
                                                if ($isPIC) {
                                                    $showPayment =
                                                        isset($pp->programs) &&
                                                        $pp->programs->user_id === $currentUser->id;
                                                }
                                            @endphp

                                            @if ($normalizedStatus === $tabId && $showPayment)
                                                <tr>
                                                    {{-- Checkbox hanya muncul di Check by Manager dan bukan participant --}}
                                                    @if ($statusLabel === 'Check by Manager' && !$isParticipant)
                                                        <td class="text-center">
                                                            <input type="checkbox"
                                                                class="form-check-input participantCheckbox"
                                                                data-id="{{ $pp->id }}"
                                                                data-tab="{{ $tabId }}">
                                                        </td>
                                                    @endif

                                                    <td>{{ $pp->programs->program_name ?? '-' }}</td>
                                                    <td>{{ $pp->classes->class_batch ?? '-' }}</td>
                                                    <td>{{ $pp->participants->participant_name ?? '-' }}</td>
                                                    <td>{{ $pp->category_fee }}</td>
                                                    <td class="text-end text-success fw-semibold">
                                                        Rp{{ number_format($pp->amount_fee, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($pp->status === 'Pending')
                                                            @if (!$isParticipant)
                                                                <button type="button"
                                                                    class="btn btn-outline-primary btn-sm mb-1 btnAction"
                                                                    data-id="{{ $pp->id }}"
                                                                    data-action="checkbypic">Check</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm mb-1 btnAction"
                                                                    data-id="{{ $pp->id }}"
                                                                    data-action="reject">Reject</button>
                                                            @else
                                                                <span class="badge bg-warning text-dark">In Process</span>
                                                            @endif
                                                        @elseif($pp->status === 'Check by PIC' && !$isParticipant)
                                                            <button type="button"
                                                                class="btn btn-outline-primary btn-sm mb-1 btnAction"
                                                                data-id="{{ $pp->id }}"
                                                                data-action="checkbymanager">Check</button>
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm mb-1 btnAction"
                                                                data-id="{{ $pp->id }}"
                                                                data-action="reject">Reject</button>
                                                        @elseif($pp->status === 'Check by Manager' && !$isParticipant)
                                                            <button type="button"
                                                                class="btn btn-outline-success btn-sm mb-1 btnAction"
                                                                data-id="{{ $pp->id }}"
                                                                data-action="approve">Approve</button>
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm mb-1 btnAction"
                                                                data-id="{{ $pp->id }}"
                                                                data-action="reject">Reject</button>
                                                        @elseif($pp->status === 'Approve')
                                                            <span class="badge bg-success">{{ $pp->status }}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $pp->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button
                                                            class="btn btn-info btn-sm mb-1 btnShowParticipantPayment text-white"
                                                            data-id="{{ $pp->id }}">View</button>
                                                        <a href="{{ route('participants-payment.edit', $pp->id) }}"
                                                            class="btn btn-warning btn-sm mb-1">Edit</a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- General Payments --}}
                        @hasrole('manager|executive|pic|superadmin')
                            <div class="col-md-5 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fw-bold">General Payments - {{ $statusLabel }}</h5>
                                    <div class="d-flex gap-2">
                                        @if ($statusLabel === 'Check by Manager')
                                            <div class="dropdown">
                                                <button class="btn btn-outline-success btn-sm dropdown-toggle shadow-sm"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-check-double"></i> Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item btnSelectAllGeneral" href="#"
                                                            data-tab="{{ $tabId }}">
                                                            <i class="fas fa-check-square"></i> Select All
                                                        </a></li>
                                                    <li><a class="dropdown-item btnDeselectAllGeneral" href="#"
                                                            data-tab="{{ $tabId }}">
                                                            <i class="fas fa-square"></i> Deselect All
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item btnBulkApproveGeneral" href="#"
                                                            data-tab="{{ $tabId }}">
                                                            <i class="fas fa-thumbs-up text-success"></i> Approve Selected
                                                        </a></li>
                                                    <li><a class="dropdown-item btnBulkRejectGeneral" href="#"
                                                            data-tab="{{ $tabId }}">
                                                            <i class="fas fa-thumbs-down text-danger"></i> Reject Selected
                                                        </a></li>
                                                </ul>
                                            </div>
                                        @endif
                                        <button class="btn btn-primary btn-sm btnOpenPaymentCreate shadow-sm">Create
                                            Transaction</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="generalPaymentsTable-{{ $tabId }}"
                                        class="table table-bordered table-hover table-sm align-middle">
                                        <thead class="table-primary text-center">
                                            <tr>
                                                @if ($statusLabel === 'Check by Manager')
                                                    <th style="width: 40px;">
                                                        <input type="checkbox"
                                                            class="form-check-input selectAllGeneralCheckbox"
                                                            data-tab="{{ $tabId }}">
                                                    </th>
                                                @endif
                                                <th style="min-width: 120px;">Program</th>
                                                <th style="min-width: 100px;">Category</th>
                                                <th style="min-width: 120px;">Total</th>
                                                <th style="min-width: 140px;">Action</th>
                                                <th style="min-width: 120px;">Information</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($payments))
                                                @foreach ($payments as $payment)
                                                    @if (strtolower(str_replace(' ', '', $payment->status)) === $tabId)
                                                        <tr>
                                                            @if ($statusLabel === 'Check by Manager')
                                                                <td class="text-center">
                                                                    <input type="checkbox"
                                                                        class="form-check-input generalCheckbox"
                                                                        data-id="{{ $payment->id }}"
                                                                        data-tab="{{ $tabId }}">
                                                                </td>
                                                            @endif
                                                            <td>{{ $payment->program->program_name ?? '-' }}</td>
                                                            <td>{{ $payment->category_fee }}</td>
                                                            <td class="text-end text-success fw-semibold">
                                                                Rp{{ number_format($payment->total_transfer, 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{-- Gunakan dropdown untuk actions di mobile --}}
                                                                <div class="d-none d-md-block">
                                                                    @if ($payment->status === 'Pending')
                                                                        <button type="button"
                                                                            class="btn btn-outline-primary btn-sm mb-1 btnActionGP"
                                                                            data-id="{{ $payment->id }}"
                                                                            data-action="checkbypic">Check</button>
                                                                        <button type="button"
                                                                            class="btn btn-outline-danger btn-sm mb-1 btnActionGP"
                                                                            data-id="{{ $payment->id }}"
                                                                            data-action="reject">Reject</button>
                                                                    @elseif($payment->status === 'Check by PIC')
                                                                        <button type="button"
                                                                            class="btn btn-outline-primary btn-sm mb-1 btnActionGP"
                                                                            data-id="{{ $payment->id }}"
                                                                            data-action="checkbymanager">Check</button>
                                                                        <button type="button"
                                                                            class="btn btn-outline-danger btn-sm mb-1 btnActionGP"
                                                                            data-id="{{ $payment->id }}"
                                                                            data-action="reject">Reject</button>
                                                                    @elseif($payment->status === 'Check by Manager')
                                                                        <button type="button"
                                                                            class="btn btn-outline-success btn-sm mb-1 btnActionGP"
                                                                            data-id="{{ $payment->id }}"
                                                                            data-action="approve">Approve</button>
                                                                        <button type="button"
                                                                            class="btn btn-outline-danger btn-sm mb-1 btnActionGP"
                                                                            data-id="{{ $payment->id }}"
                                                                            data-action="reject">Reject</button>
                                                                    @elseif($payment->status === 'Approve')
                                                                        <span
                                                                            class="badge bg-success">{{ $payment->status }}</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-danger">{{ $payment->status }}</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                                    <button
                                                                        class="btn btn-sm btn-info text-white btnShowGeneralPayment"
                                                                        data-id="{{ $payment->id }}">
                                                                        View
                                                                    </button>
                                                                    <a href="{{ route('payments.edit', $payment->id) }}"
                                                                        class="btn btn-sm btn-warning">
                                                                        Edit
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endrole
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal Konfirmasi CheckByPIC PARTI -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="confirmFormParticipant" method="POST">
                @csrf
                @method('POST')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessageParticipant">Are you sure you want to <strong
                                id="modalActionLabelParticipant"></strong> this payment?</p>
                        <div class="mb-3">
                            <label for="remarksParticipant" class="form-label">Remarks
                                (optional)
                            </label>
                            <textarea class="form-control" name="remarks" id="remarksParticipant" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Yes, Proceed</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal GP Konfirmasi approve/reject --}}
    <div class="modal fade" id="confirmModalGP" tabindex="-1" aria-labelledby="confirmModalGPLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="confirmFormGeneral" method="POST">
                @csrf
                @method('POST')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalGPLabel">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessageGeneral">Are you sure you want to <strong
                                id="modalActionLabelGeneral"></strong> this payment?</p>
                        <div class="mb-3">
                            <label for="remarksGeneral" class="form-label">Remarks
                                (optional)</label>
                            <textarea class="form-control" name="remarks" id="remarksGeneral" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Yes, Proceed</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        $(document).ready(function() {
            // Initialize modals
            let confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            let confirmModalGP = new bootstrap.Modal(document.getElementById('confirmModalGP'));
            let bulkActionModal = new bootstrap.Modal(document.getElementById('bulkActionModal'));

            // DataTable initialization function
            function initializeDataTables(tab) {
                const participantTableId = `#participantPaymentsTable-${tab}`;
                const generalTableId = `#generalPaymentsTable-${tab}`;

                // Destroy existing DataTables if they exist
                if ($.fn.DataTable.isDataTable(participantTableId)) {
                    $(participantTableId).DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable(generalTableId)) {
                    $(generalTableId).DataTable().destroy();
                }

                // Initialize Participant Payments DataTable
                if ($(participantTableId).length > 0) {
                    $(participantTableId).DataTable({
                        pageLength: 5,
                        lengthMenu: [5, 10, 20, 50, 100],
                        order: [],
                        responsive: true,
                        autoWidth: false,
                        processing: true,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries to show",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            },
                            emptyTable: "No data available in table"
                        },
                        columnDefs: [{
                            orderable: false,
                            targets: tab === 'checkbymanager' ? [0, 6, 7] : [5, 6]
                        }],
                        drawCallback: function(settings) {
                            // Re-bind event handlers after table redraw
                            bindTableEventHandlers();
                        }
                    });
                }

                // Initialize General Payments DataTable
                if ($(generalTableId).length > 0) {
                    $(generalTableId).DataTable({
                        pageLength: 5,
                        lengthMenu: [5, 10, 20, 50, 100],
                        order: [],
                        responsive: true,
                        autoWidth: false,
                        processing: true,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries to show",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            },
                            emptyTable: "No data available in table"
                        },
                        columnDefs: [{
                            orderable: false,
                            targets: tab === 'checkbymanager' ? [0, 4, 5] : [3, 4]
                        }],
                        drawCallback: function(settings) {
                            // Re-bind event handlers after table redraw
                            bindTableEventHandlers();
                        }
                    });
                }
            }

            // Function to bind event handlers (moved outside to avoid duplication)
            function bindTableEventHandlers() {
                // Re-bind any event handlers that might be lost during table redraw
                // This is important for dynamically generated content
            }

            // Initialize DataTables for all tabs
            const tabs = ['pending', 'checkbypic', 'checkbymanager', 'approve', 'reject'];

            // Initialize DataTables for the currently active tab
            const currentTab = $('.tab-pane.active').attr('id');
            if (currentTab) {
                setTimeout(function() {
                    initializeDataTables(currentTab);
                }, 100);
            }

            // Handle tab switching
            $('.nav-tabs a').on('shown.bs.tab', function(e) {
                const targetTab = $(e.target).attr('href').substring(1); // Remove the # from href
                setTimeout(function() {
                    initializeDataTables(targetTab);
                }, 100);
            });

            // Alternative approach: Initialize on tab click
            $('.nav-tabs a').on('click', function(e) {
                const targetTab = $(this).attr('href').substring(1);
                setTimeout(function() {
                    initializeDataTables(targetTab);
                }, 150);
            });

            // BULK ACTION EVENT HANDLERS (unchanged from your original code)

            // Select All Participants
            $(document).on('click', '.btnSelectAllParticipant', function(e) {
                e.preventDefault();
                const tab = $(this).data('tab');
                console.log('Select all participants for tab:', tab);
                $(`.participantCheckbox[data-tab="${tab}"]`).prop('checked', true);
                $(`.selectAllParticipantCheckbox[data-tab="${tab}"]`).prop('checked', true);
            });

            // Deselect All Participants
            $(document).on('click', '.btnDeselectAllParticipant', function(e) {
                e.preventDefault();
                const tab = $(this).data('tab');
                console.log('Deselect all participants for tab:', tab);
                $(`.participantCheckbox[data-tab="${tab}"]`).prop('checked', false);
                $(`.selectAllParticipantCheckbox[data-tab="${tab}"]`).prop('checked', false);
            });

            // Select All General Payments
            $(document).on('click', '.btnSelectAllGeneral', function(e) {
                e.preventDefault();
                const tab = $(this).data('tab');
                console.log('Select all general for tab:', tab);
                $(`.generalCheckbox[data-tab="${tab}"]`).prop('checked', true);
                $(`.selectAllGeneralCheckbox[data-tab="${tab}"]`).prop('checked', true);
            });

            // Deselect All General Payments
            $(document).on('click', '.btnDeselectAllGeneral', function(e) {
                e.preventDefault();
                const tab = $(this).data('tab');
                console.log('Deselect all general for tab:', tab);
                $(`.generalCheckbox[data-tab="${tab}"]`).prop('checked', false);
                $(`.selectAllGeneralCheckbox[data-tab="${tab}"]`).prop('checked', false);
            });

            // Header checkbox for participants
            $(document).on('change', '.selectAllParticipantCheckbox', function() {
                const tab = $(this).data('tab');
                const isChecked = $(this).prop('checked');
                console.log('Header checkbox participants changed:', tab, isChecked);
                $(`.participantCheckbox[data-tab="${tab}"]`).prop('checked', isChecked);
            });

            // Header checkbox for general payments
            $(document).on('change', '.selectAllGeneralCheckbox', function() {
                const tab = $(this).data('tab');
                const isChecked = $(this).prop('checked');
                console.log('Header checkbox general changed:', tab, isChecked);
                $(`.generalCheckbox[data-tab="${tab}"]`).prop('checked', isChecked);
            });

            // Individual checkbox change for participants
            $(document).on('change', '.participantCheckbox', function() {
                const tab = $(this).data('tab');
                const totalCheckboxes = $(`.participantCheckbox[data-tab="${tab}"]`).length;
                const checkedCheckboxes = $(`.participantCheckbox[data-tab="${tab}"]:checked`).length;
                console.log(
                    `Participant checkboxes: ${checkedCheckboxes}/${totalCheckboxes} for tab: ${tab}`);
                $(`.selectAllParticipantCheckbox[data-tab="${tab}"]`).prop('checked', totalCheckboxes ===
                    checkedCheckboxes);
            });

            // Individual checkbox change for general payments
            $(document).on('change', '.generalCheckbox', function() {
                const tab = $(this).data('tab');
                const totalCheckboxes = $(`.generalCheckbox[data-tab="${tab}"]`).length;
                const checkedCheckboxes = $(`.generalCheckbox[data-tab="${tab}"]:checked`).length;
                console.log(`General checkboxes: ${checkedCheckboxes}/${totalCheckboxes} for tab: ${tab}`);
                $(`.selectAllGeneralCheckbox[data-tab="${tab}"]`).prop('checked', totalCheckboxes ===
                    checkedCheckboxes);
            });

            // Bulk Approve Participants
            $(document).on('click', '.btnBulkApproveParticipant', function(e) {
                e.preventDefault();
                console.log('Bulk approve participants clicked');

                const tab = $(this).data('tab');
                const selectedIds = [];

                $(`.participantCheckbox[data-tab="${tab}"]:checked`).each(function() {
                    selectedIds.push($(this).data('id'));
                });

                console.log('Selected participant IDs for approve:', selectedIds);

                if (selectedIds.length === 0) {
                    alert('Please select at least one participant payment to approve.');
                    return;
                }

                showBulkActionModal('approve', 'participants-payment', selectedIds,
                    'participant payment(s)');
            });

            // Bulk Reject Participants
            $(document).on('click', '.btnBulkRejectParticipant', function(e) {
                e.preventDefault();
                console.log('Bulk reject participants clicked');

                const tab = $(this).data('tab');
                const selectedIds = [];

                $(`.participantCheckbox[data-tab="${tab}"]:checked`).each(function() {
                    selectedIds.push($(this).data('id'));
                });

                console.log('Selected participant IDs for reject:', selectedIds);

                if (selectedIds.length === 0) {
                    alert('Please select at least one participant payment to reject.');
                    return;
                }

                showBulkActionModal('reject', 'participants-payment', selectedIds,
                    'participant payment(s)');
            });

            // Bulk Approve General Payments
            $(document).on('click', '.btnBulkApproveGeneral', function(e) {
                e.preventDefault();
                console.log('Bulk approve general clicked');

                const tab = $(this).data('tab');
                const selectedIds = [];

                $(`.generalCheckbox[data-tab="${tab}"]:checked`).each(function() {
                    selectedIds.push($(this).data('id'));
                });

                console.log('Selected general payment IDs for approve:', selectedIds);

                if (selectedIds.length === 0) {
                    alert('Please select at least one general payment to approve.');
                    return;
                }

                showBulkActionModal('approve', 'payments', selectedIds, 'general payment(s)');
            });

            // Bulk Reject General Payments
            $(document).on('click', '.btnBulkRejectGeneral', function(e) {
                e.preventDefault();
                console.log('Bulk reject general clicked');

                const tab = $(this).data('tab');
                const selectedIds = [];

                $(`.generalCheckbox[data-tab="${tab}"]:checked`).each(function() {
                    selectedIds.push($(this).data('id'));
                });

                console.log('Selected general payment IDs for reject:', selectedIds);

                if (selectedIds.length === 0) {
                    alert('Please select at least one general payment to reject.');
                    return;
                }

                showBulkActionModal('reject', 'payments', selectedIds, 'general payment(s)');
            });

            function showBulkActionModal(action, type, selectedIds, itemType) {
                try {
                    console.log('showBulkActionModal called with:', {
                        action,
                        type,
                        selectedIds,
                        itemType
                    });

                    const actionText = action.charAt(0).toUpperCase() + action.slice(1);
                    const actionColor = action === 'approve' ? 'success' : 'danger';

                    $('#bulkActionModalLabel').text(`Bulk ${actionText} Confirmation`);
                    $('#bulkActionMessage').html(
                        `Are you sure you want to <strong>${action}</strong> ${selectedIds.length} ${itemType}?`
                    );

                    $('#bulkActionConfirmBtn')
                        .removeClass('btn-primary btn-success btn-danger')
                        .addClass(`btn-${actionColor}`)
                        .text(`${actionText} Selected Items`);

                    const formAction = `/bulk-action`;
                    console.log('Setting form action to:', formAction);

                    $('#bulkActionForm').attr('action', formAction);

                    // Clear existing hidden inputs first
                    $('#bulkActionForm').find('input[name="selected_ids"]').remove();
                    $('#bulkActionForm').find('input[name="type"]').remove();
                    $('#bulkActionForm').find('input[name="action"]').remove();
                    $('#bulkActionForm').append(`<input type="hidden" name="action" value="${action}">`);

                    // Add hidden inputs for selected IDs and type
                    $('#bulkActionForm').append(
                        `<input type="hidden" name="selected_ids" value="${selectedIds.join(',')}">`
                    );
                    $('#bulkActionForm').append(
                        `<input type="hidden" name="type" value="${type}">`
                    );

                    // Create list of selected items
                    let itemsList =
                        '<div class="border rounded p-2 bg-light"><small class="text-muted">Selected items:</small><br>';
                    selectedIds.forEach(id => {
                        itemsList += `<span class="badge bg-secondary me-1">ID: ${id}</span>`;
                    });
                    itemsList += '</div>';
                    $('#selectedItemsList').html(itemsList);

                    // Clear remarks
                    $('#bulkRemarks').val('');

                    console.log('About to show bulk action modal');
                    bulkActionModal.show();

                } catch (error) {
                    console.error('Error in showBulkActionModal:', error);
                    alert('An error occurred while preparing the bulk action modal. Please try again.');
                }
            }

            // Handle bulk action form submission
            $(document).on('submit', '#bulkActionForm', function(e) {
                e.preventDefault();
                console.log('Bulk action form submitted');

                const formData = new FormData(this);
                const actionUrl = $(this).attr('action');

                // Add CSRF token if not already present
                if (!formData.has('_token')) {
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                }

                // Show loading state
                const submitBtn = $('#bulkActionConfirmBtn');
                const originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('Processing...');

                // Debug log
                console.log('Submitting bulk action:', {
                    url: actionUrl,
                    selectedIds: formData.get('selected_ids'),
                    type: formData.get('type'),
                    remarks: formData.get('remarks'),
                    token: formData.get('_token')
                });

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Bulk action success:', response);
                        bulkActionModal.hide();
                        const message = response.message ||
                            'Bulk action completed successfully!';
                        showAlert('success', message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        console.error('Bulk action error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error
                        });

                        bulkActionModal.hide();

                        let errorMessage =
                            'An error occurred while processing the bulk action.';
                        if (xhr.status === 419) {
                            errorMessage =
                                'Session expired. Please refresh the page and try again.';
                        } else if (xhr.status === 404) {
                            errorMessage =
                                'Bulk action endpoint not found. Please check your routes.';
                        } else if (xhr.status === 422) {
                            errorMessage = 'Validation error. Please check your input.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        showAlert('danger', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Helper function to show alerts
            function showAlert(type, message) {
                const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

                $('.container-fluid .alert').remove();
                $('.container-fluid h2').after(alertHtml);

                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }

            // OTHER EVENT HANDLERS (unchanged)

            $(document).on('click', '.btnOpenPaymentCreate', function() {
                fetch("{{ route('payments.create') }}")
                    .then(response => response.text())
                    .then(html => {
                        const modalContent = document.getElementById('modalPaymentContent');
                        modalContent.innerHTML = html;
                        const modalEl = document.getElementById('paymentModal');
                        const modal = new bootstrap.Modal(modalEl);

                        modalEl.addEventListener('shown.bs.modal', function() {
                            setTimeout(function() {
                                $(modalContent).find('.select2').each(function() {
                                    if ($(this).hasClass(
                                            'select2-hidden-accessible')) {
                                        $(this).select2('destroy');
                                    }
                                });

                                $(modalContent).find('.select2').each(function() {
                                    const $this = $(this);
                                    const placeholder = $this.find(
                                            'option:first').text() ||
                                        '-- Select --';

                                    $this.select2({
                                        placeholder: placeholder,
                                        allowClear: true,
                                        width: '100%',
                                        minimumResultsForSearch: 0,
                                        dropdownParent: modalEl,
                                        escapeMarkup: function(markup) {
                                            return markup;
                                        }
                                    });
                                });

                                if (typeof initializeTotalTransferCalculation ===
                                    'function') {
                                    initializeTotalTransferCalculation();
                                }
                            }, 100);
                        }, {
                            once: true
                        });

                        modal.show();
                    })
                    .catch(err => console.error("Gagal load modal:", err));
            });

            $(document).on('click', '.btnOpenPartPaymentCreate', function() {
                fetch("{{ route('participants-payment.create') }}")
                    .then(response => response.text())
                    .then(html => {
                        const modalContent = document.getElementById('modalPaymentContent');
                        modalContent.innerHTML = html;
                        const modalEl = document.getElementById('paymentModal');
                        const modal = new bootstrap.Modal(modalEl);

                        modalEl.addEventListener('shown.bs.modal', function() {
                            setTimeout(function() {
                                $(modalContent).find('.select2').each(function() {
                                    if ($(this).hasClass(
                                            'select2-hidden-accessible')) {
                                        $(this).select2('destroy');
                                    }
                                });

                                $(modalContent).find('.select2').each(function() {
                                    const $this = $(this);
                                    const placeholder = $this.find(
                                            'option:first').text() ||
                                        '-- Select --';

                                    $this.select2({
                                        placeholder: placeholder,
                                        allowClear: true,
                                        width: '100%',
                                        minimumResultsForSearch: 0,
                                        dropdownParent: modalEl,
                                        escapeMarkup: function(markup) {
                                            return markup;
                                        }
                                    });
                                });

                                if (typeof initializeTotalTransferCalculation ===
                                    'function') {
                                    initializeTotalTransferCalculation();
                                }
                            }, 100);
                        }, {
                            once: true
                        });

                        modal.show();
                    })
                    .catch(err => console.error("Gagal load modal:", err));
            });

            $(document).on('click', '.btnShowGeneralPayment', function() {
                const paymentId = $(this).data('id');
                fetch(`/payments/${paymentId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalPaymentContent').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('paymentModal')).show();
                    })
                    .catch(err => console.error("Gagal load view modal:", err));
            });

            $(document).on('click', '.btnShowParticipantPayment', function() {
                const id = $(this).data('id');
                fetch(`/participants-payment/${id}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalPaymentContent').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('paymentModal')).show();
                    })
                    .catch(err => console.error("Gagal load view modal:", err));
            });

            // Participant payment actions
            $(document).on('click', '.btnAction', function(e) {
                e.preventDefault();
                console.log('Participant button clicked:', $(this).data('action'));

                const id = $(this).data('id');
                const action = $(this).data('action');
                const url = `/participants-payment/${id}/${action}`;
                $('#confirmFormParticipant').attr('action', url);

                let confirmationMessage = '';
                let actionLabel = action.charAt(0).toUpperCase() + action.slice(1);

                if (action === 'checkbypic' || action === 'checkbymanager') {
                    confirmationMessage =
                        'Are you sure this payment is reviewed and comply with the term and condition?';
                    actionLabel = action === 'checkbypic' ? 'Check by PIC' : 'Check by Manager';
                } else {
                    confirmationMessage =
                        `Are you sure you want to <strong>${actionLabel}</strong> this payment?`;
                }

                $('#confirmModal .modal-body p').html(confirmationMessage);
                $('#modalActionLabelParticipant').text(actionLabel);
                $('#remarksParticipant').val('');
                confirmModal.show();
            });

            // General payment actions
            $(document).on('click', '.btnActionGP', function(e) {
                e.preventDefault();
                console.log('General payment button clicked:', $(this).data('action'));

                const id = $(this).data('id');
                const action = $(this).data('action');
                const url = `/payments/${id}/${action}`;
                $('#confirmFormGeneral').attr('action', url);

                let confirmationMessage = '';
                let actionLabel = action.charAt(0).toUpperCase() + action.slice(1);

                if (action === 'checkbypic' || action === 'checkbymanager') {
                    confirmationMessage =
                        'Are you sure this payment is reviewed and comply with the term and condition?';
                    actionLabel = action === 'checkbypic' ? 'Check by PIC' : 'Check by Manager';
                } else {
                    confirmationMessage =
                        `Are you sure you want to <strong>${actionLabel}</strong> this payment?`;
                }

                $('#confirmModalGP .modal-body p').html(confirmationMessage);
                $('#modalActionLabelGeneral').text(actionLabel);
                $('#remarksGeneral').val('');
                confirmModalGP.show();
            });
        });

        // Total Transfer Calculation Function
        function initializeTotalTransferCalculation() {
            const amountFee = document.getElementById('amount_fee');
            const ppnFee = document.getElementById('ppn_fee');
            const pphFee = document.getElementById('pph_fee');
            const totalTransfer = document.getElementById('total_transfer');

            if (!amountFee || !ppnFee || !pphFee || !totalTransfer) {
                console.log('Total transfer calculation elements not found - may not be needed for this form');
                return;
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            function calculateTotalTransfer() {
                const amount = parseFloat(amountFee.value) || 0;
                const ppn = parseFloat(ppnFee.value) || 0;
                const pph = parseFloat(pphFee.value) || 0;
                const total = amount + ppn - pph;

                totalTransfer.value = formatCurrency(total);

                if (total < 0) {
                    totalTransfer.classList.add('text-danger');
                    totalTransfer.style.fontWeight = 'bold';
                } else {
                    totalTransfer.classList.remove('text-danger');
                    totalTransfer.style.fontWeight = '';
                }

                return total;
            }

            const inputs = [amountFee, ppnFee, pphFee];
            inputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    clearTimeout(input._calcTimeout);
                    input._calcTimeout = setTimeout(calculateTotalTransfer, 100);
                });
                input.addEventListener('change', calculateTotalTransfer);
                input.addEventListener('blur', function() {
                    if (this.value && !isNaN(this.value)) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });
                input.addEventListener('focus', function() {
                    this.select();
                });
            });

            calculateTotalTransfer();
            console.log('Total Transfer calculation initialized successfully');
        }

        window.initializeTotalTransferCalculation = initializeTotalTransferCalculation;
    </script>
@endsection
