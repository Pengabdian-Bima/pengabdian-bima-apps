<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreOrderItem extends Model
{
    protected $fillable = [
        'pre_order_id', 'product_id', 'product_name', 'qty', 'price', 'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'integer',
        'subtotal' => 'integer',
    ];

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
