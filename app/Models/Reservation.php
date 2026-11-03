<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'photography_package_id',
        'token',
        'name',
        'whatsapp',
        'date',
        'time',
        'duration',
        'location',
        'request_consultation',
        'consultation_topic',
        'consultation_custom_topic',
        'consultation_media',
        'consultation_offline_location',
        'consultation_notes',
    ];

    /**
     * Relasi ke paket fotografi
     */
    public function package()
    {
        return $this->belongsTo(PhotographyPackage::class, 'photography_package_id');
    }

    public function payment()
{
    return $this->hasOne(Payment::class);
}
}
