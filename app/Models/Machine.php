<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'machine_number'
    ];


public function machine()
{
    return $this->belongsTo(Machine::class);
}

}
