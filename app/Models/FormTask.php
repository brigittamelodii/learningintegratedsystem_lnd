<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTask extends Model
{
    use HasFactory;
    protected $table = 'form_tasks';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'subjectName',
        'subjectDesc',
        'accessCode',
        'due_date',
        'user_id',
        'created_at',
        'updated_at',
        'status',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];
    
    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPT = 'accept';
    const STATUS_REJECT = 'reject';
    const STATUS_CLOSED = 'closed';
    const STATUS_DRAFT = 'draft';    

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function generateAccessCode()
{
    do {
        $code = strtoupper(str()->random(6)); // Misalnya 6 karakter acak
    } while (self::where('accessCode', $code)->exists());

    return $code;
}

public function taskCategories()
{
    return $this->hasMany(TaskCategories::class, 'task_id');
}

public function taskFills()
    {
        return $this->hasMany(TaskFill::class, 'form_task_id');
    }

public function getUserFill($userId)
{
    return $this->taskFills()->where('user_id', $userId)->first();
}


public function userFill()
{
    return $this->hasOne(TaskFill::class, 'form_task_id')
        ->where('user_id', auth()->id());
}
    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPT;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECT;
    }
    
    public function isClosed()
    {
        return $this->status === self::STATUS_CLOSED;
        $this->save();
    }

    public function close()
    {
    $this->status = 'closed'; // contoh perubahan status
    $this->save();
    }

    public function canUserJoin($userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Already joined
        if ($this->taskFills()->where('user_id', $userId)->exists()) {
            return false;
        }

        // Task is not open
        if (!$this->isOpen()) {
            return false;
        }

        // Deadline passed
        if ($this->due_date && $this->due_date->isPast()) {
            return false;
        }

        return true;
    }

}


