<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BodyMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'measured_at',
        'weight_kg',
        'waist_cm',
        'chest_cm',
        'arm_cm',
        'thigh_cm',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'measured_at' => 'date',
            'weight_kg' => 'decimal:2',
            'waist_cm' => 'decimal:1',
            'chest_cm' => 'decimal:1',
            'arm_cm' => 'decimal:1',
            'thigh_cm' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
