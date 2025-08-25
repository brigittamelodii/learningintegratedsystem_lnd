@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="text-center text-primary fw-bold mb-2" style="margin-top: 10px">Participants Management</h3>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-3" id="participantsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="final-tab" data-bs-toggle="tab" data-bs-target="#final"
                        type="button" role="tab">✅ Final Participants</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="temp-tab" data-bs-toggle="tab" data-bs-target="#temp" type="button"
                        role="tab">🕗 Invited Participants</button>
                </li>
            </ul>

            <div class="tab-content" id="participantsTabContent">

                {{-- FINAL PARTICIPANTS --}}
                <div class="tab-pane fade show active" id="final" role="tabpanel">
                    <div class="mb-3">
                        <form action="{{ route('participants.index') }}" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                placeholder="Search participants..." value="{{ request('search') }}">
                            <button class="btn btn-primary">Search</button>
                        </form>
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Class</th>
                                <th>NIK</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Unit</th>
                                <th>Pre</th>
                                <th>Post</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($participants as $p)
                                <tr>
                                    <td>{{ $p->classes->class_name ?? '-' }}</td>
                                    <td>{{ $p->karyawan_nik }}</td>
                                    <td>{{ $p->participant_name }}</td>
                                    <td>{{ $p->participant_position }}</td>
                                    <td>{{ $p->participant_working_unit }}</td>
                                    <td>{{ $p->pre_test ?? '-' }}</td>
                                    <td>{{ $p->post_test ?? '-' }}</td>
                                    <td>{{ $p->status }}</td>
                                    <td>
                                        <a href="{{ route('participants.editByClass', ['class_id' => $p->class_id]) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        @if (in_array($p->status, ['Absent - Sick', 'Absent - Maternity', 'Absent - Business']))
                                            <form
                                                action="{{ route('participants.destroyByClass', ['class_id' => $p->class_id, 'id' => $p->id]) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('Yakin ingin menghapus peserta ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No final participants.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TEMPORARY PARTICIPANTS --}}
                <div class="tab-pane fade" id="temp" role="tabpanel">
                    <div class="mb-3">
                        <form action="{{ route('participants.index') }}" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                placeholder="Search participants..." value="{{ request('search') }}">
                            <button class="btn btn-primary">Search</button>
                        </form>
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Class</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Position</th>
                                <th>Working Unit</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tempParticipants as $tp)
                                <tr>
                                    <td>{{ $tp->classes->class_name ?? '-' }}</td>
                                    <td>{{ $tp->karyawan_nik }}</td>
                                    <td>{{ $tp->participant_name }}</td>
                                    <td>{{ $tp->participant_position }}</td>
                                    <td>{{ $tp->participant_working_unit }}</td>
                                    <td>{{ $tp->status }}</td>
                                    <td>
                                        <a href="{{ route('participants.editByClass', ['class_id' => $tp->class_id]) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        @if ($tp->participants_id)
                                            <form action="{{ route('participants.deleteTemp', $tp->participants_id) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('Yakin ingin menghapus peserta sementara ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>Hapus</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No invited participants.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
@endsection
