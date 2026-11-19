<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientScheduleSlot extends Model
{
    protected $fillable = [
        'patient_id',
        'weekday',
        'schedules_id',
        'machine_id',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'active' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedules_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function records()
    {
        return $this->hasMany(SchedulePatients::class, 'patient_schedule_slot_id');
    }
}
