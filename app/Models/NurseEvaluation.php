<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NurseEvaluation extends Model
{
    protected $table = 'nurse_evaluations';

    protected $fillable = [
        'patient_id',
        'nurse_valuation',
        'fase',
        'nurse_intervention',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
