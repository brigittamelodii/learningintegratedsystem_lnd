<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class classes extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'class_name',
        'start_date',
        'end_date',
        'class_batch',
        'class_doc',
        'program_id',
    ];
    public function programs()
    {
        return $this->belongsTo(program::class, 'program_id');
    }

    public function pic()
    {
        return $this->belongsTo(pic::class, 'pic_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'class_id');
    }

    public function classEvaluations()
    {
        return $this->hasMany(ClassEvaluation::class, 'class_id');
    }

    public function agenda()
    {
        return $this->hasMany(Agenda::class, 'class_id');
    
    }

    public function participantstemp()
    {
        return $this->hasMany(ParticipantsTemp::class, 'class_id');
    }

}
