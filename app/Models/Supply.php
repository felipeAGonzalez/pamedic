<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $table = 'supplies';

    protected $fillable = [
        'material',
        'existencias',
    ];
}
