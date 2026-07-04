<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingClaim extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_id',
        'shipping_fee_estimated',
        'shipping_fee_real',
        'selisih_rugi',
        'ekspedisi',
        'ticket_number',
        'status',
        'tanggal_klaim',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_klaim' => 'date',
    ];
}
