@extends('layouts.app')

@section('content')
    <div class="container my-4">
        <h2 class="mb-4 text-primary"><strong>Assign Roles to Users</strong></h2>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Info Alert about Role Assignment --}}
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Note:</strong> Admin roles (Manager, PIC, Executive, Super Admin) automatically include Participant
            access for learning activities.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill me-2"></i><strong>User Role Management</strong></span>
                <form class="d-flex" method="GET" action="{{ route('admin.users.index') }}">
                    <input class="form-control form-control-sm me-2" type="search" name="search"
                        value="{{ request('search') }}" placeholder="Search email or NIK..." aria-label="Search">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Email</th>
                            <th>NIK</th>
                            <th>Primary Role</th>
                            <th>All Roles</th>
                            <th>Assign Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->karyawan_nik ?? '-' }}</td>
                                <td>
                                    @php
                                        $primaryRole = $user->getPrimaryRole(); // Using our helper method
                                        $badgeClass = match ($primaryRole) {
                                            'superadmin' => 'bg-danger',
                                            'manager' => 'bg-purple',
                                            'pic' => 'bg-primary',
                                            'executive' => 'bg-success',
                                            'participant' => 'bg-info',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $user->getRoleDisplayName() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($user->getRoleNames() as $role)
                                            @php
                                                $badgeClass = match ($role) {
                                                    'superadmin' => 'bg-danger',
                                                    'manager' => 'bg-purple',
                                                    'pic' => 'bg-primary',
                                                    'executive' => 'bg-success',
                                                    'participant' => 'bg-info',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} badge-sm">
                                                {{ ucfirst($role) }}
                                            </span>
                                        @empty
                                            <span class="text-muted small">No roles</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.assignRole', $user->id) }}"
                                        class="d-flex align-items-center">
                                        @csrf
                                        <div class="input-group input-group-sm">
                                            <select name="role" class="form-select">
                                                <option value="">Select Role...</option>
                                                @foreach ($roles as $role)
                                                    @php
                                                        $isSelected = false;
                                                        // For display purposes, show admin roles as selected if user has them
                                                        // even though they also have participant
                                                        $adminRoles = ['superadmin', 'manager', 'pic', 'executive'];
                                                        if (in_array($role->name, $adminRoles)) {
                                                            $isSelected = $user->hasRole($role->name);
                                                        } else {
                                                            // For participant, only show as selected if it's their only role
                                                            $isSelected =
                                                                $user->hasRole($role->name) &&
                                                                !$user->hasAnyRole($adminRoles);
                                                        }
                                                    @endphp
                                                    <option value="{{ $role->name }}"
                                                        {{ $isSelected ? 'selected' : '' }}>
                                                        {{ ucfirst($role->name) }}
                                                        @if (in_array($role->name, ['manager', 'pic', 'executive', 'superadmin']))
                                                            (+Participant)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-primary" type="submit" title="Assign Role">
                                                <i class="bi bi-check-lg"></i> Save
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-person-x fs-1 text-muted"></i>
                                    <p class="mt-2">No users found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->count() > 0)
                <div class="card-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <small class="text-muted">
                                Showing {{ $users->count() }} user(s)
                            </small>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <span class="badge bg-danger badge-sm">Super Admin</span>
                                <span class="badge bg-purple badge-sm">Manager</span>
                                <span class="badge bg-primary badge-sm">PIC</span>
                                <span class="badge bg-success badge-sm">Executive</span>
                                <span class="badge bg-info badge-sm">Participant</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Bulk Actions (Optional) --}}
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <strong><i class="bi bi-lightning-charge me-2"></i>Bulk Actions</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.bulkAssignRole') }}" id="bulkForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Select Users (by email)</label>
                            <select name="users[]" class="form-select" multiple size="4">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Assign Role</label>
                            <select name="role" class="form-select">
                                <option value="">Select Role...</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">
                                        {{ ucfirst($role->name) }}
                                        @if (in_array($role->name, ['manager', 'pic', 'executive', 'superadmin']))
                                            (+Participant)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-all"></i> Bulk Assign
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .badge-sm {
            font-size: 0.75em;
        }
    </style>
@endsection
