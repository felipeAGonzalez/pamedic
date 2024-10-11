<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationRisk extends Model
{
    protected $table = 'evaluation_risk';

    protected $fillable = [
        'patient_id',
        'fase',
        'hour',
        'result',
        'fall_risk_trans',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
