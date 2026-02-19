<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'short_definition',
        'description',
        'icon',
        'is_active', // 0 for inactive, 1 for active
    ];
}
