<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class evaluation extends Model
{
    use HasFactory;
    protected $table = 'evaluation';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'eval_cat'
    ];

    public function class_evaluation()
    {
        return $this->hasMany(ClassEvaluation::class, 'evaluation_id');
    }

}
