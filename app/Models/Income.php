<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    protected $fillable = [
        'order_id',
        'shop_name',
        'transaction_type',
        'disbursement_amount',
        'total_revenue',
        'platform_commission_fee',
        'payment_fee',
        'affiliate_commission',
        'shipping_fee_real',
        'payout_time',
        'batch_id'
    ];

    // Hubungan balik ke data operasional pesanan
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}