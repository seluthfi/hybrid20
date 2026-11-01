<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledWorkoutExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_workout_id',
        'exercise_id',
        'exercise_name',
        'target_sets',
        'target_reps',
        'target_duration_seconds',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_sets' => 'integer',
            'target_reps' => 'integer',
            'target_duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scheduledWorkout(): BelongsTo
    {
        return $this->belongsTo(ScheduledWorkout::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
