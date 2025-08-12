<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;
    protected $table = 'verification';

    protected $fillable = [
        'patient_id',
        'patient_name',
        'date_of_birth',
        'scheduled_procedure',
        'patient_id_badge',
        'nurse_sheet_identified',
        'hep_b_c_serology_m_4_months',
        'serology_m_6_months',
        'hd_machine_test_passed',
        'kit_per_vascular_access',
        'allergies',
        'dialyzer_per_prescription',
        'reprocessed_dialyzer_label',
        'vascular_access',
    ];
}
