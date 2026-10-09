<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'name',
        'account_number',
        'daily_limit',
        'current_daily_amount',
        'owner_type',
        'owner_id',
        'is_active',
    ];

    public function operator()
    {
        return $this->belongsTo(TransferOperator::class, 'operator_id');
    }
}
