<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalLetter extends Model
{
    use HasFactory;

    protected $table = 'internal_letter';
    protected $primaryKey = 'id';
    public $incrementing = false; // Nonaktifkan auto-increment
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'letter_date',
        'letter_no',
        'subject',
        'letter_document',
        'user_id',
        'program_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}
