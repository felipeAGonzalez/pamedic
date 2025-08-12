<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOut extends Model
{
    use HasFactory;
    protected $table = 'time_out';

    protected $fillable = [
        'patient_id',
        'patient_identification',
        'scheduled_procedure',
        'dialysis_prescription',
        'dialyzer_check',
        'bleeding_check',
        'vascular_access_check',
    ];
}
