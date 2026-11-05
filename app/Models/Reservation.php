<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'session_completed_at',
        'photo_status',
        'photo_delivery_url',
        'edit_request_message',
        'edited_photo_url',
    ];

    protected $casts = [
        'session_completed_at' => 'datetime',
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

    public function testimonial(): HasOne
    {
        return $this->hasOne(Testimonial::class);
    }

    public function canBeMarkedComplete(): bool
    {
        preg_match('/\d+/', $this->duration, $durationMatches);
        $durationHours = isset($durationMatches[0]) ? (int) $durationMatches[0] : 2;
        $scheduledEnd = Carbon::parse($this->date.' '.$this->time)->addHours($durationHours);

        return now()->greaterThanOrEqualTo($scheduledEnd);
    }
}
