<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PicSeeder extends Seeder
{
    public function run()
    {
        // Menambahkan data ke tabel 'pic'
        DB::table('pics')->insert([
            [
                'pic_name' => 'Salma',
                'pic_position' => 'TO',
                'pic_working_unit' => 'Learning & Development',
                'karyawan_nik' => '100010',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pic_name' => 'Lintang',
                'pic_position' => 'TO',
                'pic_working_unit' => 'Learning & Development',
                'karyawan_nik' => '100011',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pic_name' => 'Stefan',
                'pic_position' => 'Manager',
                'pic_working_unit' => 'Learning & Development',
                'karyawan_nik' => '100012',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Tambahkan lebih banyak data sesuai kebutuhan
        ]);
    }
}
