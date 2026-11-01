<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledWorkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_week_id',
        'weekday',
        'workout_type',
        'title',
        'planned_distance_km',
        'planned_duration_minutes',
        'intensity_label',
        'sort_order',
        'instructions',
        'status',
        'rescheduled_to',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'planned_distance_km' => 'decimal:2',
            'planned_duration_minutes' => 'integer',
            'sort_order' => 'integer',
            'rescheduled_to' => 'date',
        ];
    }

    public function week(): BelongsTo
    {
        return $this->belongsTo(ProgramWeek::class, 'program_week_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(ScheduledWorkoutExercise::class);
    }

    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }
}
