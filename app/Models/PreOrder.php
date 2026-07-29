<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PreOrder extends Model
{
    protected $fillable = [
        'user_id',
        'po_code',
        'status',
        'notes',
        'rejection_reason',
        'estimated_days',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_province',
        'shipping_city',
        'shipping_district',
        'shipping_village',
        'shipping_postal_code',
        'city_id',
        'courier',
        'courier_service',
        'shipping_cost',
        'payment_method',
        'total_amount',
        'payment_proof',
        'payment_sender_name',
        'payment_sender_bank',
        'payment_amount',
        'payment_date',
    ];

    protected $casts = [
        'shipping_cost' => 'integer',
        'total_amount' => 'integer',
        'estimated_days' => 'integer',
        'payment_amount' => 'integer',
        'payment_date' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PreOrderItem::class);
    }

    public static function generatePoCode(): string
    {
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())->count() + 1;
        return 'PO' . $date . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Menunggu Review',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return [
            'pending' => 'warning',
            'accepted' => 'info',
            'rejected' => 'danger',
            'processing' => 'primary',
            'completed' => 'success',
            'cancelled' => 'default',
        ][$this->status] ?? 'default';
    }
}
