<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCategories extends Model
{
    use HasFactory;
    protected $table = 'task_categories';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'categoryName',
        'categoryDesc',
        'task_id'   
    ];
    public function task()
    {
        return $this->belongsTo(FormTask::class, 'task_id');
    }

    public function documents()
{
    return $this->hasMany(TaskDocument::class, 'taskcategory_id');
}

public function filledDocuments()
{
    return $this->hasMany(TaskDocument::class, 'taskcategory_id')->whereNotNull('documentFile');
}



}
