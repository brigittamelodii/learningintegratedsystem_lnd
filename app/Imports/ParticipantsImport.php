<?php
namespace App\Imports;

use App\Models\ParticipantsTemp;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantsImport implements ToModel, WithHeadingRow
{
protected $classId;
protected $batch;
public function __construct($class_id, $batch)
{
    $this->classId = $class_id;
    $this->batch = $batch;
}


   public function model(array $row)
{
    $userId = User::where('karyawan_nik', $row['nik'])->value('id');

    // Jika user tidak ditemukan, skip baris ini
    if (!$userId) {
        return null;
    }

    return new ParticipantsTemp([
        'participant_name' => $row['nama'] ?? '',
        'karyawan_nik' => $row['nik'] ?? '',
        'participant_position' => $row['position'] ?? '',
        'participant_working_unit' => $row['working_unit'] ?? '',
        'pre_test' => $row['pre_test'] ?? null,
        'post_test' => $row['post_test'] ?? null,
        'status' => $row['status'] ?? 'Invited',
        'class_id' => $this->classId,
        'user_id' => $userId
    ]);
}


}