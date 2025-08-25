<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payments extends Model
{
    use HasFactory;
    
    protected $table = 'payments';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'category_fee',
        'amount_fee',
        'program_id',
        'approved_at',
        'user_id',
        'account_no',
        'account_name',
        'ppn_fee',
        'pph_fee',
        'remarks',
        'status',
        'file_path',
        'total_transfer',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount_fee' => 'decimal:2',
        'ppn_fee' => 'decimal:2',
        'pph_fee' => 'decimal:2',
        'total_transfer' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'ppn_fee' => 0.00,
        'pph_fee' => 0.00,
        'status' => 'Pending',
    ];

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessor for formatted amounts
    public function getFormattedAmountFeeAttribute()
    {
        return number_format($this->amount_fee, 0, ',', '.');
    }

    public function getFormattedTotalTransferAttribute()
    {
        return number_format($this->total_transfer, 0, ',', '.');
    }

    // Mutator to ensure total_transfer is calculated
    public function setAmountFeeAttribute($value)
    {
        $this->attributes['amount_fee'] = $value;
        $this->calculateTotalTransfer();
    }

    public function setPpnFeeAttribute($value)
    {
        $this->attributes['ppn_fee'] = $value ?? 0;
        $this->calculateTotalTransfer();
    }

    public function setPphFeeAttribute($value)
    {
        $this->attributes['pph_fee'] = $value ?? 0;
        $this->calculateTotalTransfer();
    }

    private function calculateTotalTransfer()
    {
        if (isset($this->attributes['amount_fee'])) {
            $amount = $this->attributes['amount_fee'] ?? 0;
            $ppn = $this->attributes['ppn_fee'] ?? 0;
            $pph = $this->attributes['pph_fee'] ?? 0;
            $this->attributes['total_transfer'] = $amount + $ppn - $pph;
        }
    }

    // Scope for status filtering
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approve');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }
}