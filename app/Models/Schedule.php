<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'schedule',
        'schedule_type',
    ];

    /**
     * Relación con la tabla pivote SchedulePatients
     */
    public function schedulePatients()
    {
        return $this->hasMany(SchedulePatients::class, 'schedules_id');
    }

    /**
     * Relación con los pacientes a través de la tabla pivote
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'schedules_patient', 'schedules_id', 'patient_id')->wherePivotNull('deleted_at');
    }
}
