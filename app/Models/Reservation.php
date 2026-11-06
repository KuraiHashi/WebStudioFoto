<?php

namespace App\Models;

use App\Support\SessionDuration;
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
        $scheduledEnd = Carbon::parse($this->date.' '.$this->time)
            ->addSeconds($this->sessionDurationInSeconds());

        return now()->greaterThanOrEqualTo($scheduledEnd);
    }

    public function sessionDurationInSeconds(): int
    {
        return SessionDuration::inSeconds($this->package?->duration ?? $this->duration);
    }

    public function canSubmitBalancePayment(): bool
    {
        return $this->session_completed_at !== null || $this->canBeMarkedComplete();
    }
}
