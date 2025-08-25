<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskFill extends Model
{
    use HasFactory;
    
    protected $table = 'form_task_fills';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'form_task_id',
        'fillerName',
        'status',
        'created_at',
        'updated_at',
        'user_id'
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public function formTask()
    {
        return $this->belongsTo(FormTask::class, 'form_task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInReview()
    {
        return $this->status === self::STATUS_IN_REVIEW;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function submit()
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'created_at' => now()
        ]);
    }
    
    public function startReview()
    {
        $this->update([
            'status' => self::STATUS_IN_REVIEW,
        ]);
    }
    
    public function accept($remarks = null)
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'updated_at' => now(),
        ]);
    }
    
    public function reject($remarks)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'updated_at' => now(),
        ]);
    }

    public function fillDocuments()
    {
        return $this->hasMany(TaskDocument::class, 'task_fill_id');
    }
}
