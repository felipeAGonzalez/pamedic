<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivePatient extends Model
{
    protected $table = 'active_patient';

    protected $fillable = [
        'patient_id',
        'fecha',
        'active',
        'created_at',
        'updated_at'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function nursePatient()
    {
        return $this->hasOne(NursePatient::class);
    }
}
