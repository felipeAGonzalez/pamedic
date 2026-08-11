<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulePatients extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schedules_patient';

    protected $fillable = ['schedules_id', 'patient_id', 'date', 'machine_id', 'created_at', 'updated_at'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
