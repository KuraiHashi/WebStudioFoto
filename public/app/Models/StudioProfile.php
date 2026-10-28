<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudioProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'operational_hours',
        'social_media',
        'maps_link',
        'description',
    ];
}
