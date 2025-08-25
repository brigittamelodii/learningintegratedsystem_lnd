<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tna extends Model
{
    protected $table = 'tna';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'tna_document',
        'tna_year',
        'tna_min_budget',
        'tna_max_budget',
        'tna_remarks',
        'tna_realization'
    ];

    public function category()
    {
        return $this->hasMany(Category::class, 'tna_id');
    }


 public function recalculateRealization()
    {
        // Hitung total dari semua training programs dalam TNA ini
        $totalRealization = $this->category()
            ->with('training_program')
            ->get()
            ->flatMap->training_program
            ->sum('tp_realization');
            
        $this->tna_realization = $totalRealization;
        $this->save();
    }

    // ✅ PERBAIKI METHOD updateRealization() yang error
    public function updateRealization()
    {
        $this->recalculateRealization();
        return $this;
    }

    public function getRealizationAttribute()
{
    try {
        $totalRealization = 0;

        foreach ($this->category as $category) {
            foreach ($category->training_program as $trainingProgram) {
                foreach ($trainingProgram->programs as $program) {
                    // Hitung payments yang approved dengan field yang benar
                    $generalPayments = \App\Models\Payments::where('program_id', $program->id)
                        ->where('status', 'Approve')
                        ->sum('total_transfer'); // ✅ Gunakan total_transfer

                    $participantPayments = \App\Models\ParticipantsPayment::where('program_id', $program->id)
                        ->where('status', 'Approve')
                        ->sum('amount_fee'); // ✅ Gunakan amount_fee

                    $totalRealization += ($generalPayments + $participantPayments);
                }
            }
        }

        return $totalRealization;
    } catch (\Exception $e) {
        \Log::error("Error in Tna getRealizationAttribute: " . $e->getMessage());
        return 0;
    }
}

}