<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pic extends Model
{
    use HasFactory;

    protected $table = 'pics';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'pic_name',
        'pic_position',
        'pic_working_unit',
        'karyawan_nik'
    ];

    public function program()
    {
        return $this->hasMany(program::class, 'pic_id');
    }

    public function payments()
    {
        return $this->hasMany(payments::class, 'pic_id');
    }

    
}
