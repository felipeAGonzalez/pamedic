<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyOrder extends Model
{
    protected $fillable = [
        'period_start',
        'period_end',
        'generated_at',
        'emergency_from',
        'emergency_to',
        'emergency_count',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'generated_at' => 'datetime',
        'emergency_from' => 'datetime',
        'emergency_to' => 'datetime',
        'emergency_count' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(SupplyOrderItem::class);
    }

    /**
     * Verifica si una fecha cae dentro del periodo de este pedido.
     */
    public function coversDate(string $date): bool
    {
        return $this->period_start->toDateString() <= $date
            && $date <= $this->period_end->toDateString();
    }
}
