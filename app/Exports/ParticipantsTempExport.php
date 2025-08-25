<?php

namespace App\Exports;

use App\Models\ParticipantsTemp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ParticipantsTempExport implements FromCollection, WithHeadings
{
    protected $classId;

    public function __construct($classId)
    {
        $this->classId = $classId;
    }

    public function collection()
    {
        return ParticipantsTemp::where('class_id', $this->classId)
            ->select(
                'karyawan_nik as nik',
                'participant_name as nama',
                'participant_position as position',
                'participant_working_unit as working_unit',
                'pre_test',
                'post_test',
                'status'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'nik',
            'nama',
            'position',
            'working_unit',
            'pre_test',
            'post_test',
            'status'
        ];
    }
}


