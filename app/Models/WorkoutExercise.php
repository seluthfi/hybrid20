<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_id',
        'exercise_id',
        'exercise_name',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function calculateVolume(): float
    {
        return (float) $this->sets
            ->where('is_warmup', false)
            ->filter(fn ($s) => (float)$s->weight_kg > 0 && (int)$s->reps > 0)
            ->sum(fn ($s) => (float)$s->weight_kg * (int)$s->reps);
    }
}
