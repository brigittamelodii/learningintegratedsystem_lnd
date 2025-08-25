<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\ParticipantsTemp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FinalParticipantScoresImport implements ToCollection, WithHeadingRow
{
    protected $classId;
    public $log = [
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    public function __construct($classId)
    {
        $this->classId = $classId;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            try {
                // Normalize headers (remove extra spaces, convert to lowercase)
                $rowData = [];
                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(trim(str_replace(' ', '_', $key)));
                    $rowData[$normalizedKey] = $value;
                }

                // Required fields mapping
                $requiredFields = [
                    'nik' => $rowData['nik'] ?? $rowData['karyawan_nik'] ?? null,
                    'nama' => $rowData['nama'] ?? $rowData['participant_name'] ?? $rowData['name'] ?? null,
                    'status' => $rowData['status'] ?? 'Present',
                ];

                // Skip if missing required data
                if (empty($requiredFields['nik']) || empty($requiredFields['nama'])) {
                    $this->log['skipped']++;
                    $this->log['errors'][] = "Baris dilewati: NIK atau Nama kosong";
                    continue;
                }

                // Optional fields
                $preTest = $this->validateTestScore($rowData['pre_test'] ?? null);
                $postTest = $this->validateTestScore($rowData['post_test'] ?? null);
                $position = $rowData['position'] ?? $rowData['participant_position'] ?? '';
                $workingUnit = $rowData['working_unit'] ?? $rowData['participant_working_unit'] ?? '';

                // Check if participant exists in temp table first
                $tempParticipant = ParticipantsTemp::where('class_id', $this->classId)
                    ->where('karyawan_nik', $requiredFields['nik'])
                    ->first();

                if ($tempParticipant) {
                    // Move from temp to final table
                    $finalParticipant = Participant::create([
                        'class_id' => $this->classId,
                        'karyawan_nik' => $requiredFields['nik'],
                        'participant_name' => $requiredFields['nama'],
                        'participant_position' => $position ?: $tempParticipant->participant_position,
                        'participant_working_unit' => $workingUnit ?: $tempParticipant->participant_working_unit,
                        'status' => $requiredFields['status'],
                        'pre_test' => $preTest,
                        'post_test' => $postTest,
                        'user_id' => $tempParticipant->user_id,
                    ]);

                    // Delete from temp table
                    $tempParticipant->delete();
                    $this->log['inserted']++;
                    
                } else {
                    // Check if participant exists in final table
                    $finalParticipant = Participant::where('class_id', $this->classId)
                        ->where('karyawan_nik', $requiredFields['nik'])
                        ->first();

                    if ($finalParticipant) {
                        // Update existing participant
                        $finalParticipant->update([
                            'participant_name' => $requiredFields['nama'],
                            'participant_position' => $position ?: $finalParticipant->participant_position,
                            'participant_working_unit' => $workingUnit ?: $finalParticipant->participant_working_unit,
                            'status' => $requiredFields['status'],
                            'pre_test' => $preTest ?? $finalParticipant->pre_test,
                            'post_test' => $postTest ?? $finalParticipant->post_test,
                        ]);
                        $this->log['updated']++;
                        
                    } else {
                        // Create new participant
                        Participant::create([
                            'class_id' => $this->classId,
                            'karyawan_nik' => $requiredFields['nik'],
                            'participant_name' => $requiredFields['nama'],
                            'participant_position' => $position,
                            'participant_working_unit' => $workingUnit,
                            'status' => $requiredFields['status'],
                            'pre_test' => $preTest,
                            'post_test' => $postTest,
                            'user_id' => null, // Set default or handle as needed
                        ]);
                        $this->log['inserted']++;
                    }
                }

            } catch (\Exception $e) {
                $this->log['skipped']++;
                $this->log['errors'][] = "Error pada baris dengan NIK {$requiredFields['nik']}: " . $e->getMessage();
                Log::error("Import error for NIK {$requiredFields['nik']}: " . $e->getMessage());
            }
        }
    }

    private function validateTestScore($score)
    {
        if ($score === null || $score === '') {
            return null;
        }

        $score = (float) $score;
        return ($score >= 0 && $score <= 100) ? $score : null;
    }
}