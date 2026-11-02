<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'amount',
        'payment_method',
        'receipt_photo',
        'notes',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
