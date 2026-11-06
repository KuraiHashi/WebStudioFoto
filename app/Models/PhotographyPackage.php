<?php

namespace App\Models;

use App\Support\SessionDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotographyPackage extends Model
{
    use HasFactory;

    protected $table = 'photography_packages';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'category',
        'price',
        'duration',
        'bonus',
        'description',
        'facilities',
        'photos',
        'is_wedding',
    ];

    // Otomatis convert JSON dari database ke array PHP
    protected $casts = [
        'photos' => 'array',
    ];

    public function durationInSeconds(): int
    {
        return SessionDuration::inSeconds($this->duration);
    }
}
