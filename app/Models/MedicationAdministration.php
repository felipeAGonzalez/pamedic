<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationAdministration extends Model
{
    protected $table = 'medication_administration';

    protected $fillable = [
        'patient_id',
        'nurse_prepare_id',
        'nurse_admin_id',
        'medicine_id',
        'route_administration',
        'dilution',
        'velocity',
        'hour',
        'due_date',
        'history'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function nurse_prepare()
    {
        return $this->belongsTo(User::class);
    }
    public function nurse_admin()
    {
        return $this->belongsTo(User::class);
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

}
