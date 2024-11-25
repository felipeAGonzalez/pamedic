<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NursePatient extends Model
{
    protected $table = 'nurse_patient';

    protected $fillable = [
        'active_patient_id',
        'user_id',
        'date',
        'history',
    ];

    public function active_patient()
    {
        return $this->belongsTo(ActivePatient::class);
    }
    public function nurse()
    {
        return $this->belongsTo(User::class)->where(['position' => 'nurse']);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
