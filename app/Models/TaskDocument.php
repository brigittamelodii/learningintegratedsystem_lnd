<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TaskDocument extends Model
{
    use HasFactory;

    protected $table = 'task_documents';
    protected $primaryKey = 'id';   
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'documentFile',
        'documentDesc',
        'documentName',
        'comment',
        'status',
        'taskcategory_id',
        'task_fill_id'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public function taskCategory()
    {
        return $this->belongsTo(TaskCategories::class, 'taskcategory_id');
    }

    public function taskFill()
    {
    return $this->belongsTo(TaskFill::class, 'task_fill_id');
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

    

}
