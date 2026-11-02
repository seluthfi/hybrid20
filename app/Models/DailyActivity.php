<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_date',
        'steps',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'steps' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
