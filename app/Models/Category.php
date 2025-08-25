<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'description',
        'tna_id'
    ];
    public function tna()
    {
        return $this->belongsTo(tna::class, 'tna_id');
    }

    public function training_program()
    {
        return $this->hasMany(TrainingProgram::class, 'category_id');
    }
    
}
