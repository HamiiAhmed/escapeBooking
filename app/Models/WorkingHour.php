<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingHour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'day_type',
        'start_time',
        'end_time',
        'is_overnight', 
        'is_active'
    ];

    protected $casts = [
        'is_overnight' => 'boolean',
        'is_active' => 'boolean'
    ];

    // ✅ UNIQUE VALIDATION - Single entry per day
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            self::where('day_type', $model->day_type)->delete(); // Delete existing
        });
    }
}
