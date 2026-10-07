<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyOrderItem extends Model
{
    protected $fillable = [
        'supply_id',
        'material',
        'requested_quantity',
        'cancellation_quantity',
    ];

    protected $casts = [
        'requested_quantity' => 'float',
        'cancellation_quantity' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(SupplyOrder::class, 'supply_order_id');
    }
}
