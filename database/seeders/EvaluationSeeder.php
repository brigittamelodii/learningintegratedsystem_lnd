<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationSeeder extends Seeder
{
    public function run()
    {
        $evaluations = [
            ['eval_cat' => 'Materi', 'eval_desc' => 'Memberikan manfaat dalam meningkatkan wawasan pengetahuan'],
            ['eval_cat' => 'Materi', 'eval_desc' => 'Memberikan manfaat dalam menunjang kinerja pekerjaan'],
            ['eval_cat' => 'Materi', 'eval_desc' => 'Tampilan presentasi modul dapat dimengerti'],
            ['eval_cat' => 'Pengajar', 'eval_desc' => 'Kemampuan menjelaskan materi dengan bahasa yang mudah dipahami'],
            ['eval_cat' => 'Pengajar', 'eval_desc' => 'Menguasai isi materi yang dibawakan'],
            ['eval_cat' => 'Pengajar', 'eval_desc' => 'Kemampuan menciptakan suasana belajar yang efektif baik dari sisi Metode dan Waktu'],
            ['eval_cat' => 'Kepanitiaan', 'eval_desc' => 'Support panitia terhadap kelancaran proses training'],
            ['eval_cat' => 'Kepanitiaan', 'eval_desc' => 'Support panitia terhadap pemenuhan kebutuhan peserta training'],
            ['eval_cat' => 'Kepanitiaan', 'eval_desc' => 'Etika panitia dalam berinteraksi dengan peserta training'],
        ];

        DB::table('evaluation')->insert($evaluations);
    }
}
