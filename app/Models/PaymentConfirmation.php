<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentConfirmation extends Model
{
    protected $fillable = [
        'order_id', 'sender_name', 'sender_bank',
        'amount', 'transfer_date', 'proof_image', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getProofImageUrlAttribute()
    {
        return asset('storage/' . $this->proof_image);
    }
}
