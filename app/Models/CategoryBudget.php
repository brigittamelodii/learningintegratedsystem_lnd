<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryBudget extends Model
{
    use HasFactory;
    protected $table = 'category_budget';
    protected $primaryKey = 'id';   
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'category_fee',
        'amount_fee',
        'program_id'
    ];
    public function program()
    {
        return $this->belongsTo(program::class, 'program_id');
    }
}
