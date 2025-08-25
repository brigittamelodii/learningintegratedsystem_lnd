@extends('layouts.app')

<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Task List</h1>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#joinTaskModal">
                <i class="fas fa-plus"></i> Join Task
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">My Joined Tasks</h6>
            </div>
            <div class="card-body">
                @if ($joinedTasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Description</th>
                                    <th>Due Date</th>
                                    <th>My Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($joinedTasks as $task)
                                    @php
                                        $userFill = $task->userFill;
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>{{ $task->subjectName }}</strong>
                                        </td>
                                        <td>
                                            {{ Str::limit($task->subjectDesc, 60) }}
                                        </td>
                                        <td>
                                            @if ($task->due_date)
                                                <span class="@if ($task->due_date->isPast()) text-danger @endif">
                                                    {{ $task->due_date->format('M d, Y H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>

                                        <td>
                                            @php
                                                $userFill = $task->userFill;
                                            @endphp

                                            @if ($userFill)
                                                @if ($userFill->isDraft())
                                                    <span class="badge bg-secondary">Draft</span>
                                                @elseif ($userFill->isPending())
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif ($userFill->isInReview())
                                                    <span class="badge bg-info text-dark">In Review</span>
                                                @elseif ($userFill->isAccepted())
                                                    <span class="badge bg-success">Accepted</span>
                                                @elseif ($userFill->isRejected())
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-light text-dark">Unknown</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Not Joined</span>
                                            @endif
                                        </td>


                                        <td>
                                            {{ $userFill->updated_at->format('M d, Y H:i') }}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if ($userFill->isDraft() || $userFill->isRejected())
                                                    <a href="{{ route('tasks.fill', $task) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                        {{ $userFill->isDraft() ? 'Continue' : 'Revise' }}
                                                    </a>
                                                @endif

                                                <a href="{{ route('tasks.show', $task) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>

                                                @if ($userFill->isRejected() && $userFill->remarks)
                                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                                        data-bs-target="#remarksModal-{{ $task->id }}">
                                                        <i class="fas fa-comment"></i> Remarks
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No tasks joined yet</h5>
                        <p class="text-muted">Join a task using the access code provided by your PIC!</p>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#joinTaskModal">
                            <i class="fas fa-plus"></i> Join Task
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Join Task Modal -->
    <div class="modal fade" id="joinTaskModal" tabindex="-1" aria-labelledby="joinTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="joinTaskModalLabel">Join Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="joinTaskForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="accessCode" class="form-label">Access Code</label>
                            <input type="text" class="form-control text-uppercase" id="accessCode" name="accessCode"
                                placeholder="Enter 6-character access code" maxlength="6" required
                                style="font-weight: bold;">
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Enter the access code provided by your PIC</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-sign-in-alt"></i> Join Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remarks Modals -->
    @foreach ($joinedTasks as $task)
        @php
            $userFill = $task->userFill;
        @endphp


        @if ($userFill->isRejected() && $userFill->remarks)
            <div class="modal fade" id="remarksModal-{{ $task->id }}" tabindex="-1"
                aria-labelledby="remarksModalLabel-{{ $task->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="remarksModalLabel-{{ $task->id }}">
                                Rejection Remarks - {{ $task->subjectName }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Your submission was rejected. Please review the remarks below and make necessary
                                corrections.
                            </div>
                            <div class="bg-light p-3 rounded">
                                <strong>Remarks:</strong><br>
                                {{ $userFill->remarks }}
                            </div>
                            @if ($userFill->reviewed_at)
                                <small class="text-muted d-block mt-2">
                                    Reviewed on: {{ $userFill->reviewed_at->format('M d, Y H:i') }}
                                </small>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="{{ route('tasks.fill', $task) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Revise Submission
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle access code input formatting
            const accessCodeInput = document.getElementById('accessCode');
            accessCodeInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase();
            });

            // Handle join task form submission
            document.getElementById('joinTaskForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(e.target);
                const submitBtn = e.target.querySelector('button[type="submit"]');
                const accessCodeInput = e.target.querySelector('#accessCode');

                // Clear previous errors
                accessCodeInput.classList.remove('is-invalid');
                e.target.querySelector('.invalid-feedback').textContent = '';

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Joining...';

                fetch('{{ route('tasks.join') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (!response.ok) {
                            throw data; // Ini akan masuk ke .catch()
                        }

                        // Success handling
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'joinTaskModal'));
                        modal.hide();
                        window.location.href = data.redirect;
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        const accessCodeInput = document.getElementById('accessCode');
                        const feedback = accessCodeInput.closest('.mb-3').querySelector(
                            '.invalid-feedback');

                        if (error?.errors?.accessCode) {
                            accessCodeInput.classList.add('is-invalid');
                            feedback.textContent = error.errors.accessCode[0];
                        } else {
                            accessCodeInput.classList.add('is-invalid');
                            feedback.textContent = error.message || 'An unknown error occurred.';
                        }
                    })
                    .finally(() => {
                        const submitBtn = document.querySelector('#joinTaskForm button[type="submit"]');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Join Task';
                    });

            });
        });
    </script>
@endsection
