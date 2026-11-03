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
        'tagihan',
        'total_paid',
        'description',
        'notes',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relasi ke detail/riwayat pembayaran
    public function histories()
    {
        return $this->hasMany(PaymentHistory::class);
    }
}
