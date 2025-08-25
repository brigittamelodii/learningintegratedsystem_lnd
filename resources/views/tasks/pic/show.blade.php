@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3">
        {{-- Enhanced Header --}}
        <div class="compact-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-tasks fa-2x text-primary me-3"></i>
                        <div>
                            <h3 class="mb-1 fw-bold">{{ $task->subjectName }}</h3>
                            <p class="text-muted mb-0">{{ Str::limit($task->subjectDesc, 150) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end text-start">
                    <div class="btn-group-enhanced d-flex flex-wrap gap-2 justify-content-lg-end">
                        {{-- Status Control Buttons --}}
                        @if ($task->isOpen())
                            <button type="button" class="btn btn-danger btn-sm" onclick="closeTask({{ $task->id }})">
                                <i class="fas fa-times-circle me-1"></i> Close Task
                            </button>
                        @elseif($task->isClosed())
                            <button type="button" class="btn btn-success btn-sm" onclick="reopenTask({{ $task->id }})">
                                <i class="fas fa-unlock me-1"></i> Reopen Task
                            </button>
                        @endif

                        @if (!$task->isClosed())
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        @endif

                        {{-- Delete Button - Only show if no submissions exist --}}
                        @if ($stats['total'] == 0)
                            <button type="button" class="btn btn-delete btn-sm" onclick="deleteTask({{ $task->id }})">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        @endif

                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Alert for Closed Tasks --}}
        @if ($task->isClosed())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Task Closed</h6>
                        <p class="mb-0">This task is currently closed. No new submissions are allowed.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Left Column: Task Info & Statistics --}}
            <div class="col-xl-5 col-lg-6">
                {{-- Enhanced Task Info Card --}}
                <div class="card compact-card section-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Task Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label fw-bold text-muted small">ACCESS CODE</label>
                                    <div class="d-flex align-items-center">
                                        <code
                                            class="bg-light px-3 py-2 rounded-pill fs-5 fw-bold text-primary me-2">{{ $task->accessCode }}</code>
                                        <button class="btn btn-outline-primary btn-xs"
                                            onclick="copyCode('{{ $task->accessCode }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="info-item mt-3">
                                    <label class="form-label fw-bold text-muted small">STATUS</label>
                                    <div>
                                        @php
                                            $statusColors = [
                                                'open' => 'success',
                                                'closed' => 'danger',
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                            ];
                                            $statusIcons = [
                                                'open' => 'unlock',
                                                'closed' => 'lock',
                                                'draft' => 'edit',
                                                'pending' => 'clock',
                                            ];
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }} badge-sm fs-6 px-3 py-2">
                                            <i class="fas fa-{{ $statusIcons[$task->status] ?? 'question' }} me-2"></i>
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label fw-bold text-muted small">CREATED</label>
                                    <div class="text-dark">{{ $task->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted small">{{ $task->created_at->format('H:i') }}</div>
                                </div>
                                <div class="info-item mt-3">
                                    <label class="form-label fw-bold text-muted small">DUE DATE</label>
                                    @if ($task->due_date)
                                        <div class="text-dark {{ $task->due_date->isPast() ? 'text-danger' : '' }}">
                                            {{ $task->due_date->format('M d, Y H:i') }}
                                            @if ($task->due_date->isPast())
                                                <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                            @endif
                                        </div>
                                        <div class="text-muted small">{{ $task->due_date->diffForHumans() }}</div>
                                    @else
                                        <div class="text-muted">No due date set</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Enhanced Statistics Cards --}}
                <div class="card compact-card section-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>Submission Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-lg-4">
                                <div class="stat-card bg-primary bg-opacity-10 text-primary">
                                    <div class="stat-number">{{ $stats['total'] }}</div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="stat-card bg-warning bg-opacity-10 text-warning">
                                    <div class="stat-number">{{ $stats['pending'] }}</div>
                                    <div class="stat-label">Pending</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="stat-card bg-success bg-opacity-10 text-success">
                                    <div class="stat-number">{{ $stats['accepted'] }}</div>
                                    <div class="stat-label">Accepted</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="stat-card bg-info bg-opacity-10 text-info">
                                    <div class="stat-number">{{ $stats['in_review'] }}</div>
                                    <div class="stat-label">In Review</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="stat-card bg-danger bg-opacity-10 text-danger">
                                    <div class="stat-number">{{ $stats['rejected'] }}</div>
                                    <div class="stat-label">Rejected</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="stat-card bg-secondary bg-opacity-10 text-secondary">
                                    <div class="stat-number">{{ $stats['draft'] }}</div>
                                    <div class="stat-label">Draft</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Submissions & Categories --}}
            <div class="col-xl-7 col-lg-6">
                {{-- Enhanced Submissions Card --}}
                <div class="card compact-card section-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2 text-primary"></i>Submissions
                            <span class="badge bg-primary rounded-pill">{{ $taskFills->total() }}</span>
                        </h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                            <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                </option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="in_review" {{ request('status') == 'in_review' ? 'selected' : '' }}>In
                                    Review</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>
                                    Accepted</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($taskFills->count())
                            <div class="overflow-auto-custom">
                                <table class="table table-hover submissions-table mb-0">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Participant</th>
                                            <th width="100"><i class="fas fa-file me-1"></i>Files</th>
                                            <th width="100"><i class="fas fa-flag me-1"></i>Status</th>
                                            <th width="120"><i class="fas fa-clock me-1"></i>Submitted</th>
                                            <th width="80"><i class="fas fa-cog me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($taskFills as $fill)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-3">
                                                            {{ substr($fill->fillerName, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">
                                                                {{ Str::limit($fill->fillerName, 20) }}</div>
                                                            <div class="text-muted small">
                                                                {{ Str::limit($fill->user->email ?? 'N/A', 25) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-info badge-sm">{{ $fill->fillDocuments->count() }}</span>
                                                    @if ($fill->fillDocuments->count())
                                                        <div class="text-muted small">
                                                            {{ $fill->fillDocuments->sortByDesc('created_at')->first()->created_at->diffForHumans() }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'draft' => 'secondary',
                                                            'pending' => 'warning',
                                                            'in_review' => 'info',
                                                            'accepted' => 'success',
                                                            'rejected' => 'danger',
                                                        ];
                                                        $statusIcons = [
                                                            'draft' => 'edit',
                                                            'pending' => 'clock',
                                                            'in_review' => 'eye',
                                                            'accepted' => 'check-circle',
                                                            'rejected' => 'times-circle',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $statusColors[$fill->status] ?? 'secondary' }} badge-sm">
                                                        <i
                                                            class="fas fa-{{ $statusIcons[$fill->status] ?? 'question' }} me-1"></i>
                                                        {{ ucfirst(str_replace('_', ' ', $fill->status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($fill->status !== 'draft')
                                                        <div class="fw-semibold small">
                                                            {{ $fill->updated_at->format('M d, H:i') }}</div>
                                                        <div class="text-muted small">
                                                            {{ $fill->updated_at->diffForHumans() }}</div>
                                                    @else
                                                        <span class="text-muted small"><i>Not submitted</i></span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($fill->status === 'pending' || $fill->status === 'in_review')
                                                        <a href="{{ route('tasks.review', $task) }}?fill={{ $fill->id }}"
                                                            class="btn btn-xs btn-primary">
                                                            <i class="fas fa-gavel"></i> Review
                                                        </a>
                                                    @else
                                                        <button class="btn btn-xs btn-outline-secondary"
                                                            onclick="viewSubmission({{ $fill->id }})">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Enhanced Pagination --}}
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light">
                                <div class="text-muted small">
                                    Showing <strong>{{ $taskFills->firstItem() }}</strong> to
                                    <strong>{{ $taskFills->lastItem() }}</strong>
                                    of <strong>{{ $taskFills->total() }}</strong> results
                                </div>
                                {{ $taskFills->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-inbox fa-4x text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted">No submissions yet</h5>
                                <p class="text-muted">Participants haven't submitted any documents yet.</p>
                                @if ($stats['total'] == 0)
                                    <div class="mt-4">
                                        <p class="text-muted small">Since there are no submissions, you can safely
                                            delete this task if needed.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Enhanced Categories Card --}}
                <div class="card compact-card section-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h6 class="mb-0">
                            <i class="fas fa-folder me-2 text-primary"></i>Categories
                            <span class="badge bg-primary rounded-pill">{{ $taskCategories->total() }}</span>
                        </h6>
                        <select class="form-select form-select-sm" id="categoriesPerPageSelect" style="width: auto;">
                            <option value="6" {{ request('categories_per_page') == 6 ? 'selected' : '' }}>6
                            </option>
                            <option value="12" {{ request('categories_per_page') == 12 ? 'selected' : '' }}>12
                            </option>
                            <option value="24" {{ request('categories_per_page') == 24 ? 'selected' : '' }}>24
                            </option>
                        </select>
                    </div>
                    <div class="card-body">
                        @if ($taskCategories->count())
                            <div class="categories-grid">
                                @foreach ($taskCategories as $category)
                                    @php
                                        $categoryDocuments = \App\Models\TaskDocument::where(
                                            'taskcategory_id',
                                            $category->id,
                                        )->count();
                                        $acceptedDocs = \App\Models\TaskDocument::where(
                                            'taskcategory_id',
                                            $category->id,
                                        )
                                            ->where('status', 'accepted')
                                            ->count();
                                        $pendingDocs = \App\Models\TaskDocument::where('taskcategory_id', $category->id)
                                            ->where('status', 'pending')
                                            ->count();
                                    @endphp
                                    <div class="category-card">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="card-title mb-0 fw-bold text-dark">
                                                {{ Str::limit($category->categoryName, 30) }}
                                            </h6>
                                            <small
                                                class="text-muted bg-light px-2 py-1 rounded">#{{ $category->id }}</small>
                                        </div>

                                        @if ($category->categoryDesc)
                                            <p class="text-muted small mb-3 lh-sm">
                                                {{ Str::limit($category->categoryDesc, 80) }}
                                            </p>
                                        @endif

                                        <div class="d-flex gap-2 flex-wrap mt-auto">
                                            <span class="badge bg-primary badge-sm">
                                                <i class="fas fa-file me-1"></i>{{ $categoryDocuments }} docs
                                            </span>
                                            @if ($acceptedDocs > 0)
                                                <span class="badge bg-success badge-sm">
                                                    <i class="fas fa-check me-1"></i>{{ $acceptedDocs }}
                                                </span>
                                            @endif
                                            @if ($pendingDocs > 0)
                                                <span class="badge bg-warning badge-sm">
                                                    ⏳ {{ $pendingDocs }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Categories Pagination --}}
                            @if ($taskCategories->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        {{ $taskCategories->firstItem() }}-{{ $taskCategories->lastItem() }} of
                                        {{ $taskCategories->total() }}
                                    </small>
                                    {{ $taskCategories->appends(request()->except('categories'))->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="text-muted">No categories defined yet</h6>
                                <p class="text-muted small">Add categories to organize document submissions.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set up CSRF token for all AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Per page selector for participants
        document.getElementById('perPageSelect').addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('fills'); // Reset to first page
            window.location.href = url.toString();
        });

        // Categories per page selector
        document.getElementById('categoriesPerPageSelect').addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('categories_per_page', this.value);
            url.searchParams.delete('categories'); // Reset to first page
            window.location.href = url.toString();
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const url = new URL(window.location);
            if (this.value) {
                url.searchParams.set('status', this.value);
            } else {
                url.searchParams.delete('status');
            }
            url.searchParams.delete('fills'); // Reset to first page
            window.location.href = url.toString();
        });
    });

    function copyCode(code) {
        navigator.clipboard.writeText(code).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Access code copied to clipboard!',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                background: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                color: 'white'
            });
        }).catch(function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Access code copied to clipboard!',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
    }

    function deleteTask(taskId) {
        Swal.fire({
            title: 'Delete Task?',
            html: `
                <div class="text-start">
                    <p class="mb-2"><strong>Are you sure you want to delete this task?</strong></p>
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    <ul class="text-muted small mb-0">
                        <li>All task categories will be deleted</li>
                        <li>This task will be permanently removed</li>
                        <li>Participants will no longer have access</li>
                    </ul>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Yes, Delete Task',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn-delete',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting Task...',
                    html: `
                        <div class="text-center">
                            <div class="spinner-border text-danger mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0">Please wait while we delete the task.</p>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                // Make AJAX request to delete
                fetch(`/tasks/${taskId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Task Deleted!',
                                text: data.message || 'Task has been deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false,
                                background: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                                color: 'white'
                            }).then(() => {
                                window.location.href = '/tasks';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to delete task',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the task',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            }
                        });
                    });
            }
        });
    }

    function closeTask(taskId) {
        Swal.fire({
            title: 'Close Task?',
            html: `
                <div class="text-start">
                    <p class="mb-3"><strong>Are you sure you want to close this task?</strong></p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No new submissions will be allowed after closing.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-lock me-1"></i> Yes, Close Task',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Closing Task...',
                    html: `
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0">Please wait while we close the task.</p>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                // Make AJAX request
                fetch(`/tasks/${taskId}/close`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Task Closed!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false,
                                background: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                                color: 'white'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to close task'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while closing the task'
                        });
                    });
            }
        });
    }

    function reopenTask(taskId) {
        Swal.fire({
            title: 'Reopen Task?',
            html: `
                <div class="text-start">
                    <p class="mb-3"><strong>Are you sure you want to reopen this task?</strong></p>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-unlock me-2"></i>
                        Participants will be able to submit documents again.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-unlock me-1"></i> Yes, Reopen Task',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Reopening Task...',
                    html: `
                        <div class="text-center">
                            <div class="spinner-border text-success mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0">Please wait while we reopen the task.</p>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                // Make AJAX request
                fetch(`/tasks/${taskId}/reopen`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Task Reopened!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false,
                                background: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                                color: 'white'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to reopen task'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while reopening the task'
                        });
                    });
            }
        });
    }

    function viewSubmission(fillId) {
        // Show loading
        Swal.fire({
            title: 'Loading Submission...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0">Please wait while we fetch the submission details.</p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        // Fetch submission details
        fetch(`/tasks/submission/${fillId}/details`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSubmissionModal(data.submission);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to fetch submission details'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while fetching submission details'
                });
            });
    }

    function showSubmissionModal(submission) {
        // Close any existing SweetAlert
        Swal.close();

        // Create documents HTML
        let documentsHtml = '';
        if (submission.documents && submission.documents.length > 0) {
            documentsHtml = submission.documents.map(doc => {
                const statusColors = {
                    'draft': 'secondary',
                    'pending': 'warning',
                    'accepted': 'success',
                    'rejected': 'danger'
                };

                const statusIcons = {
                    'draft': 'edit',
                    'pending': 'clock',
                    'accepted': 'check-circle',
                    'rejected': 'times-circle'
                };

                return `
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="card-title mb-0 fw-bold">${doc.category_name}</h6>
                                <span class="badge bg-${statusColors[doc.status] || 'secondary'} fs-6 px-3 py-2">
                                    <i class="fas fa-${statusIcons[doc.status] || 'question'} me-2"></i>
                                    ${doc.status.charAt(0).toUpperCase() + doc.status.slice(1)}
                                </span>
                            </div>
                            
                            ${doc.document_file ? `
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-muted small">FILE:</label>
                                                <div class="d-flex align-items-center">
                                                    <a href="/tasks/document/${doc.id}/download" class="text-decoration-none btn btn-outline-primary btn-sm me-2" target="_blank">
                                                        <i class="fas fa-download me-2"></i>${doc.document_name}
                                                    </a>
                                                    <button class="btn btn-outline-info btn-sm" onclick="previewDocument(${doc.id})">
                                                        <i class="fas fa-eye me-1"></i> Preview
                                                    </button>
                                                </div>
                                            </div>
                                        ` : '<div class="mb-3"><label class="form-label fw-bold text-muted small">FILE:</label><div class="text-muted fst-italic">No file uploaded</div></div>'}
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">DESCRIPTION:</label>
                                <p class="mb-0 text-dark">${doc.document_desc || 'No description provided'}</p>
                            </div>
                            
                            ${doc.comment ? `
                                            <div class="alert alert-info border-0 mb-0">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-comment-alt fa-lg me-3 mt-1"></i>
                                                    <div>
                                                        <strong>Review Comment:</strong>
                                                        <p class="mb-0 mt-1">${doc.comment}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            documentsHtml = `
                <div class="text-center text-muted py-5">
                    <i class="fas fa-file-alt fa-3x mb-3 opacity-50"></i>
                    <h6>No documents submitted</h6>
                </div>
            `;
        }

        // Create modal HTML with enhanced styling
        const modalHtml = `
            <div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold" id="submissionModalLabel">
                                <i class="fas fa-file-alt me-2"></i>Submission Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <!-- Participant Info -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-user me-2 text-primary"></i>Participant Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <label class="form-label fw-bold text-muted small">NAME:</label>
                                                <p class="mb-0 fs-5 fw-semibold">${submission.filler_name}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <label class="form-label fw-bold text-muted small">EMAIL:</label>
                                                <p class="mb-0">${submission.user_email || 'N/A'}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <label class="form-label fw-bold text-muted small">STATUS:</label>
                                                <div>
                                                    <span class="badge bg-${submission.status === 'accepted' ? 'success' : submission.status === 'rejected' ? 'danger' : submission.status === 'pending' ? 'warning' : 'secondary'} fs-6 px-3 py-2">
                                                        ${submission.status.charAt(0).toUpperCase() + submission.status.slice(1)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <label class="form-label fw-bold text-muted small">SUBMITTED:</label>
                                                <p class="mb-0">${new Date(submission.updated_at).toLocaleDateString('en-US', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-folder me-2 text-primary"></i>Submitted Documents 
                                        <span class="badge bg-primary rounded-pill">${submission.documents ? submission.documents.length : 0}</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    ${documentsHtml}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            ${submission.status === 'pending' || submission.status === 'in_review' ? `
                                            <a href="/tasks/${submission.task_id}/review?fill=${submission.id}" class="btn btn-primary btn-lg">
                                                <i class="fas fa-gavel me-2"></i>Review Submission
                                            </a>
                                        ` : ''}
                            <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('submissionModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
        modal.show();

        // Clean up when modal is hidden
        document.getElementById('submissionModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    function previewDocument(documentId) {
        // Open document preview in new tab
        window.open(`/tasks/document/${documentId}/preview`, '_blank');
    }
</script>
<style>
    :root {
        --primary: #0d6efd;
        --secondary: #6c757d;
        --success: #198754;
        --danger: #dc3545;
        --warning: #ffc107;
        --info: #0dcaf0;
        --light: #f8f9fa;
        --dark: #212529;
    }

    .compact-card {
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .compact-card:hover {
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .stat-card {
        text-align: center;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        background: linear-gradient(45deg, currentColor, rgba(255, 255, 255, 0.8));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .category-card {
        height: 140px;
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--info));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .category-card:hover {
        border-color: var(--primary);
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.2);
        transform: translateY(-5px);
    }

    .category-card:hover::before {
        transform: scaleX(1);
    }

    .submissions-table {
        font-size: 0.875rem;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .submissions-table th {
        padding: 0.75rem;
        border-top: none;
        font-weight: 700;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .submissions-table td {
        padding: 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }

    .submissions-table tbody tr {
        transition: all 0.2s ease;
    }

    .submissions-table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: scale(1.01);
    }

    .avatar-xs {
        width: 40px;
        height: 40px;
        font-size: 0.875rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), var(--info));
        color: white;
        font-weight: 600;
    }

    .compact-header {
        padding: 1.5rem 0;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #e9ecef;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);
        margin: -1rem -1rem 1.5rem -1rem;
        padding: 2rem 2rem 1.5rem 2rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .section-card {
        margin-bottom: 1.5rem;
    }

    .section-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
        font-weight: 700;
        padding: 1rem 1.25rem;
    }

    .section-card .card-body {
        padding: 1.25rem;
    }

    .badge-sm {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
        font-weight: 600;
    }

    .btn-xs {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        border-radius: 0.375rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-xs:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
    }

    .overflow-auto-custom {
        max-height: 450px;
        overflow-y: auto;
        border-radius: 0.5rem;
    }

    .categories-grid {
        max-height: 350px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    .categories-grid::-webkit-scrollbar {
        width: 4px;
    }

    .categories-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .categories-grid::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 2px;
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
        font-weight: 500;
    }

    .alert-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
        border-left: 4px solid var(--warning);
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
    }

    .btn-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        border: none;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: none;
        color: #000;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
    }

    .pagination {
        border-radius: 0.5rem;
    }

    .page-link {
        border-radius: 0.375rem;
        margin: 0 0.125rem;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }

    .page-link:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        transform: translateY(-1px);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
        border-color: var(--primary);
    }

    /* Enhanced delete button styles */
    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        border: none;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #bb2d3b 0%, #a02834 100%);
        color: white;
    }

    .btn-delete::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .btn-delete:hover::before {
        left: 100%;
    }

    /* Responsive enhancements */
    @media (max-width: 768px) {
        .compact-header {
            margin: -1rem -0.5rem 1rem -0.5rem;
            padding: 1.5rem 1rem;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .category-card {
            height: auto;
            min-height: 120px;
        }
    }
</style>
