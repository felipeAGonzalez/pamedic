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
        return $this->hasMany(SchedulePatients::class, 'schedule_id');
    }

    /**
     * Relación con los pacientes a través de la tabla pivote
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'schedule_patients', 'schedule_id', 'patient_id');
    }
}
