<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoubleVerification extends Model
{
    protected $table = 'double_verifications';

    protected $fillable = [
        'patient_id',
        'correct_medication',
        'correct_dosage',
        'correct_dilution',
        'correct_time',
        'expiration_verification',
        'medication_record',
        'patient_education',
        'medication_identification',
        'nurse_id',
        'history',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function nurse()
    {
        return $this->belongsTo(User::class);
    }
}
