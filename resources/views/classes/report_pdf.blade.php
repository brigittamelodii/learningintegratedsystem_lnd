<!DOCTYPE html>
<html lang="en">

</head>

<head>
    <title>Class Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>

<body>
    <h2>Class Report</h2>
    <table>
        <thead>
            <tr>
                <th>Program</th>
                <th>Class Name</th>
                <th>PIC</th>
                <th>Batch</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Participants</th>
            </tr>
        </thead>
        <tbody>
            <td>{{ $classes->programs->program_name ?? '-' }}</td>
            <td>{{ $classes->class_name }}</td>
            <td>{{ $classes->programs->pic->pic_name ?? '-' }}</td>
            <td>{{ $classes->class_batch }}</td>
            <td>{{ \Carbon\Carbon::parse($classes->start_date)->format('d-m-Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($classes->end_date)->format('d-m-Y') }}</td>
            <td>{{ $total_participants }}</td>
        </tbody>
    </table>
    <h3>Participants List</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIK</th>
                <th>Name</th>
                <th>Position</th>
                <th>Working Unit </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($participants as $index => $participant)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $participant->karyawan_nik }}</td>
                    <td>{{ $participant->participant_name }}</td>
                    <td>{{ $participant->participant_position }}</td>
                    <td>{{ $participant->participant_working_unit }}</td>
                </tr>
            @endforeach
        </tbody>
</body>

</html>
