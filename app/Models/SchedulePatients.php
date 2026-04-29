<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulePatients extends Model
{
    use HasFactory;
    protected $table = 'schedules_patient';
    protected $fillable = ['schedules_id', 'patient_id', 'date', 'machine_id', 'created_at', 'updated_at'];

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

    public function schedules()
{
    return $this->hasMany(SchedulePatients::class);
}
}
