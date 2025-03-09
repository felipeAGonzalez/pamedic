<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patient';

    protected $casts = [
        'birth_date' => 'date',
        'date_entry' => 'date',
    ];
    protected $fillable = ['expedient_number','name','last_name','contact_phone_number','last_name_two','gender','birth_date','date_entry','height','photo',];


    public function activePatient()
    {
        return $this->hasOne(ActivePatient::class, 'patient_id')->orderby('date','desc');
    }
    public function medicNote()
    {
        return $this->hasOne(MedicNote::class, 'patient_id')->whereDate('date', now());
    }
}
