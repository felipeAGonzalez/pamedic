<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $table = 'medicines';

    protected $fillable = [
        'name',
        'route_administration',
    ];

    public function activePatient()
    {
        return $this->hasMany(MedicationAdministration::class, 'medicine_id');
    }
}
