<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantsPayment extends Model
{
    use HasFactory;

    protected $table = 'participants_payment';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'category_fee',
        'amount_fee',
        'total_amount',
        'account_no',
        'account_name',
        'program_id',
        'status',
        'class_id',
        'file_path',
        'approved_by_pics_id',
        'participants_id',
        'remarks',
        'user_id'
    ];

    public function programs()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function pics()
    {
        return $this->belongsTo(pic::class, 'approved_by_pics_id');
    }

    public function classes()
    {
        return $this->belongsTo(classes::class, 'class_id');
    }

    public function participants()
    {
        return $this->belongsTo(participant::class, 'participants_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }




}
