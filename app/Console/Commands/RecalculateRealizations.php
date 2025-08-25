<?php

namespace App\Console\Commands;

use App\Models\Tna;
use Illuminate\Console\Command;

class RecalculateRealizations extends Command
{
    protected $signature = 'realization:recalculate {tna_id?}';
    protected $description = 'Recalculate all realizations from bottom up';

    public function handle()
    {
        $tnaId = $this->argument('tna_id');

        if ($tnaId) {
            $tnas = Tna::where('id', $tnaId)->get();
        } else {
            $tnas = Tna::all();
        }

        foreach ($tnas as $tna) {
            $this->info("Recalculating TNA: {$tna->tna_year}");

            // Recalculate dari bottom up
            foreach ($tna->category as $category) {
                foreach ($category->training_program as $trainingProgram) {
                    foreach ($trainingProgram->programs as $program) {
                        // Recalculate program realization
                        $program->updateRealization();
                        $this->line("  - Updated Program: {$program->program_name}");
                    }
                    
                    // Recalculate training program realization
                    $trainingProgram->recalculateRealization();
                    $this->line("  - Updated Training Program: {$trainingProgram->tp_name}");
                }
            }

            // Recalculate TNA realization
            $tna->recalculateRealization();
            $this->info("  - Updated TNA realization: {$tna->tna_realization}");
        }

        $this->info('All realizations recalculated successfully!');
    }
}