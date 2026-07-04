<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $fillable = [
        'shop_name',
        'target_month',
        'target_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_month' => 'date',
            'target_amount' => 'decimal:2',
        ];
    }
}
