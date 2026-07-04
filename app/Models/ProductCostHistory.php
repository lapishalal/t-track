<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCostHistory extends Model
{
    protected $fillable = [
        'sku_id',
        'shop_name',
        'product_name',
        'hpp_amount_old',
        'hpp_amount_new',
        'overhead_per_pack_old',
        'overhead_per_pack_new',
        'changed_by',
    ];
}