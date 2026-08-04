<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description',
        'price', 'cost_price', 'discount_percent', 'discount_start_at', 'discount_end_at', 'is_discount_active',
        'stock', 'min_stock', 'weight', 'thumbnail', 'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_percent' => 'float',
        'discount_start_at' => 'datetime',
        'discount_end_at' => 'datetime',
        'is_discount_active' => 'boolean',
        'weight' => 'decimal:2',
        'status' => 'boolean',
    ];

    protected $appends = [
        'thumbnail_url',
        'is_discount_active',
        'final_price',
        'discounted_price',
        'savings_amount',
        'discount_end_at_formatted',
        'discount_remaining_seconds',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return asset('img/no-image.png');
    }

    public function getIsDiscountActiveAttribute(): bool
    {
        if (isset($this->attributes['is_discount_active']) && !$this->attributes['is_discount_active']) {
            return false;
        }

        if ((float)$this->discount_percent <= 0) {
            return false;
        }

        $now = now();

        if ($this->discount_start_at && $now->lt($this->discount_start_at)) {
            return false;
        }

        if ($this->discount_end_at && $now->gt($this->discount_end_at)) {
            return false;
        }

        return true;
    }

    public function getDiscountedPriceAttribute(): float
    {
        if ($this->is_discount_active) {
            $discounted = (float)$this->price * (1 - ((float)$this->discount_percent / 100));
            return round($discounted);
        }
        return (float)$this->price;
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->discounted_price;
    }

    public function getSavingsAmountAttribute(): float
    {
        if ($this->is_discount_active) {
            return round((float)$this->price - $this->discounted_price);
        }
        return 0;
    }

    public function getDiscountRemainingSecondsAttribute(): int
    {
        if (!$this->is_discount_active || !$this->discount_end_at) {
            return 0;
        }

        $diff = now()->diffInSeconds($this->discount_end_at, false);
        return max(0, (int)$diff);
    }

    public function getDiscountEndAtFormattedAttribute(): ?string
    {
        if (!$this->discount_end_at) {
            return null;
        }

        return $this->discount_end_at->locale('id')->translatedFormat('d F Y H:i') . ' WITA';
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
