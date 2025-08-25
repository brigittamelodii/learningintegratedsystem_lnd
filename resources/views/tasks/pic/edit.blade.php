<!-- resources/views/tasks/pic/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-gray-800">Edit Task</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Task
                </a>
            </div>
        </div>

        @if ($hasSubmissions)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> This task has submissions. Some changes might affect existing submissions.
                Categories with accepted documents cannot be deleted.
            </div>
        @endif

        <form id="editTaskForm">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Task Details -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Task Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="subjectName" class="form-label">Subject Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="subjectName" id="subjectName"
                                    value="{{ old('subjectName', $task->subjectName) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="subjectDesc" class="form-label">Description <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" name="subjectDesc" id="subjectDesc" rows="3" required>{{ old('subjectDesc', $task->subjectDesc) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" class="form-control" name="due_date" id="due_date"
                                    value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Categories <span class="text-danger">*</span></h5>
                            <button type="button" class="btn btn-sm btn-success" id="addCategory">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </div>
                        <div class="card-body" id="categoriesContainer">
                            @foreach ($task->taskCategories as $index => $category)
                                <div class="card mb-3 category-card" data-category="{{ $index + 1 }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Category {{ $index + 1 }}</h6>
                                        <div class="d-flex gap-2">
                                            @if ($hasSubmissions)
                                                <small class="text-muted me-2">
                                                    <i class="fas fa-info-circle"></i>
                                                    {{ $category->documents()->where('status', 'accepted')->count() }}
                                                    accepted docs
                                                </small>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeCategory({{ $index + 1 }})"
                                                {{ $hasSubmissions && $category->documents()->where('status', 'accepted')->exists() ? 'disabled title="Cannot delete category with accepted documents"' : '' }}>
                                                X
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" name="categories[{{ $index + 1 }}][id]"
                                            value="{{ $category->id }}">
                                        <div class="mb-3">
                                            <label class="form-label">Category Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control"
                                                name="categories[{{ $index + 1 }}][categoryName]"
                                                value="{{ old('categories.' . ($index + 1) . '.categoryName', $category->categoryName) }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Category Description</label>
                                            <textarea class="form-control" name="categories[{{ $index + 1 }}][categoryDesc]" rows="2">{{ old('categories.' . ($index + 1) . '.categoryDesc', $category->categoryDesc) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Task
                        </button>
                    </div>
                </div>

                <!-- Instructions & Info -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header text-info">
                            <h5 class="mb-0">Edit Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-edit text-info me-2"></i> You can modify task details and categories.
                                </li>
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i> Categories with accepted
                                    documents cannot be deleted.</li>
                                <li><i class="fas fa-users text-info me-2"></i> Changes may affect draft submissions.</li>
                                <li><i class="fas fa-clock text-info me-2"></i> Updating due date affects future submissions
                                    only.</li>
                            </ul>
                        </div>
                    </div>

                    @if ($hasSubmissions)
                        <div class="card mb-4">
                            <div class="card-header text-warning">
                                <h5 class="mb-0">Current Submissions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="text-primary">
                                            <h4>{{ $task->taskFills()->where('status', 'pending')->count() }}</h4>
                                            <small>Pending</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-success">
                                            <h4>{{ $task->taskFills()->where('status', 'accepted')->count() }}</h4>
                                            <small>Accepted</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Task Info</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Access Code:</strong> <code>{{ $task->accessCode }}</code></p>
                            <p><strong>Created:</strong> {{ $task->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $task->isOpen() ? 'success' : 'secondary' }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

<script>
    let categoryCount = {{ count($task->taskCategories) }};

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('addCategory').addEventListener('click', () => addCategory());
        document.getElementById('editTaskForm').addEventListener('submit', handleFormSubmit);
    });

    function addCategory() {
        categoryCount++;
        const container = document.getElementById('categoriesContainer');

        const html = `
        <div class="card mb-3 category-card" data-category="${categoryCount}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Category ${categoryCount} <span class="badge bg-success ms-2">New</span></h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCategory(${categoryCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="categories[${categoryCount}][categoryName]" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category Description</label>
                    <textarea class="form-control" name="categories[${categoryCount}][categoryDesc]" rows="2"></textarea>
                </div>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function removeCategory(id) {
        const categoryCard = document.querySelector(`[data-category="${id}"]`);
        const hiddenIdInput = categoryCard.querySelector('input[name*="[id]"]');

        if (document.querySelectorAll('.category-card').length <= 1) {
            alert('At least one category is required.');
            return;
        }

        // Jika kategori existing, mark for deletion
        if (hiddenIdInput && hiddenIdInput.value) {
            if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                // Add hidden input to mark for deletion
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = `categories[${id}][action]`;
                deleteInput.value = 'delete';
                categoryCard.appendChild(deleteInput);

                // Hide the card
                categoryCard.style.display = 'none';
            }
        } else {
            // New category, just remove from DOM
            categoryCard.remove();
        }
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');

        if (!confirm('Are you sure you want to update this task? This may affect existing submissions.')) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        const formData = new FormData(form);

        fetch('{{ route('tasks.update', $task->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'PUT'
                }
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    throw new Error(data.message || 'Error occurred');
                }
            }).catch(error => {
                alert('Error: ' + error.message);
            }).finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Task';
            });
    }
</script>
