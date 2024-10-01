<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationAdministration extends Model
{
    protected $table = 'medication_administrations';

    protected $fillable = [
        'patient_id',
        'nurse_prepare',
        'nurse_admin',
        'medicine',
        'route_administration',
        'dilution',
        'velocity',
        'hour',
        'due_date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
