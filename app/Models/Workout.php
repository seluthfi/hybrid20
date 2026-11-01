<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scheduled_workout_id',
        'workout_type',
        'title',
        'performed_at',
        'performed_on',
        'duration_seconds',
        'distance_km',
        'pace_seconds_per_km',
        'steps',
        'rpe',
        'status',
        'body_condition',
        'pain_flag',
        'pain_notes',
        'general_notes',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'performed_on' => 'date',
            'duration_seconds' => 'integer',
            'distance_km' => 'decimal:2',
            'pace_seconds_per_km' => 'integer',
            'steps' => 'integer',
            'rpe' => 'integer',
            'pain_flag' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduledWorkout(): BelongsTo
    {
        return $this->belongsTo(ScheduledWorkout::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class);
    }

    public function calculateVolume(): float
    {
        $total = 0.0;
        foreach ($this->exercises as $exercise) {
            $total += $exercise->calculateVolume();
        }
        return $total;
    }
}
