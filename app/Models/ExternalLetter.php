<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalLetter extends Model
{
    use HasFactory;

    protected $table = 'external_letter';
    protected $primaryKey = 'id';
    public $incrementing = false; // Karena kita pakai ID manual (custom gap ID)
    protected $keyType = 'int';

    protected $fillable = [
        'id', // penting karena ID akan diisi manual
        'letter_date',
        'letter_no',
        'subject',
        'letter_document',
        'recipient_initial',
        'user_id',
        'program_id', // hanya jika memang relasi ini ada
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
