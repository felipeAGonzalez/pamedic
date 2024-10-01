<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OxygenTherapy extends Model
{
    protected $table = 'oxygen_therapy';

    protected $fillable = [
        'patient_id',
        'initial_oxygen_saturation',
        'final_oxygen_saturation',
        'start_time',
        'end_time',
        'oxygen_flow',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
