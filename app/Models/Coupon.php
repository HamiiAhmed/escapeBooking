<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function isValid($amount = 0)
    {
        return $this->is_active &&
            Carbon::today()->between($this->start_date, $this->end_date) &&
            ($this->usage_limit === null || $this->used_count < $this->usage_limit) &&
            // ✅ Min amount check optional
            ($this->min_amount === 0 || $this->min_amount === null || $amount >= $this->min_amount);
    }


    public function calculateDiscount($amount)
    {
        if (!$this->isValid($amount)) {
            return 0;
        }

        return $this->discount_type === 'percent'
            ? $amount * ($this->discount_value / 100)
            : min($this->discount_value, $amount);
    }

    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    // Scope for active coupons
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }
}
