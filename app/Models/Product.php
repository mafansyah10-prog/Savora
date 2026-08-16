<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enable_spiciness' => 'boolean',
        'spiciness_levels' => 'array',
        'enable_toppings' => 'boolean',
        'toppings' => 'array',
        'enable_sauces' => 'boolean',
        'sauces' => 'array',
        'enable_additionals' => 'boolean',
        'additionals' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getSellingPriceAttribute()
    {
        return ($this->discount_price && $this->discount_price < $this->price)
            ? $this->discount_price
            : $this->price;
    }

    public function hasDiscount()
    {
        return $this->discount_price && $this->discount_price < $this->price;
    }
}
