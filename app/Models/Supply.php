<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $table = 'supplies';

    protected $fillable = [
        'material',
        'type',
        'for_vascular_access',
        'existencias',
    ];

    const TYPES = [
        'filter' => 'Filtro',
        'supply' => 'Insumo',
        'kits' => 'Kits',
    ];
}
