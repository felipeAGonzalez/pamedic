<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialysisMonitoring extends Model
{
    protected $table = 'dialysis_monitoring';

    protected $fillable = [
        'patient_id',
        'date_hour',
        'machine_number',
        'session_number',
        'implantation',
        'vascular_access',
        'catheter_type',
        'needle_mesure',
        'collocation_date',
        'serology',
        'side',
        'serology_date',
        'blood_type',
        'allergy',
        'diagnostic',
        'history',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
