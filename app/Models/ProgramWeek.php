<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'week_number',
        'calendar_position',
        'is_repeat',
        'phase_label',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'week_number' => 'integer',
            'calendar_position' => 'integer',
            'is_repeat' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function scheduledWorkouts(): HasMany
    {
        return $this->hasMany(ScheduledWorkout::class);
    }
}
