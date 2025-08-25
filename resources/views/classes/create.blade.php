@extends('layouts.app')

@section('content')
    <div class="container-fluid my-1">
        <a href="{{ route('classes.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow rounded-4 mx-3" style="height: auto;">
            <div class="card-header bg-primary text-white rounded-top-4 py-2">
                <h5 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Create New Class</h5>
            </div>
            <div class="card-body px-4 pt-3 pb-2">
                <form method="POST" action="{{ route('classes.store') }}" enctype="multipart/form-data" id="classForm">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="program_id" class="form-label">Program</label>
                            <select name="program_id" id="program_id" class="form-select form-select-sm select2-dropdown"
                                required>
                                <option value="">-- Pilih Program --</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}"
                                        {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->program_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="class_name" class="form-label">Class Name (with Batch)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="class_name" id="class_name"
                                    class="form-control @error('class_name') is-invalid @enderror"
                                    value="{{ old('class_name') }}" required readonly>
                                <button type="button" class="btn btn-outline-secondary" id="editClassNameBtn"
                                    title="Edit class name">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                            <small class="text-muted">Format: Program Name - Batch [Number]</small>
                            @error('class_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label for="batch_number" class="form-label">Batch Number</label>
                            <input type="number" id="batch_number" min="1" value="1"
                                class="form-control form-control-sm" placeholder="1">
                            <small class="text-muted" id="batch_info">Auto-incremented based on program</small>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control form-control-sm @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control form-control-sm @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="class_loc" class="form-label">Location</label>
                            <input type="text" name="class_loc" id="class_loc"
                                class="form-control form-control-sm @error('class_loc') is-invalid @enderror"
                                placeholder="Link Zoom/Address" value="{{ old('class_loc') }}">
                            @error('class_loc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="class_doc" class="form-label">Class Document</label>
                        <input type="file" name="class_doc" id="class_doc"
                            class="form-control form-control-sm @error('class_doc') is-invalid @enderror"
                            accept=".pdf,.doc,.docx">
                        @error('class_doc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: PDF, DOC, DOCX. Max size: 2MB</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-sm px-4" id="submitBtn">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- jQuery & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for Program dropdown
            $('#program_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '🔍 Cari nama program...',
                allowClear: true,
                dropdownParent: $('body'),
                minimumInputLength: 0,
                language: {
                    noResults: function() {
                        return "Program tidak ditemukan";
                    },
                    searching: function() {
                        return "Mencari program...";
                    },
                    inputTooShort: function() {
                        return "Ketik untuk mencari program";
                    },
                    placeholder: function() {
                        return "Pilih program...";
                    }
                },
                templateResult: function(data) {
                    if (!data.id) {
                        return data.text;
                    }

                    // Custom formatting for program dropdown
                    return $(`
                        <div class="select2-result-item">
                            <div class="program-name-result">
                                <i class="bi bi-folder me-2 text-primary"></i>${data.text}
                            </div>
                        </div>
                    `);
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });

            // Function to generate class name
            function generateClassName() {
                const programSelect = $('#program_id');
                const selectedProgram = programSelect.find('option:selected').text();
                const batchNumber = $('#batch_number').val() || '1';

                if (selectedProgram && selectedProgram !== '-- Pilih Program --') {
                    return `${selectedProgram} - Batch ${batchNumber}`;
                }
                return '';
            }

            // Auto-generate class name when program or batch changes
            function updateClassName() {
                const generatedName = generateClassName();
                if (generatedName) {
                    $('#class_name').val(generatedName);
                }
            }

            // Show batch info message
            function showBatchInfo(message) {
                const batchInfo = $('#batch_info');
                const originalText = batchInfo.text();

                batchInfo.text(message);
                batchInfo.removeClass('text-muted').addClass('text-info');

                // Revert to original text after 3 seconds
                setTimeout(function() {
                    batchInfo.text(originalText);
                    batchInfo.removeClass('text-info').addClass('text-muted');
                }, 3000);
            }

            // Event handlers
            $('#program_id').on('select2:select', function(e) {
                var programId = e.params.data.id;

                // Get next batch number for selected program
                $.ajax({
                    url: '{{ route('classes.get-next-batch') }}',
                    type: 'GET',
                    data: {
                        program_id: programId
                    },
                    success: function(response) {
                        $('#batch_number').val(response.next_batch);
                        updateClassName();

                        // Show info about auto-increment
                        if (response.next_batch > 1) {
                            showBatchInfo(
                                `Auto-incremented to Batch ${response.next_batch} (previous: Batch ${response.next_batch - 1})`
                                );
                        }
                    },
                    error: function() {
                        $('#batch_number').val(1);
                        updateClassName();
                    }
                });
            });

            $('#program_id').on('select2:clear', function(e) {
                $('#class_name').val('');
            });

            $('#batch_number').on('input', function() {
                updateClassName();
            });

            // Toggle edit mode for class name
            $('#editClassNameBtn').on('click', function() {
                const classNameInput = $('#class_name');
                const isReadonly = classNameInput.prop('readonly');

                if (isReadonly) {
                    classNameInput.prop('readonly', false);
                    classNameInput.focus();
                    $(this).html('<i class="bi bi-check"></i>');
                    $(this).removeClass('btn-outline-secondary').addClass('btn-outline-success');
                } else {
                    classNameInput.prop('readonly', true);
                    $(this).html('<i class="bi bi-pencil"></i>');
                    $(this).removeClass('btn-outline-success').addClass('btn-outline-secondary');
                }
            });

            // Validate class name format when edited manually
            $('#class_name').on('blur', function() {
                const className = $(this).val();
                if (className && !className.toLowerCase().includes('batch')) {
                    alert(
                        'Class name should include "Batch" followed by a number (e.g., "Program Name - Batch 1")');
                }
            });

            // Form validation
            $('#classForm').on('submit', function(e) {
                const submitBtn = $('#submitBtn');
                const originalText = submitBtn.html();

                // Basic client-side validation
                let isValid = true;
                const requiredFields = ['program_id', 'class_name', 'start_date', 'end_date'];

                requiredFields.forEach(function(fieldName) {
                    const field = $(`[name="${fieldName}"]`);
                    if (!field.val()) {
                        field.addClass('is-invalid');
                        isValid = false;
                    } else {
                        field.removeClass('is-invalid');
                    }
                });

                // Validate class name contains batch
                const className = $('#class_name').val();
                if (className && !className.toLowerCase().includes('batch')) {
                    $('#class_name').addClass('is-invalid');
                    alert('Class name must include "Batch" followed by a number');
                    isValid = false;
                }

                // Date validation
                const startDate = new Date($('#start_date').val());
                const endDate = new Date($('#end_date').val());

                if (startDate && endDate && startDate > endDate) {
                    $('#end_date').addClass('is-invalid');
                    alert('End date must be after start date');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                // Disable button and show loading
                submitBtn.prop('disabled', true);
                submitBtn.html(
                    '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...'
                );

                // Re-enable after 10 seconds to prevent permanent disable
                setTimeout(function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }, 10000);
            });

            // Real-time validation feedback
            $('input[required], select[required]').on('blur', function() {
                if ($(this).val()) {
                    $(this).removeClass('is-invalid');
                }
            });

            // Initialize with first batch if program is pre-selected
            if ($('#program_id').val()) {
                updateClassName();
            }
        });
    </script>

    <style>
        /* Custom Select2 styling for small form controls */
        .select2-container {
            z-index: 9999 !important;
            display: block !important;
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: calc(1.25rem + 0.5rem + 2px) !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            color: #212529 !important;
            line-height: 1.5 !important;
            padding-left: 0.5rem !important;
            padding-right: 2rem !important;
            height: auto !important;
            margin: 0 !important;
            display: block !important;
            width: 100% !important;
            font-size: 0.875rem !important;
        }

        .select2-container .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
            font-size: 0.875rem !important;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(1.25rem + 0.5rem + 2px) !important;
            right: 0.5rem !important;
            top: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container .select2-selection--single .select2-selection__arrow b {
            border-color: #6c757d transparent transparent transparent !important;
            border-width: 4px 4px 0 4px !important;
        }

        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .select2-results__option {
            padding: 0.375rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        /* Custom result formatting */
        .select2-result-item {
            line-height: 1.3;
        }

        .program-name-result {
            color: #212529;
            font-size: 0.875rem;
        }

        /* Focus states */
        .select2-container--focus .select2-selection--single,
        .select2-container--open .select2-selection--single {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            outline: 0 !important;
        }

        .select2-container {
            vertical-align: top !important;
        }

        .form-label+.select2-container {
            margin-top: 0 !important;
        }

        .col-md-6 .select2-container,
        .col-md-12 .select2-container {
            display: block !important;
            width: 100% !important;
        }

        .select2-selection__clear {
            color: #6c757d !important;
            font-size: 1em !important;
            margin-right: 0.5rem !important;
        }

        .select2-selection__clear:hover {
            color: #dc3545 !important;
        }

        /* Custom styling for readonly class name input */
        #class_name[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        #class_name:not([readonly]) {
            background-color: #fff;
        }

        .input-group .btn {
            border-left: none;
        }

        @media (max-width: 768px) {
            .select2-dropdown {
                font-size: 16px !important;
            }
        }
    </style>
@endsection
