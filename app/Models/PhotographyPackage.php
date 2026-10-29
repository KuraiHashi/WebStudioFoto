<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotographyPackage extends Model
{
    use HasFactory;

    protected $table = 'photography_packages';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'price',
        'duration',
        'bonus',
        'description',
        'facilities',
    ];
}
