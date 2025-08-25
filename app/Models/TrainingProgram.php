<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    use HasFactory;
    protected $table = 'training_programs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'tp_name',
        'tp_duration',
        'tp_invest',
        'tp_realization',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(category::class, 'category_id');
    }

    public function programs()
    {
    return $this->hasMany(Program::class, 'tp_id'); // pastikan foreign key 'tp_id' sesuai
    }

    public function getTpRealizationAttribute()
{
    return $this->programs->sum('program_realization');
}

// TrainingProgram.php
  public function recalculateRealization()
    {
        // Hitung total realisasi dari semua program dalam training program ini
        $totalRealization = $this->programs()->sum('program_realization');
        
        // Update tp_realization di database
        $this->tp_realization = $totalRealization;
        $this->save();
        
        // ✅ TRIGGER UPDATE KE TNA
        if ($this->category && $this->category->tna) {
            $this->category->tna->recalculateRealization();
        }
    }

}
