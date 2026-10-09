<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferOperatorFee extends Model
{
    protected $fillable = ['operator_id', 'min_amount', 'max_amount', 'fee_amount', 'fee_type'];

    public function operator()
    {
        return $this->belongsTo(TransferOperator::class, 'operator_id');
    }
}
