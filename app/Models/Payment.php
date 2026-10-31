<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'token',
        'status',
        'amount',
        'receipt_photo',
        'notes',
    ];

    // Relasi ke tabel Reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
