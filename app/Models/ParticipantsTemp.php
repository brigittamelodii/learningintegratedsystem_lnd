<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantsTemp extends Model
{
    use HasFactory;

    protected $table = 'participants_temp';

    protected $primaryKey = 'participants_id';

    protected $fillable = [
        'karyawan_nik',
        'participant_name',
        'participant_position',
        'participant_working_unit',
        'class_id',
        'status',
        'pre_test',
        'post_test',
        'user_id',
    ];

    public function classes()
{
    return $this->belongsTo(Classes::class, 'class_id');
}

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    

}
