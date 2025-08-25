<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    protected $table = 'programs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'program_name',
        'program_loc',
        'program_duration',
        'program_realization',
        'program_type',
        'program_act_type',
        'program_unit_int',
        'program_remarks',
        'tp_id',
        'bi_code',
        'user_id',        // ✅ Tambahkan ini
        'facilitator_name',
        'program_document'
    ];

    public function training_program()
    {
        return $this->belongsTo(TrainingProgram::class, 'tp_id');
    }

    public function category_bi()
    {
        return $this->belongsTo(CategoryBi::class, 'bi_code', 'bi_code');
    }


    public function category_budget()
    {
        return $this->hasMany(CategoryBudget::class, 'program_id','id');
    }

    public function payments()
    {
        return $this->hasMany(payments::class, 'program_id','id');
    }

    public function participants_payment()
    {
        return $this->hasMany(ParticipantsPayment::class, 'program_id', 'id');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'program_id', 'id');
    }

    // ✅ Relasi yang benar menggunakan user_id
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ✅ Alias untuk backward compatibility jika ada kode lama
    public function pic()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updateRealization()
    {
        // Hitung total pembayaran umum yang sudah diapprove
        $general = $this->payments()
            ->where('status', 'Approve')
            ->sum('total_transfer');

        // Hitung total pembayaran peserta yang sudah diapprove  
        $participant = $this->participants_payment()
            ->where('status', 'Approve')
            ->sum('amount_fee');

        // Update program realization
        $this->program_realization = $general + $participant;
        $this->save();

        // ✅ TRIGGER UPDATE KE TRAINING PROGRAM
        if ($this->training_program) {
            $this->training_program->recalculateRealization();
        }
    }

    public function getRealizationAttribute()
{
    try {
        $programPayments = \App\Models\Payments::where('program_id', $this->id)
            ->where('status', 'Approve')
            ->sum('total_transfer'); // ✅ Gunakan total_transfer bukan amount

        $participantPayments = \App\Models\ParticipantsPayment::where('program_id', $this->id)
            ->where('status', 'Approve')
            ->sum('amount_fee'); // ✅ Gunakan amount_fee bukan amount

        return $programPayments + $participantPayments;
    } catch (\Exception $e) {
        \Log::error("Error in Program getRealizationAttribute: " . $e->getMessage());
        return 0;
    }
}

}