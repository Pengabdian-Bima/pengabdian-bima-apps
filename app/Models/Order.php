<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'total_amount', 'payment_method', 'status',
        'shipping_name', 'shipping_phone', 'shipping_address',
        'shipping_province', 'shipping_city', 'shipping_city_id', 'shipping_district',
        'shipping_village', 'shipping_postal_code', 'notes',
        'shipping_cost', 'courier', 'courier_service', 'rejection_reason'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentConfirmation()
    {
        return $this->hasOne(PaymentConfirmation::class);
    }

    public static function generateOrderCode()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'warning',
            'menunggu_verifikasi' => 'info',
            'diproses' => 'primary',
            'dikirim' => 'primary',
            'selesai' => 'success',
            'ditolak' => 'danger',
            'dibatalkan' => 'danger',
            default => 'secondary',
        };
    }
}
