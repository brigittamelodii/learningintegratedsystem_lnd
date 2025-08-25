@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


@section('content')
    <div class="container">
        <h2 class="text-center text-primary fw-bold" style="margin-top: 10px">Classes List</h2>
        <form method="GET" action="{{ route('classes.index') }}"
            class="row gx-3 gy-3 align-items-end mb-3 shadow-sm p-3 bg-white rounded">

            {{-- Search --}}
            <div class="col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search class or program..." value="{{ request('search') }}" autocomplete="off">
            </div>

            {{-- Filter by Month --}}
            <div class="col-md-2">
                <label class="form-label small mb-1">Year</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">-- Select Year --</option>
                    @foreach (range(2020, date('Y')) as $year)
                        <option value="{{ $year }}" {{ request('month') == $year ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->year($year)->format('Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Search Button --}}
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-secondary w-50">
                    <i class="bi bi-search fs-5"></i>
                </button>
            </div>

            {{-- Create Class Button --}}
            @role('manager|superadmin|pic')
                <div class="col-md-1 d-grid ms-auto">
                    <a href="{{ route('classes.create') }}" class="btn btn-sm btn-primary text-nowrap">
                        Create Class
                    </a>
                </div>
            @endrole
        </form>
        <div class="table-responsive shadow-sm">
            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-nowrap text-center">
                        <th>#</th>
                        <th>Class</th>
                        <th>Program</th>
                        <th>PIC</th>
                        <th>Batch</th>
                        <th>Start</th>
                        <th>End</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $class->class_name }}</td>
                            <td class="text-truncate">{{ $class->programs->program_name }}</td>
                            <td>
                                {{ $class->programs && $class->programs->user
                                    ? ucwords(str_replace('.', ' ', Str::before($class->programs->user->email, '@')))
                                    : '-' }}
                            </td>

                            <td class="text-center">{{ $class->class_batch }}</td>
                            <td class="text-center"> {{ \Carbon\Carbon::parse($class->start_date)->format('d-m-Y') }}
                            </td>
                            <td class="text-center"> {{ \Carbon\Carbon::parse($class->end_date)->format('d-m-Y') }}
                            </td>
                            <td class="text-nowrap">
                                @role('manager|superadmin|pic')
                                    <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @endrole
                                <a href="{{ route('classes.view', $class->id) }}" class="btn btn-sm btn-primary">View</a>
                                @role('manager|superadmin|pic')
                                    <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this class?')">Delete</button>
                                    </form>
                                    @php
                                        $userEmailName = Str::before(auth()->user()->email, '@');
                                        $picEmailName = optional($class->programs->user)->email
                                            ? Str::before($class->programs->user->email, '@')
                                            : null;
                                    @endphp

                                    @if ($userEmailName === $picEmailName || auth()->user()->hasRole('superadmin'))
                                        <a href="{{ route('classes.duplicate', $class->id) }}"
                                            class="btn btn-sm btn-outline-info text-end">
                                            Add Batch
                                        </a>
                                    @endif
                                @endrole
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No class data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
