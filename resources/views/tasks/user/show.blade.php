@extends('layouts.app')

@section('title', $task->subjectName)

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">{{ $task->subjectName }}</h1>
                <p class="text-muted mb-0">{{ $task->subjectDesc }}</p>
            </div>
            <div class="btn-group" role="group">
                @if ($userFill->isDraft() || $userFill->isRejected())
                    <a href="{{ route('tasks.fill', $task) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i>
                        {{ $userFill->isDraft() ? 'Continue Editing' : 'Revise Submission' }}
                    </a>
                @endif
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Tasks
                </a>
            </div>
        </div>

        @if ($userFill->isRejected() && $userFill->remarks)
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Submission Rejected</h5>
                <p class="mb-2"><strong>Remarks:</strong> {{ $userFill->remarks }}</p>
                <small class="text-muted">
                    Please make the necessary corrections and resubmit.
                    @if ($userFill->reviewed_at)
                        (Reviewed: {{ $userFill->reviewed_at->format('M d, Y H:i') }})
                    @endif
                </small>
            </div>
        @endif

        @if ($userFill->isAccepted())
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle me-2"></i>Submission Accepted</h5>
                <p class="mb-0">Your submission has been accepted. Great job!</p>
                @if ($userFill->remarks)
                    <p class="mb-2 mt-2"><strong>Feedback:</strong> {{ $userFill->remarks }}</p>
                @endif
                @if ($userFill->reviewed_at)
                    <small class="text-muted">Reviewed: {{ $userFill->reviewed_at->format('M d, Y H:i') }}</small>
                @endif
            </div>
        @endif

        <div class="row">
            <!-- Task Details -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Task Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Description:</strong>
                            </div>
                            <div class="col-md-9">
                                {{ $task->subjectDesc }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Due Date:</strong>
                            </div>
                            <div class="col-md-9">
                                @if ($task->due_date)
                                    <span class="@if ($task->due_date->isPast()) text-danger @endif">
                                        {{ $task->due_date->format('l, M d, Y \a\t H:i') }}
                                    </span>
                                    @if ($task->due_date->isPast())
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    @endif
                                @else
                                    <span class="text-muted">No due date set</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Categories:</strong>
                            </div>
                            <div class="col-md-9">
                                <span class="badge bg-info">{{ $task->taskCategories->count() }} categories</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories and Required Documents -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Required Categories & Documents</h6>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="categoriesAccordion">
                            @foreach ($task->taskCategories as $index => $category)
                                @php
                                    $userDocs = $userFill->fillDocuments->where('taskcategory_id', $category->id);
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $category->id }}">
                                        <button class="accordion-button @if ($index > 0) collapsed @endif"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $category->id }}"
                                            aria-expanded="@if ($index === 0) true @else false @endif">
                                            <div class="d-flex justify-content-between w-100 me-3">
                                                <span>
                                                    <strong>{{ $category->categoryName }}</strong>
                                                    @if ($category->categoryDesc)
                                                        <small
                                                            class="text-muted d-block">{{ $category->categoryDesc }}</small>
                                                    @endif
                                                </span>
                                                <div class="d-flex gap-2">
                                                    @if ($userDocs->count() > 0)
                                                        <span class="badge bg-success">
                                                            {{ $userDocs->count() }} submitted
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">Not submitted</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $category->id }}"
                                        class="accordion-collapse collapse @if ($index === 0) show @endif"
                                        data-bs-parent="#categoriesAccordion">
                                        <div class="accordion-body">

                                            <!-- Submitted Documents -->
                                            @if ($userDocs->count() > 0)
                                                <div class="mb-3">
                                                    <h6>Your Submitted Documents:</h6>
                                                    <div class="list-group">
                                                        @foreach ($userDocs as $doc)
                                                            <div
                                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $doc->documentName }}</strong>
                                                                </div>
                                                                <div class="btn-group" role="group">
                                                                    <a href="{{ route('tasks.download-document', $doc) }}"
                                                                        class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-download"></i> Download
                                                                    </a>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-info"
                                                                        onclick="previewDocument('{{ $doc->documentName }}', '{{ route('tasks.preview-document', $doc) }}')">
                                                                        <i class="fas fa-eye"></i> Preview
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    You haven't submitted any documents for this category yet.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Status Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">Submission Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Current Status:</strong><br>
                            @if ($userFill->isDraft())
                                <span class="badge bg-secondary fs-6">Draft</span>
                                <p class="text-muted mt-2 mb-0">Your submission is not yet completed.</p>
                            @elseif($userFill->isPending())
                                <span class="badge bg-warning fs-6">Pending Review</span>
                                <p class="text-muted mt-2 mb-0">Your submission is waiting for review.</p>
                            @elseif($userFill->isInReview())
                                <span class="badge bg-info fs-6">In Review</span>
                                <p class="text-muted mt-2 mb-0">Your submission is currently being reviewed.</p>
                            @elseif($userFill->isAccepted())
                                <span class="badge bg-success fs-6">Accepted</span>
                                <p class="text-muted mt-2 mb-0">Your submission has been accepted!</p>
                            @elseif($userFill->isRejected())
                                <span class="badge bg-danger fs-6">Rejected</span>
                                <p class="text-muted mt-2 mb-0">Your submission needs revision.</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <strong>Submitted At:</strong><br>
                            @if ($userFill->created_at)
                                {{ $userFill->created_at->format('M d, Y H:i') }}
                            @else
                                <span class="text-muted">Not submitted yet</span>
                            @endif
                        </div>

                        @if ($userFill->reviewed_at)
                            <div class="mb-3">
                                <strong>Reviewed At:</strong><br>
                                {{ $userFill->reviewed_at->format('M d, Y H:i') }}
                            </div>
                        @endif

                        <div class="mb-3">
                            <strong>Documents Submitted:</strong><br>
                            <span class="badge bg-info">{{ $userFill->fillDocuments->count() }} files</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Actions</h6>
                    </div>
                    <div class="card-body">
                        @if ($userFill->isDraft())
                            <a href="{{ route('tasks.fill', $task) }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-edit"></i> Continue Editing
                            </a>
                            <p class="text-muted small mb-0">Complete your submission to send it for review.</p>
                        @elseif($userFill->isRejected())
                            <a href="{{ route('tasks.fill', $task) }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-edit"></i> Revise Submission
                            </a>
                            <p class="text-muted small mb-0">Make the necessary corrections and resubmit.</p>
                        @elseif($userFill->isPending())
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Your submission is waiting for review. You'll be notified once it's been reviewed.
                            </div>
                        @elseif($userFill->isAccepted())
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Congratulations! Your submission has been accepted.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Progress Summary -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">Progress Summary</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $totalCategories = $task->taskCategories->count();
                            $completedCategories = 0;
                            foreach ($task->taskCategories as $category) {
                                if ($userFill->fillDocuments->where('taskcategory_id', $category->id)->count() > 0) {
                                    $completedCategories++;
                                }
                            }
                            $progressPercentage =
                                $totalCategories > 0 ? ($completedCategories / $totalCategories) * 100 : 0;
                        @endphp

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Categories Completed</span>
                                <span>{{ $completedCategories }}/{{ $totalCategories }}</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $progressPercentage }}%" aria-valuenow="{{ $progressPercentage }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    {{ round($progressPercentage) }}%
                                </div>
                            </div>
                        </div>

                        <div class="small">
                            @foreach ($task->taskCategories as $category)
                                @php
                                    $hasDocuments =
                                        $userFill->fillDocuments->where('taskcategory_id', $category->id)->count() > 0;
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>{{ $category->category_name }}</span>
                                    @if ($hasDocuments)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-circle text-muted"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentPreviewModalLabel">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="previewContent">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="downloadBtn" href="#" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewDocument(fileName, fileUrl) {
            const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
            const previewContent = document.getElementById('previewContent');
            const downloadBtn = document.getElementById('downloadBtn');
            const modalTitle = document.getElementById('documentPreviewModalLabel');

            modalTitle.textContent = `Preview: ${fileName}`;
            downloadBtn.href = fileUrl;

            // File extension
            const ext = fileName.split('.').pop().toLowerCase();

            // Loading spinner
            previewContent.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

            // Delay a bit to show spinner before embedding
            setTimeout(() => {
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    previewContent.innerHTML = `<img src="${fileUrl}" class="img-fluid" alt="${fileName}">`;
                } else if (ext === 'pdf') {
                    previewContent.innerHTML = `
                <embed src="${fileUrl}" type="application/pdf" width="100%" height="500px" />
                <p class="mt-2 text-center">Jika tidak tampil, <a href="${fileUrl}" target="_blank">klik di sini</a></p>
            `;
                } else {
                    previewContent.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-file fa-3x mb-3"></i>
                    <h5>${fileName}</h5>
                    <p>Preview tidak tersedia. Silakan download file untuk melihatnya.</p>
                    <a href="${fileUrl}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            `;
                }

                modal.show();
            }, 300); // optional delay
        }
    </script>
@endsection
