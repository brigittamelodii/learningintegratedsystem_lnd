<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassEvaluation extends Model
{
    use HasFactory;
    protected $table = 'class_evaluation';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'eval_score',
        'class_id',
        'eval_id',
        'eval_doc'
    ];

    public function classes()
    {
        return $this->belongsTo(classes::class, 'class_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(evaluation::class, 'eval_id');
    }
}
