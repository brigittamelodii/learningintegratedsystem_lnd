<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Participant;
use App\Models\ParticipantsTemp;

class MergeParticipantsTables extends Command
{
    protected $signature = 'participants:merge-temp';
    protected $description = 'Merge data from ParticipantsTemp into the final participants table';

    public function handle()
    {
        $imported = 0;
        $skipped = 0;

        foreach (ParticipantsTemp::all() as $temp) {
            $exists = Participant::where('karyawan_nik', $temp->karyawan_nik)
                ->where('class_id', $temp->class_id)
                ->exists();

            if (!$exists) {
                Participant::create([
                    'class_id' => $temp->class_id,
                    'karyawan_nik' => $temp->karyawan_nik,
                    'participant_name' => $temp->participant_name,
                    'participant_position' => $temp->participant_position,
                    'participant_working_unit' => $temp->participant_working_unit,
                    'status' => $temp->status ?? 'Invited',
                    'pre_test' => null,
                    'post_test' => null,
                    'user_id' => $temp->user_id,
                ]);
                $imported++;
            } else {
                $skipped++;
            }
        }

        $this->info("Selesai. $imported peserta berhasil dipindahkan, $skipped dilewati karena sudah ada.");
    }
}
