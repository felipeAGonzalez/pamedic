<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PostHemoDialysis extends Model
{
    protected $table = 'post_hemodialysis';

    protected $primaryKey = 'id';

    protected $fillable = [
        'patient_id',
        'final_ultrafiltration',
        'treated_blood',
        'ktv',
        'patient_temperature',
        'blood_pressure_stand',
        'blood_pressure_sit',
        'respiratory_rate',
        'heart_rate',
        'weight_out',
        'fall_risk',
        'history',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
