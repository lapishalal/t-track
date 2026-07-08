<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'sku_id',
        'shop_name',
        'order_status',
        'product_name',
        'variation',
        'quantity',
        'order_amount',
        'shipping_fee_estimated',
        'buyer_username',
        'province',
        'regency_city',
        'tracking_id',
        'created_time',
        'batch_id',
        'audited_at',
        'hidden_at',
        'retur_moved_at',
        'retur_completed_at'
    ];

    protected $casts = [
        'audited_at' => 'datetime',
        'hidden_at' => 'datetime',
        'retur_moved_at' => 'datetime',
        'retur_completed_at' => 'datetime',
    ];

    // Hubungan 1 to 1 dari Order ke data Keuangan (Income)
    public function income(): HasOne
    {
        return $this->hasOne(Income::class, 'order_id', 'order_id');
    }

    // Hubungan balik ke Kamus HPP Produk
    public function productCost(): BelongsTo
    {
        return $this->belongsTo(ProductCost::class, 'sku_id', 'sku_id');
    }
}