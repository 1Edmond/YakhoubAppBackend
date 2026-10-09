<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferOperator extends Model
{
    protected $fillable = ['name', 'logo', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fees()
    {
        return $this->hasMany(TransferOperatorFee::class, 'operator_id');
    }
}
