<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'species',
        'breed',
        'sex',
        'reproductive_status',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function diets(): HasMany
    {
        return $this->hasMany(Diet::class);
    }
}

