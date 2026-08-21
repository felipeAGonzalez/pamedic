<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleCloneBatch extends Model
{
    protected $fillable = [
        'source_year',
        'source_week',
        'target_year',
        'target_week',
        'snapshot_hash',
        'records_count',
        'status',
        'cloned_by',
        'undone_by',
        'undone_at',
    ];

    protected $casts = [
        'undone_at' => 'datetime',
    ];

    public function records()
    {
        return $this->hasMany(SchedulePatients::class, 'clone_batch_id');
    }
}
