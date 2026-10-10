<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceReductionRequest extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REFUSED = 'refused';
    const STATUS_COUNTER_OFFER = 'counter_offer';

    protected $fillable = [
        'cart_id', 'product_id', 'cart_base_discount', 'order_id', 'customer_id', 'vendor_id',
        'original_price', 'requested_reduction', 'proposed_price', 'status',
        'counter_offer_amount', 'round_number', 'applied_at', 'applied_reduction',
    ];

    protected $casts = [
        'original_price' => 'float',
        'requested_reduction' => 'float',
        'proposed_price' => 'float',
        'counter_offer_amount' => 'float',
        'cart_base_discount' => 'float',
        'applied_reduction' => 'float',
        'applied_at' => 'datetime',
    ];

    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function vendor(): BelongsTo { return $this->belongsTo(User::class, 'vendor_id'); }

    public function isNegotiationComplete(): bool
    {
        return $this->round_number >= config('multishop.max_negotiation_rounds', 2)
            || in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_REFUSED], true);
    }

    public function scopePending($query) { return $query->where('status', self::STATUS_PENDING); }
    public function scopeActive($query) { return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_COUNTER_OFFER]); }
    public function scopeForVendor($query, $vendorId) { return $query->where('vendor_id', $vendorId); }
    public function scopeForCustomer($query, $customerId) { return $query->where('customer_id', $customerId); }
}
