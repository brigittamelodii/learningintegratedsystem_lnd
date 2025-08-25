@extends('layouts.app')

<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        ol.custom-list {
            list-style: none;
            counter-reset: item;
            padding-left: 1em;
            /* sesuai permintaan, tetap 1em */
            margin: 0;
        }

        ol.custom-list li {
            counter-increment: item;
            position: relative;
            padding-left: 1.2em;
            /* agak besar, beri ruang angka */
            text-indent: -1.2em;
            /* geser baris pertama agar angka + teks sejajar */
            margin-bottom: 0.5em;
            line-height: 1.5;
        }

        ol.custom-list li::before {
            content: counter(item) ".";
            font-weight: bold;
            position: absolute;
            left: 0;
            width: 1.4em;
            /* cocok untuk angka 1-9 */
        }

        .danger {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">{{ $task->subjectName }}</h1>
                <p class="text-muted mb-0">{{ $task->subjectDesc }}</p>
            </div>
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tasks
            </a>
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

        <div class="row">
            <div class="col-lg-8">
                <form id="taskFillForm" method="POST" action="{{ route('tasks.submit-fill', $task) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="fillerName" class="form-label">Your Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fillerName" name="fillerName"
                                    value="{{ old('fillerName', $userFill->fillerName) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Document Categories</h6>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="categoriesAccordion">
                                @foreach ($task->taskCategories as $index => $category)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $category->id }}">
                                            <button
                                                class="accordion-button @if ($index > 0) collapsed @endif"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse{{ $category->id }}"
                                                aria-expanded="@if ($index === 0) true @else false @endif"
                                                aria-controls="collapse{{ $category->id }}">
                                                <div class="d-flex justify-content-between w-100 me-3">
                                                    <span>
                                                        <strong>{{ $category->categoryName }}</strong>
                                                        @if ($category->categoryDesc)
                                                            <small class="text-muted d-block">
                                                                <strong>Description:</strong>
                                                                {{ $category->categoryDesc ? $category->categoryDesc : 'No Remarks' }}
                                                            </small>
                                                        @endif
                                                    </span>
                                                    <span class="badge bg-info" id="fileCount{{ $category->id }}">
                                                        {{ $category->documents->count() }} files
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $category->id }}"
                                            class="accordion-collapse collapse @if ($index === 0) show @endif"
                                            aria-labelledby="heading{{ $category->id }}"
                                            data-bs-parent="#categoriesAccordion">
                                            <div class="accordion-body">
                                                <!-- Existing uploaded files -->
                                                @php
                                                    $existingFiles = $userFill->fillDocuments->where(
                                                        'taskcategory_id',
                                                        $category->id,
                                                    );
                                                @endphp

                                                @if ($existingFiles->count() > 0)
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-3">Uploaded Files:</h6>
                                                        <div class="list-group">
                                                            @foreach ($existingFiles as $file)
                                                                <div class="list-group-item">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-start">
                                                                        <div class="me-3 flex-grow-1">
                                                                            <p class="mb-1 fw-semibold">
                                                                                {{ $file->documentName }}</p>
                                                                            <small class="text-muted d-block mb-1">
                                                                                Uploaded:
                                                                                {{ $file->created_at->format('M d, Y H:i') }}
                                                                            </small>

                                                                            @if ($file->documentDesc)
                                                                                <small class="text-muted d-block mb-1">
                                                                                    <strong>Description:</strong>
                                                                                    {{ $file->documentDesc }}
                                                                                </small>
                                                                            @endif
                                                                        </div>
                                                                        <div class="btn-group btn-group-sm" role="group">
                                                                            <a href="{{ route('tasks.download-document', $file) }}"
                                                                                class="btn btn-outline-primary">
                                                                                Download
                                                                            </a>
                                                                            <button type="button"
                                                                                class="btn btn-outline-danger"
                                                                                onclick="removeFile({{ $file->id }})">
                                                                                Delete
                                                                            </button>
                                                                        </div>
                                                                    </div>

                                                                    @if ($file->comment)
                                                                        <div class="mt-3 p-2 rounded bg-light border">
                                                                            <small
                                                                                class="text-muted d-block mb-1"><strong>Comment:</strong></small>
                                                                            <div class="text-muted">{{ $file->comment }}
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- File upload section -->
                                                <div class="mb-3">
                                                    <label class="form-label">Upload Documents:</label>
                                                    <input type="file" class="form-control"
                                                        id="files{{ $category->id }}"
                                                        name="documents[{{ $category->id }}][]" multiple
                                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.rar"
                                                        onchange="previewSelectedFiles(this, {{ $category->id }})">
                                                    <div class="form-text">
                                                        Optional: Leave empty to keep existing files, or upload new files to
                                                        replace existing ones.
                                                    </div>
                                                    <div class="mt-2" id="previewFiles{{ $category->id }}">
                                                        <!-- Preview file names will appear here -->
                                                    </div>

                                                    <!-- Document Description - Always Required -->
                                                    <div class="mt-3">
                                                        <label for="document_desc_{{ $category->id }}" class="form-label">
                                                            Document Description <span class="text-danger">*</span>
                                                        </label>
                                                        <textarea name="document_desc[{{ $category->id }}]" id="document_desc_{{ $category->id }}"
                                                            class="form-control @error('document_desc.' . $category->id) is-invalid @enderror" rows="3"
                                                            placeholder="Please provide a description for this category..." required>{{ old('document_desc.' . $category->id, $existingFiles->first()->documentDesc ?? '') }}</textarea>
                                                        @error('document_desc.' . $category->id)
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                        <div class="form-text">
                                                            This description is required and will be updated each time you
                                                            submit.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                            </div>
                        </div>
                    </div>



                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Submit Task
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">Task Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Due Date:</strong><br>
                            @if ($task->due_date)
                                <span class="@if ($task->due_date->isPast()) text-danger @endif">
                                    {{ $task->due_date->format('l, M d, Y \a\t H:i') }}
                                </span>
                            @else
                                <span class="text-muted">No due date set</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <strong>Categories:</strong><br>
                            <span class="badge bg-info">{{ $task->taskCategories->count() }} categories</span>
                        </div>

                        <div class="mb-3">
                            <strong>Current Status:</strong><br>
                            @if ($userFill->isDraft())
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($userFill->isRejected())
                                <span class="badge bg-danger">Rejected - Needs Revision</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Instructions</h6>
                    </div>
                    <div class="card-body">
                        <ol class="custom-list">
                            <li>Isi nama Anda dan unggah dokumen yang dibutuhkan untuk setiap kategori.</li>
                            <li>Anda dapat mengunggah beberapa file per kategori. Ukuran maksimal file adalah 10MB.</li>
                            <li>Progres Anda akan otomatis disimpan saat mengerjakan.</li>
                            <li>Tinjau semua kategori sebelum mengirimkan.</li>
                            <li><span class="danger">Jika Anda menerima revisi, pastikan file yang pertama kali diunggah
                                    SUDAH DIHAPUS terlebih dahulu.</span></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewSelectedFiles(input, categoryId) {
            const previewContainer = document.getElementById('previewFiles' + categoryId);
            previewContainer.innerHTML = '';

            if (input.files.length > 0) {
                const ul = document.createElement('ul');
                ul.classList.add('list-group');

                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    const li = document.createElement('li');
                    li.classList.add('list-group-item');
                    li.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    ul.appendChild(li);
                }

                previewContainer.appendChild(ul);
            }
        }

        function handleFileSelect(categoryId, input) {
            const container = document.getElementById(`selectedFiles${categoryId}`);
            const badge = document.getElementById(`fileCount${categoryId}`);

            // Clear previous preview
            container.innerHTML = '';

            if (input.files.length > 0) {
                const fileList = document.createElement('div');
                fileList.className = 'list-group';

                Array.from(input.files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    fileItem.innerHTML = `
                <div>
                    <i class="fas fa-file me-2"></i>
                    <strong>${file.name}</strong>
                    <small class="text-muted d-block">${formatFileSize(file.size)}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSelectedFile(${categoryId}, ${index}, this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
                    fileList.appendChild(fileItem);
                });

                container.appendChild(fileList);

                // Update badge count (existing + new files)
                const existingCount = parseInt(badge.textContent.split(' ')[0]) || 0;
                badge.textContent = `${existingCount + input.files.length} files`;
            }
        }

        function removeSelectedFile(categoryId, fileIndex, button) {
            const input = document.getElementById(`files${categoryId}`);
            const dt = new DataTransfer();

            // Rebuild FileList without the removed file
            Array.from(input.files).forEach((file, index) => {
                if (index !== fileIndex) {
                    dt.items.add(file);
                }
            });

            input.files = dt.files;

            // Remove the file item from preview
            button.closest('.list-group-item').remove();

            // Update badge count
            const badge = document.getElementById(`fileCount${categoryId}`);
            const existingCount = parseInt(badge.textContent.split(' ')[0]) || 0;
            badge.textContent = `${existingCount + input.files.length} files`;
        }

        function removeFile(fileId) {
            if (confirm('Are you sure you want to remove this file?')) {
                fetch(`/tasks/document/${fileId}/remove`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to remove file');
                        }
                    });
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form submission handler
        // Form submission handler with validation
        document.getElementById('taskFillForm').addEventListener('submit', function(e) {
            const submitBtn = e.target.querySelector('button[type="submit"]');
            let isValid = true;
            let errorMessages = [];

            // Validate required descriptions
            const descriptionTextareas = document.querySelectorAll('textarea[name^="document_desc"]');
            descriptionTextareas.forEach(function(textarea) {
                const value = textarea.value.trim();
                const categoryMatch = textarea.name.match(/\[(\d+)\]/);
                const categoryName = categoryMatch ? document.querySelector(
                    `#collapse${categoryMatch[1]} .accordion-button strong`).textContent : 'Unknown';

                // Remove any existing error styling
                textarea.classList.remove('is-invalid');
                const existingError = textarea.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }

                if (!value) {
                    isValid = false;
                    textarea.classList.add('is-invalid');

                    // Add error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = `Description is required for ${categoryName}`;
                    textarea.parentNode.appendChild(errorDiv);

                    errorMessages.push(`Description is required for category: ${categoryName}`);
                }
            });

            // Check if at least one category has either existing files or new files or description
            const categories = document.querySelectorAll('.accordion-item');
            let hasContent = false;

            categories.forEach(function(category) {
                const categoryId = category.querySelector('.accordion-collapse').id.replace('collapse', '');
                const existingFiles = category.querySelectorAll('.list-group-item').length;
                const newFiles = document.getElementById(`files${categoryId}`).files.length;
                const description = document.querySelector(`textarea[name="document_desc[${categoryId}]"]`)
                    .value.trim();

                if (existingFiles > 0 || newFiles > 0 || description) {
                    hasContent = true;
                }
            });

            if (!hasContent) {
                isValid = false;
                errorMessages.push('Please provide at least one document or description in any category.');
            }

            if (!isValid) {
                e.preventDefault();

                // Show error alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors:</h6>
            <ul class="mb-0">
                ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                // Insert alert at the top of the form
                const form = document.getElementById('taskFillForm');
                form.insertBefore(alertDiv, form.firstChild);

                // Scroll to top to show error
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

                return false;
            }

            // If validation passes, disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            // Re-enable after a delay if submission fails
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Task';
                }
            }, 10000);
        });

        // Drag and drop functionality
        document.querySelectorAll('.file-upload-area').forEach(area => {
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '#f8f9fa';
                this.style.borderColor = '#007bff';
            });

            area.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
                this.style.borderColor = '#dee2e6';
            });

            area.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
                this.style.borderColor = '#dee2e6';

                const categoryId = this.getAttribute('onclick').match(/\d+/)[0];
                const input = document.getElementById(`files${categoryId}`);

                input.files = e.dataTransfer.files;
                handleFileSelect(categoryId, input);
            });
        });
    </script>
@endsection
