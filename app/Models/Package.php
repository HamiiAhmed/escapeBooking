<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'image', 
        'slug', 
        'price',
        'description',
        'duration_minutes',
        'min_bookings',
        'max_bookings',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];


    public function getRevenueAttribute()
    {
        return $this->bookings()->where('status', 'paid')->sum('total_amount');
    }

    // Slug auto-generate
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($package) {
            $package->slug = Str::slug($package->name);
        });
    }
}
