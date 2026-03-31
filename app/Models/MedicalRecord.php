<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'evaluated_at',
        'weight_kg',
        'bcs',
        'iris_stage',
        'creatinine',
        'bun',
        'phosphorus',
        'potassium',
        'sodium',
        'bicarbonate',
        'urine_density',
        'proteinuria',
        'appetite',
        'activity_level',
        'physiological_status',
        'special_considerations',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

