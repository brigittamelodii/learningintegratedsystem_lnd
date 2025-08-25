@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('participants.index') }}" style="color: #6c757d; font-size: 14px; text-decoration: none;">← Back to
            All Participants</a>

        <h3 class="mb-4" style="margin-top: 10px">👥 Participants in Class: <strong>{{ $class->class_name }}</strong></h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="participantTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="temp-tab" data-bs-toggle="tab" data-bs-target="#temp" type="button">🕗
                    Temporary</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button">✅
                    Final</button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TEMP PARTICIPANTS --}}
            <div class="tab-pane fade show active" id="temp">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>NIK</th>
                            <th>Position</th>
                            <th>Working Unit</th>
                            <th>Status</th>
                            <th>Pre</th>
                            <th>Post</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tempParticipants as $tp)
                            <tr>
                                <td>{{ $tp->participant_name }}</td>
                                <td>{{ $tp->karyawan_nik }}</td>
                                <td>{{ $tp->participant_position }}</td>
                                <td>{{ $tp->participant_working_unit }}</td>
                                <td>{{ $tp->status }}</td>
                                <td>{{ $tp->pre_test ?? '-' }}</td>
                                <td>{{ $tp->post_test ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('participants.editByClass', ['class_id' => $class->id]) }}"
                                        class="btn btn-sm btn-warning">✏️</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No temporary participants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FINAL PARTICIPANTS --}}
            <div class="tab-pane fade" id="final">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>NIK</th>
                            <th>Position</th>
                            <th>Working Unit</th>
                            <th>Status</th>
                            <th>Pre</th>
                            <th>Post</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($finalParticipants as $fp)
                            <tr>
                                <td>{{ $fp->participant_name }}</td>
                                <td>{{ $fp->karyawan_nik }}</td>
                                <td>{{ $fp->participant_position }}</td>
                                <td>{{ $fp->participant_working_unit }}</td>
                                <td>{{ $fp->status }}</td>
                                <td>{{ $fp->pre_test ?? '-' }}</td>
                                <td>{{ $fp->post_test ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No final participants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
