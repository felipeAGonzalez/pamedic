<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreHemodialysis extends Model
{
    protected $table = 'pre_hemodialysis';

    protected $fillable = [
        'patient_id',
        'previous_initial_weight',
        'previous_final_weight',
        'previous_weight_gain',
        'initial_weight',
        'dry_weight',
        'weight_gain',
        'reuse_number',
        'sitting_blood_pressure',
        'standing_blood_pressure',
        'body_temperature',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'conductivity',
        'dextrostix',
        'itchiness',
        'pallor_skin',
        'edema',
        'vascular_access_conditions',
        'fall_risk',
        'observations',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
