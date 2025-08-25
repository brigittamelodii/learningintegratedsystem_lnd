<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BITrainingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('category_bi')->insert([
            ['bi_code' => '110', 'bi_category_type' => 'Jenis Pelatihan - Teknikal skill', 'bi_desc' => 'Pelaporan Bank'],
            ['bi_code' => '120', 'bi_category_type' => 'Jenis Pelatihan - Teknikal skill', 'bi_desc' => 'Perkreditan/Treasury'],
            ['bi_code' => '130', 'bi_category_type' => 'Jenis Pelatihan - Tekninal Skill', 'bi_desc' => 'Manajemen Risiko'],
            ['bi_code' => '140', 'bi_category_type' => 'Jenis Pelatihan - Teknikal Skill', 'bi_desc' => 'Sosialisasi Ketentuan Perbankan'],
            ['bi_code' => '150', 'bi_category_type' => 'Jenis Pelatihan - Teknikal Skill', 'bi_desc' => 'Audit'],
            ['bi_code' => '160', 'bi_category_type' => 'Jenis Pelatihan - Teknikal skill', 'bi_desc' => 'Teknologi Informasi'],
            ['bi_code' => '170', 'bi_category_type' => 'Jenis Pelatihan - Teknikal skill', 'bi_desc' => 'Manajemen Umum'],
            ['bi_code' => '180', 'bi_category_type' => 'Jenis Pelatihan - Teknikal Skill', 'bi_desc' => 'Manajemen Perbankan'],
            ['bi_code' => '199', 'bi_category_type' => 'Jenis Pelatihan - Teknikal Skill', 'bi_desc' => 'Lainnya'],
            ['bi_code' => '210', 'bi_category_type' => 'Jenis Pelatihan - Soft skill', 'bi_desc' => 'Analisa Masalah dan Pengambilan Keputusan'],
            ['bi_code' => '220', 'bi_category_type' => 'Jenis Pelatihan - Soft skill', 'bi_desc' => 'Customer Relationship skill'],
            ['bi_code' => '230', 'bi_category_type' => 'Jenis Pelatihan - Soft skill', 'bi_desc' => 'Leadership'],
            ['bi_code' => '240', 'bi_category_type' => 'Jenis Pelatihan - Soft Skill', 'bi_desc' => 'Teknik Presentasi dan Komunikasi'],
            ['bi_code' => '299', 'bi_category_type' => 'Jenis Pelatihan - Soft skill', 'bi_desc' => 'Lainnya'],
            ['bi_code' => '310', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Manajemen Risiko'],
            ['bi_code' => '320', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. General Banking'],
            ['bi_code' => '330', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Audit Intern Bank'],
            ['bi_code' => '340', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Wealth Management'],
            ['bi_code' => '350', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Funding & Services'],
            ['bi_code' => '360', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Treasury Dealer, Settlement, dan Money Broker'],
            ['bi_code' => '370', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Kepatuhan Perbankan'],
            ['bi_code' => '380', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Operasional Perbankan'],
            ['bi_code' => '390', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Kredit Perbankan'],
            ['bi_code' => '399', 'bi_category_type' => 'Jenis Pelatihan - Sertifikasi', 'bi_desc' => 'Sert. Lainnya'],
        ]);
    }
}
