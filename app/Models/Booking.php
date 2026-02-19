<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'package_id',
        'booking_start_time',
        'duration_minutes',
        'people_count',
        'customer_name',
        'customer_phone',
        'customer_email',
        'total_amount',
        'status',
        'payment_id',
        'notes',
    ];

    protected $casts = [
        'booking_start_time' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function getPaymentStatusAttribute()
    {
        return $this->payment ? $this->payment->status : 'unpaid';
    }
}
