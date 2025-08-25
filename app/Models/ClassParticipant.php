<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassParticipant extends Model
{
    protected $table = 'class_participant';

    protected $fillable = ['class_id', 'participant_id', 'batch'];

    public function class()
    {
        return $this->belongsTo(classes::class); // sesuaikan nama model class kamu
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
