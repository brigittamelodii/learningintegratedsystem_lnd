<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Event\Tracer\Tracer;

class CategoryBi extends Model
{
    use HasFactory;

    protected $table = 'category_bi';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'bi_code',
        'bi_category_type',
        'bi_desc',
    ];

    public function program()
    {
        return $this->hasMany(Program::class, 'bi_code');
    }
}
