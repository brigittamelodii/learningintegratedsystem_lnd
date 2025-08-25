@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-gray-800">Create New Task</h1>
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tasks
            </a>
        </div>

        <form id="taskForm">
            @csrf
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
                                <input type="text" class="form-control" name="subjectName" id="subjectName" required>
                            </div>
                            <div class="mb-3">
                                <label for="subjectDesc" class="form-label">Description <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" name="subjectDesc" id="subjectDesc" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" class="form-control" name="due_date" id="due_date">
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
                        <div class="card-body" id="categoriesContainer"></div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Task
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header text-info">
                            <h5 class="mb-0">Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-info-circle text-info me-2"></i> Fill in the task details and
                                    categories.</li>
                                <li><i class="fas fa-calendar text-info me-2"></i> Set a due date to close submissions
                                    automatically.</li>
                                <li><i class="fas fa-key text-info me-2"></i> Access code is generated for participants to
                                    join.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Access Code Modal -->
        <div class="modal fade" id="accessCodeModal" tabindex="-1" aria-labelledby="accessCodeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-success">
                        <h5 class="modal-title" id="accessCodeModalLabel">
                            <i class="fas fa-check-circle me-2"></i> Task Created Successfully!
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <h4>Your Access Code:</h4>
                        <div class="alert alert-info">
                            <code id="generatedAccessCode" style="font-size: 1.5rem; font-weight: bold;"></code>
                        </div>
                        <p class="text-muted">Share this code with participants so they can join your task.</p>
                        <button onclick="copyAccessCode(this)" class="btn btn-outline-secondary">Copy</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="goToTaskList">Go to Task List</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


<script>
    let categoryCount = 0;

    document.addEventListener('DOMContentLoaded', () => {
        addCategory();

        document.getElementById('addCategory').addEventListener('click', () => addCategory());
        document.getElementById('taskForm').addEventListener('submit', handleFormSubmit);
    });

    function addCategory() {
        categoryCount++;
        const container = document.getElementById('categoriesContainer');

        const html = `
        <div class="card mb-3 category-card" data-category="${categoryCount}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Category ${categoryCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCategory(${categoryCount})">X</button>
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
        if (document.querySelectorAll('.category-card').length <= 1) {
            alert('At least one category is required.');
            return;
        }
        document.querySelector(`[data-category="${id}"]`).remove();
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

        const formData = new FormData(form);

        fetch('{{ route('tasks.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('generatedAccessCode').textContent = data.accessCode;
                    new bootstrap.Modal(document.getElementById('accessCodeModal')).show();
                    document.getElementById('goToTaskList').addEventListener('click', () => {
                        window.location.href = data.redirect;
                    });
                } else {
                    throw new Error(data.message || 'Error occurred');
                }
            }).catch(error => {
                alert('Error: ' + error.message);
            }).finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Task';
            });
    }

    function copyAccessCode(button) {
        const code = document.getElementById('generatedAccessCode').textContent;
        navigator.clipboard.writeText(code).then(() => {
            const original = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => button.innerHTML = original, 2000);
        }).catch(err => alert('Failed to copy: ' + err));
    }
</script>
