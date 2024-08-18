<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransHemodyaisis extends Model
{
    protected $table = 'trans_hemodyaisis';

    protected $fillable = [
        'patient_id',
        'time',
        'arterial_pressure',
        'mean_pressure',
        'heart_rate',
        'respiratory_rate',
        'temperature',
        'arterial_pressure_monitor',
        'venous_pressure_monitor',
        'transmembrane_pressure_monitor',
        'blood_flow',
        'ultrafiltration',
        'heparin',
        'observations',
        'history',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
