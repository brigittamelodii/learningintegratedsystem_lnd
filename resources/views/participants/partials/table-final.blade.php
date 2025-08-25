<h5>✅ Final Participants</h5>
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>NIK</th>
            <th>Name</th>
            <th>Position</th>
            <th>Unit</th>
            <th>Pre</th>
            <th>Post</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($finalParticipants as $p)
            <tr>
                <td>{{ $p->karyawan_nik }}</td>
                <td>{{ $p->participant_name }}</td>
                <td>{{ $p->participant_position }}</td>
                <td>{{ $p->participant_working_unit }}</td>
                <td>
                    <input type="number" name="participants[{{ $p->id }}][pre_test]"
                        value="{{ old("participants.$p->id.pre_test", $p->pre_test) }}" class="form-control" min="0"
                        max="100">
                </td>
                <td>
                    <input type="number" name="participants[{{ $p->id }}][post_test]"
                        value="{{ old("participants.$p->id.post_test", $p->post_test) }}" class="form-control"
                        min="0" max="100">
                </td>
                <td>
                    <select name="participants[{{ $p->id }}][status]" class="form-select">
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" {{ $p->status == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="participants[{{ $p->id }}][source]" value="main">
                </td>
                <td>
                    @if (in_array($p->status, ['Absent - Sick', 'Absent - Maternity']))
                        <form
                            action="{{ route('participants.destroyByClass', ['class_id' => $class->id, 'id' => $p->id]) }}"
                            method="POST" onsubmit="return confirm('Yakin ingin menghapus peserta ini?')">

                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
