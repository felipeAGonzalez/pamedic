<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulePatients extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schedules_patient';

    protected $fillable = ['schedules_id', 'patient_id', 'date', 'machine_id', 'continue_schedule', 'clone_batch_id', 'created_at', 'updated_at'];

    protected $casts = [
        'date' => 'date',
        'continue_schedule' => 'boolean',
    ];

    public function cloneBatch()
    {
        return $this->belongsTo(ScheduleCloneBatch::class, 'clone_batch_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedules_id');
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
