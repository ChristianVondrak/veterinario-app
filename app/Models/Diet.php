<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diet extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'summary',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
