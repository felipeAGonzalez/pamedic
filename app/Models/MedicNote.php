<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicNote extends Model
{
    protected $table = 'medic_note';

    protected $fillable = [
        'patient_id',
        'patient',
        'subjective',
        'objective',
        'prognosis',
        'plan',
        'date',
        'note_type',
        'user_id'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
