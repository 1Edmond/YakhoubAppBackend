<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTransferProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transfer_account_id',
        'transaction_reference',
        'proof_image',
        'status',
        'transfer_fee_amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transferAccount()
    {
        return $this->belongsTo(TransferAccount::class);
    }
}
