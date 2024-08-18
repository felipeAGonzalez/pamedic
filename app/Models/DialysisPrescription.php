<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DialysisPrescription extends Model
{
    protected $fillable = [
        'patient_id',
        'type_dialyzer',
        'time',
        'blood_flux',
        'flux_dialyzer',
        'heparin',
        'schedule_ultrafilter',
        'profile_ultrafilter',
        'sodium_profile',
        'machine_temperature',
        'history',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
