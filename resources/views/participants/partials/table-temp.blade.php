<h5>🕗 Temporary Participants (Invited)</h5>
<table class="table table-bordered mb-4">
    <thead class="table-light">
        <tr>
            <th>NIK</th>
            <th>Name</th>
            <th>Position</th>
            <th>Unit</th>
            {{-- <th>Pre</th>
            <th>Post</th> --}}
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tempParticipants as $p)
        <tr>
            <td>{{ $p->karyawan_nik }}</td>
            <td>{{ $p->participant_name }}</td>
            <td>{{ $p->participant_position}}</td>
            <td>{{ $p->participant_working_unit }}</td>
            {{-- <td><input type="number" name="participants[{{ $p->participants_id }}][pre_test]" value="{{ $p->pre_test }}" class="form-control"></td>
            <td><input type="number" name="participants[{{ $p->participants_id }}][post_test]" value="{{ $p->post_test }}" class="form-control"></td> --}}
            <td>
                <select name="participants[{{ $p->participants_id }}][status]" class="form-select">
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" {{ $p->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="participants[{{ $p->participants_id }}][source]" value="temp">
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
