@extends('layouts.app')

@push('styles')
    <style>
        .category-row {
            background-color: #e9f7ef;
            font-weight: 600;
            vertical-align: middle;
        }

        .no-program-row td {
            background-color: #f8f9fa;
            font-style: italic;
            color: #6c757d;
            text-align: center;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <!-- Back & Title -->
        <a href="{{ route('tna.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">← Back to List</a>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2>Detail TNA: {{ $tna->tna_year }}</h2>
            @role('manager')
                <a href="{{ route('tna.edit', $tna->id) }}" class="btn btn-warning">Edit TNA</a>
            @endrole
        </div>

        <!-- TNA Info Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 TNA Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Training Plan File:</strong>
                    <a href="{{ asset('storage/' . $tna->tna_document) }}" target="_blank"
                        class="btn btn-sm btn-outline-info ms-2">📄 Lihat Dokumen</a>
                </p>
                <p><strong>Minimum Investment Projection:</strong>
                    <span class="text-success">Rp {{ number_format($tna->tna_min_budget, 2, ',', '.') }}</span>
                </p>
            </div>
        </div>

        <!-- Filter + Table Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📚 Kategori & Training Program</h5>
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" placeholder="Cari program..." class="form-control form-control-sm"
                        value="{{ request('search') }}">

                    <select name="category" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">🔎 Semua Kategori</option>
                        @foreach ($allCategories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                </form>

            </div>
            <div class="card-body table-responsive">
                @if ($categories->isNotEmpty())
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Training Program</th>
                                {{-- <th>Durasi</th> --}}
                                <th>Investation (Rp)</th>
                                <th>Realization (Rp)</th>
                                <th>Remaining Budget (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                @php
                                    $programs = $trainingPrograms->where('category_id', $category->id);
                                    $rowspan = max($programs->count(), 1);
                                    $rendered = false;
                                @endphp

                                @foreach ($programs as $program)
                                    <tr>
                                        @if (!$rendered)
                                            <td rowspan="{{ $rowspan }}">
                                                {{ $category->name }}
                                                <br><small class="text-muted">{{ $category->description }}</small>
                                            </td>
                                            @php $rendered = true; @endphp
                                        @endif
                                        <td>{{ $program->tp_name }}</td>
                                        {{-- <td class="text-center">{{ $program->tp_duration }}</td> --}}
                                        <td class="text-end">Rp {{ number_format($program->tp_invest, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($program->tp_realization, 2, ',', '.') }}
                                        </td>
                                        <td>
                                            Rp
                                            {{ number_format($program->tp_invest - $program->tp_realization, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada data ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Belum ada kategori yang terdaftar dalam TNA ini.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
