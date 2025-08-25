@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="container my-1">
        <a href="{{ route('program.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0">Create New Program</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('program.store') }}" method="POST" enctype="multipart/form-data" id="programForm">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tp_id" class="form-label">Category & Training Program</label>
                            <select name="tp_id" id="tp_id" class="form-select select2-dropdown" required>
                                <option value="">-- Choose Category & Program --</option>
                                @foreach ($training_programs as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->category->name ?? 'No Category' }} -
                                        {{ $tp->tp_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">PIC</label>
                            <select name="user_id" id="user_id" class="form-select select2-dropdown" required>
                                <option value="">-- Choose PIC --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ ucwords(str_replace('.', ' ', Str::before($user->email, '@'))) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program Name</label>
                            <input type="text" name="program_name" class="form-control"
                                placeholder="e.g. Digital Leadership Training" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="program_loc" class="form-control"
                                placeholder="e.g. Office, Virtual, etc." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (HH:MM)</label>
                            <input type="time" name="program_duration" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">BI Code</label>
                            <select name="bi_code" id="bi_code" class="form-select select2-dropdown" required>
                                <option value="">-- Choose BI Code --</option>
                                @foreach ($category_bi as $bi)
                                    <option value="{{ $bi->bi_code }}">({{ $bi->bi_category_type }}) {{ $bi->bi_desc }} -
                                        {{ $bi->bi_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Initiator</label>
                            <input type="text" name="program_unit_int" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Facilitator Name</label>
                            <input type="text" name="facilitator_name" class="form-control" placeholder="e.g. John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program Type</label>
                            <select name="program_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="Training Activity">Training Activity</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Activity Type</label>
                            <select name="program_act_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="Internal">Internal</option>
                                <option value="External">External</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Upload Program Approval Document</label>
                            <input type="file" name="program_document"
                                class="form-control @error('program_document') is-invalid @enderror"
                                accept=".pdf,.doc,.docx">
                            @error('program_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional. Accepted formats: PDF, DOC, DOCX. Max size:
                                2MB</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="program_remarks" class="form-control" rows="3" placeholder="Additional information..."></textarea>
                        </div>
                    </div>
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i data-feather="save"></i> Save Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for all searchable dropdowns
            $('.select2-dropdown').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).find('option:first').text();
                },
                allowClear: true,
                dropdownParent: $('body'),
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            });

            // Specific configuration for Category & Training Program
            $('#tp_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '🔍 Cari kategori & program...',
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
                    }
                },
                templateResult: function(data) {
                    if (!data.id) {
                        return data.text;
                    }

                    // Custom formatting for dropdown items
                    var text = data.text;
                    var parts = text.split(' - ');
                    if (parts.length === 2) {
                        return $(`
                            <div class="select2-result-item">
                                <div class="category-name">${parts[0]}</div>
                                <div class="program-name">${parts[1]}</div>
                            </div>
                        `);
                    }
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });

            // Specific configuration for PIC
            $('#user_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '🔍 Cari nama PIC...',
                allowClear: true,
                dropdownParent: $('body'),
                language: {
                    noResults: function() {
                        return "PIC tidak ditemukan";
                    },
                    searching: function() {
                        return "Mencari PIC...";
                    }
                }
            });

            // Specific configuration for BI Code
            $('#bi_code').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '🔍 Cari BI Code...',
                allowClear: true,
                dropdownParent: $('body'),
                language: {
                    noResults: function() {
                        return "BI Code tidak ditemukan";
                    },
                    searching: function() {
                        return "Mencari BI Code...";
                    }
                },
                templateResult: function(data) {
                    if (!data.id) {
                        return data.text;
                    }

                    // Extract BI Code info for better display
                    var text = data.text;
                    var match = text.match(/\(([^)]+)\)\s*(.+?)\s*-\s*(.+)$/);
                    if (match) {
                        return $(`
                            <div class="select2-result-item">
                                <div class="bi-code">${match[3]}</div>
                                <div class="bi-type">${match[1]}</div>
                                <div class="bi-desc">${match[2]}</div>
                            </div>
                        `);
                    }
                    return data.text;
                }
            });

            // Event handlers
            $('.select2-dropdown').on('select2:select', function(e) {
                var data = e.params.data;
                console.log('Selected:', data.text);
            });

            $('.select2-dropdown').on('select2:clear', function(e) {
                console.log('Cleared selection');
            });

            // Initialize Feather icons
            feather.replace();

            // Form submission handling
            $('#programForm').on('submit', function(e) {
                const submitBtn = $('#submitBtn');
                const originalText = submitBtn.html();

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
        });
    </script>

    <style>
        /* Custom Select2 styling */
        .select2-container {
            z-index: 9999 !important;
            display: block !important;
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            color: #212529 !important;
            line-height: 1.5 !important;
            padding-left: 0.75rem !important;
            padding-right: 2.25rem !important;
            height: auto !important;
            margin: 0 !important;
            display: block !important;
            width: 100% !important;
            font-size: 1rem !important;
        }

        .select2-container .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
            font-size: 1rem !important;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem + 2px) !important;
            right: 0.75rem !important;
            top: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container .select2-selection--single .select2-selection__arrow b {
            border-color: #6c757d transparent transparent transparent !important;
        }

        /* Fix container alignment */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 0.75rem + 2px) !important;
            font-size: 1rem !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
        }

        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .select2-results__option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }

        .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem !important;
        }

        /* Custom result formatting */
        .select2-result-item {
            line-height: 1.3;
        }

        .category-name {
            font-weight: 600;
            color: #0d6efd;
            font-size: 0.8em;
        }

        .program-name {
            color: #212529;
            margin-top: 2px;
        }

        .bi-code {
            font-weight: 600;
            color: #198754;
            font-size: 0.9em;
        }

        .bi-type {
            color: #6c757d;
            font-size: 0.75em;
            margin-top: 1px;
        }

        .bi-desc {
            color: #212529;
            font-size: 0.8em;
            margin-top: 1px;
        }

        /* Focus states */
        .select2-container--focus .select2-selection--single,
        .select2-container--open .select2-selection--single {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            outline: 0 !important;
        }

        /* Ensure proper vertical alignment with other form controls */
        .select2-container {
            vertical-align: top !important;
        }

        .form-label+.select2-container {
            margin-top: 0 !important;
        }

        /* Additional alignment fixes */
        .col-md-6 .select2-container,
        .col-md-12 .select2-container {
            display: block !important;
            width: 100% !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .select2-dropdown {
                font-size: 16px !important;
                /* Prevents zoom on iOS */
            }

            .select2-container .select2-selection--single {
                height: calc(1.5em + 1rem + 2px) !important;
                /* Slightly larger on mobile */
            }
        }

        /* Loading state */
        .select2-results__option.loading-results {
            color: #6c757d;
            text-align: center;
            font-style: italic;
        }

        /* Clear button styling */
        .select2-selection__clear {
            color: #6c757d !important;
            font-size: 1.2em !important;
            margin-right: 0.5rem !important;
        }

        .select2-selection__clear:hover {
            color: #dc3545 !important;
        }
    </style>
@endsection
