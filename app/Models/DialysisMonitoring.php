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
        'implantation',
        'fistula',
        'needle_mesure',
        'collocation_date',
        'serology',
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
