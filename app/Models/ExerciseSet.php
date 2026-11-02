<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_exercise_id',
        'set_number',
        'reps',
        'weight_kg',
        'duration_seconds',
        'distance_m',
        'rpe',
        'is_warmup',
    ];

    protected function casts(): array
    {
        return [
            'set_number' => 'integer',
            'reps' => 'integer',
            'weight_kg' => 'decimal:2',
            'duration_seconds' => 'integer',
            'distance_m' => 'decimal:2',
            'rpe' => 'integer',
            'is_warmup' => 'boolean',
        ];
    }

    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }
}
