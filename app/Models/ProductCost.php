<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCost extends Model
{
    protected $fillable = [
        'sku_id',
        'shop_name',
        'product_name',
        'variation',
        'hpp_amount',
        'overhead_per_pack'
    ];

    // Hubungan satu SKU ke banyak riwayat pesanan (Untuk Fitur Traceability)
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'sku_id', 'sku_id');
    }
}