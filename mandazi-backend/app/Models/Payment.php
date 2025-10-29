<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'mandazi_id',
        'transaction_id',
        'checkout_request_id',
        'amount',
        'phone_number',
        'status',
        'mpesa_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function mandazi()
    {
        return $this->belongsTo(Mandazi::class);
    }
}