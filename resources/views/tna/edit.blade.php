@extends('layouts.app')

@section('content')
    <div class="container mt-2">
        <a href="{{ route('tna.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">
            ← Back to List
        </a>

        <h2 class="mb-3 text-primary"><strong>Edit TNA</strong></h2>
        {{-- Form Edit TNA --}}
        <form method="POST" action="{{ route('tna.update', $tna->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="tna">

            <div class="mb-3">
                <label for="tna_year" class="form-label">Tahun:</label>
                <input type="text" class="form-control" name="tna_year" value="{{ old('tna_year', $tna->tna_year) }}">
            </div>

            <div class="mb-3">
                <label for="tna_document" class="form-label">Input Training Plan:</label>
                <input type="file" class="form-control" name="tna_document">
                @if ($tna->tna_document)
                    <small>Dokumen saat ini: <a href="{{ asset('storage/' . $tna->tna_document) }}"
                            target="_blank">Lihat</a></small>
                @endif
            </div>

            <div class="mb-3">
                <label for="tna_min_budget" class="form-label">Minimum Investment Projection:</label>
                <input type="text" class="form-control" name="tna_min_budget"
                    value="{{ old('tna_min_budget', $tna->tna_min_budget) }}">
            </div>

            <div class="mb-3">
                <label for="tna_remarks" class="form-label">TNA Remarks:</label>
                <textarea class="form-control" name="tna_remarks" rows="2">{{ old('tna_remarks', $tna->tna_remarks) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary mb-4">💾 Update TNA</button>
        </form>

        {{-- Edit Categories --}}
        <div class="d-flex justify-content-between align-items-center mt-2">
            <h4>Edit Category</h4>
            <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#formTambahKategori" aria-expanded="false" aria-controls="formTambahKategori">
                ➕ Add Category
            </button>
        </div>

        <!-- Collapse Form -->
        <div class="collapse mt-3" id="formTambahKategori">
            <div class="card mb-3 border-success">
                <div class="card-body">
                    <form method="POST" action="{{ route('category.store') }}">
                        @csrf
                        <input type="hidden" name="tna_id" value="{{ $tna->id }}">

                        <div class="mb-2">
                            <label>Nama Kategori:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-2">
                            <label>Deskripsi:</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">💾 Simpan Kategori</button>
                    </form>
                </div>
            </div>
        </div>
        @foreach ($categories as $category)
            <form method="POST" action="{{ route('tna.update', $tna->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="category">
                <input type="hidden" name="category_id" value="{{ $category->id }}">

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>Nama Kategori:</strong> <input type="text" name="name" value="{{ $category->name }}"
                            class="form-control">
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label>Deskripsi:</label>
                            <textarea name="description" class="form-control">{{ $category->description }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">💾 Update Category</button>
                    </div>
                </div>
            </form>
        @endforeach

        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h4>Edit Training Programs</h4>
            <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#formTambahProgram" aria-expanded="false" aria-controls="formTambahProgram">
                ➕ Add Training Program
            </button>
        </div>

        <!-- Form Tambah Program Collapse -->
        <div class="collapse mt-2" id="formTambahProgram">
            <div class="card mb-3 border-primary">
                <div class="card-body">
                    {{-- FIXED: Change the action to point to the correct route --}}
                    <form method="POST" action="{{ route('category.store.training.program') }}"
                        id="addTrainingProgramForm">
                        @csrf
                        <input type="hidden" name="tna_id" value="{{ $tna->id }}">

                        <div class="mb-3">
                            <label class="form-label">Kategori: <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="" disabled selected>Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Program: <span class="text-danger">*</span></label>
                            <input type="text" name="tp_name" class="form-control" value="{{ old('tp_name') }}"
                                required placeholder="Masukkan nama program pelatihan">
                            @error('tp_name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Durasi (jam): <span class="text-danger">*</span></label>
                            <input type="time" name="tp_duration" class="form-control"
                                value="{{ old('tp_duration') }}" required>
                            <small class="text-muted">Format: HH:MM (contoh: 08:30 untuk 8 jam 30 menit)</small>
                            @error('tp_duration')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Investasi: <span class="text-danger">*</span></label>
                            <input type="number" name="tp_invest" class="form-control" value="{{ old('tp_invest') }}"
                                placeholder="Masukkan nominal investasi" min="0" step="0.01">
                            @error('tp_invest')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Program
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse"
                                data-bs-target="#formTambahProgram">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- Display any errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h6>Terjadi kesalahan:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Display success message --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @foreach ($trainingPrograms as $program)
            <form method="POST" action="{{ route('tna.update', $tna->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="program">
                <input type="hidden" name="program_id" value="{{ $program->id }}">

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>Program:</strong>
                        <input type="text" name="tp_name" value="{{ $program->tp_name }}" class="form-control">
                        <small class="text-muted">Kategori:
                            {{ $program->category->name ?? 'Tidak ada kategori' }}</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label>Durasi (jam):</label>
                            <input type="time" name="tp_duration" class="form-control"
                                value="{{ $program->tp_duration }}">
                        </div>
                        <div class="mb-2">
                            <label>Investasi:</label>
                            <input type="number" name="tp_invest" class="form-control"
                                value="{{ $program->tp_invest }}">
                        </div>
                        <button type="submit" class="btn btn-primary">💾 Update Training Program</button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>

    <script>
        // Add form validation and loading state
        document.getElementById('addTrainingProgramForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Add loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // Re-enable button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 5000);
        });
    </script>
@endsection
