<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mandazi extends Model
{
    use HasFactory;

    // ADD THIS LINE - Specify the correct table name
    protected $table = 'mandazi';

    protected $fillable = [
        'user_id',
        'seller_id',
        'quantity',
        'price_per_unit',
        'total_amount',
        'phone_number',
        'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}