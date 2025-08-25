@extends('layouts.app')


@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Review Submissions</h1>
                <p class="text-muted mb-0">{{ $task->subjectName }}</p>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View Task
                </a>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Tasks
                </a>
            </div>
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

        <!-- Submissions List -->
        @if ($task->taskFills->count() > 0)
            @foreach ($task->taskFills as $fill)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="m-0 font-weight-bold text-primary">
                                    {{ $fill->fillerName }}
                                    <small class="text-muted">({{ $fill->user->email }})</small>
                                </h6>
                                <small class="text-muted">
                                    Submitted:
                                    {{ $fill->created_at ? $fill->created_at->format('M d, Y H:i') : 'Not submitted' }}
                                </small>
                            </div>
                            <div>
                                @if ($fill->isPending())
                                    <span class="badge bg-warning">Pending Review</span>
                                @elseif($fill->isInReview())
                                    <span class="badge bg-info">In Review</span>
                                @elseif($fill->isAccepted())
                                    <span class="badge bg-success">Accepted</span>
                                @elseif($fill->isRejected())
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tasks.submit-review', $task) }}" class="review-form">
                            @csrf
                            <input type="hidden" name="task_fill_id" value="{{ $fill->id }}">

                            <!-- Categories and Documents -->
                            <div class="accordion mb-4" id="reviewAccordion{{ $fill->id }}">
                                @foreach ($task->taskCategories as $index => $category)
                                    @php
                                        $categoryDocuments = $fill->fillDocuments->where(
                                            'taskcategory_id',
                                            $category->id,
                                        );
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header"
                                            id="reviewHeading{{ $fill->id }}_{{ $category->id }}">
                                            <button
                                                class="accordion-button @if ($index > 0) collapsed @endif"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#reviewCollapse{{ $fill->id }}_{{ $category->id }}"
                                                aria-expanded="@if ($index === 0) true @else false @endif">
                                                <div class="d-flex justify-content-between w-100 me-3">
                                                    <span>
                                                        <strong>{{ $category->categoryName }}</strong>
                                                    </span>
                                                    <span class="badge bg-info">
                                                        {{ $categoryDocuments->count() }} documents
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="reviewCollapse{{ $fill->id }}_{{ $category->id }}"
                                            class="accordion-collapse collapse @if ($index === 0) show @endif"
                                            data-bs-parent="#reviewAccordion{{ $fill->id }}">
                                            <div class="accordion-body">

                                                <!-- Submitted Documents -->
                                                @if ($categoryDocuments->count() > 0)
                                                    <div class="mb-3">
                                                        <h6>Submitted Documents:</h6>
                                                        <div class="list-group">
                                                            @foreach ($categoryDocuments as $doc)
                                                                <div class="list-group-item">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-start">
                                                                        <div class="me-3">
                                                                            <strong>{{ $doc->documentName }}</strong>
                                                                            <br>
                                                                            {{ $doc->documentDesc }}
                                                                            <br>
                                                                            <small class="text-muted">
                                                                                Uploaded:
                                                                                {{ $doc->created_at->format('M d, Y H:i') }}
                                                                            </small>
                                                                        </div>
                                                                        <div class="btn-group mb-2">
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

                                                                    <div class="mt-3">
                                                                        <label class="form-label">Review Decision:</label>
                                                                        <div
                                                                            class="d-flex flex-wrap gap-2 align-items-center">
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="documents[{{ $doc->id }}][status]"
                                                                                    id="accept_{{ $doc->id }}"
                                                                                    value="approved" required>
                                                                                <label class="form-check-label"
                                                                                    for="accept_{{ $doc->id }}">Accept</label>
                                                                            </div>
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="documents[{{ $doc->id }}][status]"
                                                                                    id="reject_{{ $doc->id }}"
                                                                                    value="rejected">
                                                                                <label class="form-check-label"
                                                                                    for="reject_{{ $doc->id }}">Reject</label>
                                                                            </div>
                                                                        </div>

                                                                        <label for="comment_{{ $doc->id }}"
                                                                            class="form-label mt-2">Comment:
                                                                            :</label>
                                                                        <textarea class="form-control comment-field" name="documents[{{ $doc->id }}][comment]"
                                                                            id="comment_{{ $doc->id }}" rows="2" placeholder="Provide comment..."></textarea>
                                                                    </div>
                                                                </div>
                                                            @endforeach

                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        No documents submitted for this category.
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Action Buttons -->
                            @if ($fill->isPending() || $fill->isInReview())
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" name="action" value="reject" class="btn btn-danger">
                                        Reject
                                    </button>
                                    <button type="submit" name="action" value="accept" class="btn btn-success">
                                        Accept
                                    </button>
                                </div>
                            @elseif($fill->isAccepted())
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    This submission has been accepted.
                                    @if ($fill->comment)
                                        <br><strong>Comment:</strong> {{ $fill->comment }}
                                    @endif
                                    @if ($fill->reviewed_at)
                                        <br><small class="text-muted">Reviewed:
                                            {{ $fill->reviewed_at->format('M d, Y H:i') }}</small>
                                    @endif
                                </div>
                            @elseif($fill->isRejected())
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-times-circle me-2"></i>
                                    This submission has been rejected.
                                    @if ($fill->comment)
                                        <br><strong>Comment:</strong> {{ $fill->comment }}
                                    @endif
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endforeach
        @else
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No submissions to review</h5>
                    <p class="text-muted">Submissions will appear here once participants submit their tasks.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Approve All Modal -->
    <div class="modal fade" id="approveAllModal" tabindex="-1" aria-labelledby="approveAllModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveAllModalLabel">Approve All Submissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve all pending submissions?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This action will automatically approve all submissions that are currently pending review.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('tasks.approve-all', $task) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-double"></i> Approve All
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject All Modal -->
    <div class="modal fade" id="rejectAllModal" tabindex="-1" aria-labelledby="rejectAllModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectAllModalLabel">Reject All Submissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
        function previewDocument(fileName, downloadUrl) {
            const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
            const previewContent = document.getElementById('previewContent');
            const downloadBtn = document.getElementById('downloadBtn');

            document.getElementById('documentPreviewModalLabel').textContent = `Preview: ${fileName}`;
            downloadBtn.href = downloadUrl;

            previewContent.innerHTML =
                '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

            const extension = fileName.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                previewContent.innerHTML = `<img src="${downloadUrl}" class="img-fluid" alt="${fileName}">`;
            } else if (extension === 'pdf') {
                previewContent.innerHTML = `
            <embed src="${downloadUrl}" type="application/pdf" width="100%" height="500px">
            <p class="mt-2">If the PDF doesn't display properly, <a href="${downloadUrl}" target="_blank">click here to open in new tab</a></p>
        `;
            } else {
                previewContent.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-file fa-3x mb-3 d-block"></i>
                <h5>${fileName}</h5>
                <p>Preview not available for this file type.</p>
                <a href="${downloadUrl}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download to view
                </a>
            </div>
        `;
            }

            modal.show();
        }

        function initReviewFormHandlers() {
            document.querySelectorAll('.review-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const action = e.submitter?.value || 'accept';
                    const fillerName = form.closest('.card').querySelector('.font-weight-bold').textContent
                        .split('(')[0].trim();

                    // Validate all documents have status selected
                    const radioGroups = new Set();
                    form.querySelectorAll('input[type=radio][name*="[status]"]').forEach(radio => {
                        radioGroups.add(radio.name);
                    });

                    let allReviewed = true;
                    radioGroups.forEach(name => {
                        if (!form.querySelector(`input[name="${name}"]:checked`)) {
                            allReviewed = false;
                        }
                    });

                    // Validate all comments are filled
                    const commentFields = form.querySelectorAll('.comment-field');
                    let allCommented = true;
                    commentFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            allCommented = false;
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });

                    if (!allReviewed || !allCommented) {
                        e.preventDefault();
                        alert('Please review all documents and provide comments.');
                        return;
                    }

                    const message = action === 'accept' ?
                        `Are you sure you want to accept the submission from ${fillerName}?` :
                        `Are you sure you want to reject the submission from ${fillerName}?`;

                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        }

        // Auto-run on page load
        document.addEventListener('DOMContentLoaded', initReviewFormHandlers);
    </script>
@endsection
