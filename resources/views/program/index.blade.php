@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-4" style="margin-top: 10px">Program List</h2>

        {{-- Header Bar: Search & Action Buttons --}}
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                {{-- Form Search --}}
                <form method="GET" action="{{ route('program.index') }}" class="d-flex">
                    <input type="text" name="query" class="form-control me-2" placeholder="Cari program..."
                        value="{{ request('query') }}">
                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </form>
            </div>

            @hasanyrole('manager|superadmin')
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="{{ route('program.create') }}" class="btn btn-success">
                            Create Program
                        </a>
                    </div>
                </div>
            @endhasanyrole
        </div>

        {{-- Filter Section --}}
        <form method="GET" action="{{ route('program.index') }}">
            <div class="row g-2 mb-4">
                <div class="col-md-4">
                    <select name="tna_year" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter TNA Year --</option>
                        @foreach ($tna_years as $year)
                            <option value="{{ $year }}" {{ request('tna_year') == $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="tp_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter Training Program --</option>
                        @foreach ($training_programs as $tp)
                            <option value="{{ $tp->id }}" {{ request('tp_id') == $tp->id ? 'selected' : '' }}>
                                {{ $tp->tp_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="pic_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter PIC --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('pic_id') == $user->id ? 'selected' : '' }}>
                                {{ ucwords(str_replace('.', ' ', Str::before($user->email, '@'))) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 6%;">Year</th>
                        <th style="width: 17%;">Program</th>
                        <th style ="width: 12%;">Facilitator</th>
                        <th style="width: 10%;">Location</th>
                        <th style="width: 10%;">Investment</th>
                        <th style="width: 12%;">Realization</th>
                        <th style="width: 10%;">PIC</th>
                        <th style="width: 18%;">Action</th> {{-- Diperlebar --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                        <tr>
                            <td>{{ $program->training_program->category->tna->tna_year }}</td>
                            <td>{{ $program->program_name }}</td>
                            <td>{{ $program->facilitator_name ?? '-' }}</td>
                            <td>{{ $program->program_loc }}</td>
                            <td>
                                Rp{{ number_format($program->category_budget->sum('amount_fee'), 0, ',', '.') }}
                            </td>
                            <td>
                                @php
                                    $totalBudget = $program->category_budget->sum('amount_fee');
                                    $realization = $program->program_realization;
                                @endphp

                                @if ($realization && $totalBudget > 0)
                                    @if ($realization > $totalBudget)
                                        <span class="text-danger">
                                            Rp{{ number_format($realization, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-success">
                                            Rp{{ number_format($realization, 0, ',', '.') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $program->user ? ucwords(str_replace('.', ' ', Str::before($program->user->email, '@'))) : '-' }}
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    @hasanyrole('manager|superadmin')
                                        <a href="{{ route('program.edit', $program->id) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                    @endhasanyrole
                                    <a href="{{ route('program.view', $program->id) }}"
                                        class="btn btn-sm btn-primary text-white">View</a>
                                    @hasanyrole('manager|superadmin')
                                        <form action="{{ route('program.destroy', $program->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus program ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endhasanyrole
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Data program tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $programs->links() }}
        </div>
    </div>
@endsection
