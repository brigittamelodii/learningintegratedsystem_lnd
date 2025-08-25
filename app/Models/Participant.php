<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class participant extends Model
{
    use HasFactory;
    protected $table = 'participants';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'participant_name',
        'karyawan_nik',
        'participant_position',
        'participant_working_unit',
        'pre_test',
        'post_test',
        'status',
        'user_id',
        'class_id'
    ];
    
    public function user(){
        return $this->belongsTo(user::class, 'user_id');
    }
    public function classes()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
    public function class_evaluation()
    {
        return $this->hasMany(ClassEvaluation::class, 'participant_id');
    }
    public function payment()
    {
        return $this->hasMany(payments::class, 'participant_id');    
    }
    public function classParticipants()
    {
    return $this->hasMany(ClassParticipant::class);
    }

}
