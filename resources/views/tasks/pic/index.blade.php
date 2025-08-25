@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-center text-primary fw-bold" style="margin-top: 10px">Tasks List</h2>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Task
            </a>
        </div>

        {{-- Flash Messages --}}
        @foreach (['success', 'error'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible fade show"
                    role="alert">
                    {{ session($msg) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Task List</h6>
            </div>
            <div class="card-body">
                @if ($tasks->count())
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Access Code</th>
                                    <th>Categories</th>
                                    <th>Participants</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    @php
                                        $total = $task->taskFills->count();
                                        $pending = $task->taskFills->where('status', 'pending')->count();
                                        $accepted = $task->taskFills->where('status', 'accepted')->count();
                                        $rejected = $task->taskFills->where('status', 'rejected')->count();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $task->subjectName }}</strong>
                                            <small
                                                class="text-muted d-block">{{ Str::limit($task->subjectDesc, 50) }}</small>
                                        </td>
                                        <td>
                                            <code class="access-code" data-code="{{ $task->accessCode }}">
                                                {{ $task->accessCode }}
                                            </code>
                                            <button class="btn btn-sm btn-outline-secondary ms-2"
                                                onclick="copyCode('{{ $task->accessCode }}')">
                                                Copy
                                            </button>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $task->taskCategories->count() }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <span class="badge bg-secondary">Total: {{ $total }}</span>
                                                @if ($pending)
                                                    <span class="badge bg-warning">Pending: {{ $pending }}</span>
                                                @endif
                                                @if ($accepted)
                                                    <span class="badge bg-success">Accepted: {{ $accepted }}</span>
                                                @endif
                                                @if ($rejected)
                                                    <span class="badge bg-danger">Rejected: {{ $rejected }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($task->due_date)
                                                <span class="{{ $task->due_date->isPast() ? 'text-danger' : '' }}">
                                                    {{ $task->due_date->format('M d, Y H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($task->isOpen())
                                                <span class="badge bg-success">Open</span>
                                            @elseif($task->isDraft())
                                                <span class="badge bg-secondary">Draft</span>
                                            @else
                                                <span class="badge bg-danger">Closed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <!-- Tombol View dan Review -->
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('tasks.show', $task) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>

                                                    @if ($pending)
                                                        <a href="{{ route('tasks.review', $task) }}"
                                                            class="btn btn-sm btn-outline-warning">
                                                            Review
                                                        </a>
                                                    @endif
                                                </div>

                                                <!-- Tombol Delete -->
                                                @if ($task->taskFills->isEmpty())
                                                    <a href="#" class="btn btn-sm btn-danger"
                                                        onclick="event.preventDefault(); document.getElementById('delete-form-{{ $task->id }}').submit();">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>

                                                    <form id="delete-form-{{ $task->id }}"
                                                        action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                                        style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif

                                                <!-- Tombol Edit -->
                                                @if ($task->user_id === Auth::id())
                                                    <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning">
                                                        <i class="fas fa-edit"></i> Edit Task
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $tasks->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No tasks created yet</h5>
                        <p class="text-muted">Create your first task to get started!</p>
                        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Task
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

<script>
    function copyCode(code) {
        navigator.clipboard.writeText(code).then(function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">Access code copied to clipboard!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>`;
            document.body.appendChild(toast);

            const toastBootstrap = new bootstrap.Toast(toast);
            toastBootstrap.show();

            setTimeout(() => toast.remove(), 3000);
        });
    }
</script>
