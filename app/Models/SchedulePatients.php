<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulePatients extends Model
{
    use HasFactory;

    protected $table = 'schedules_patient';
    protected $fillable = ['schedule_id', 'patient_id'];

    /**
     * Relación con el modelo Schedule
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Relación con el modelo Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
