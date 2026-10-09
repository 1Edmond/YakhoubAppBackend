<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReductionTier extends Model
{
    protected $fillable = ['tier_number', 'amount', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
