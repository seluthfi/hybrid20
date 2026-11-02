<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'height_cm',
        'starting_weight_kg',
        'target_weight_kg',
        'daily_calorie_target',
        'protein_target_g',
        'carbs_target_g',
        'fat_target_g',
        'daily_steps_target',
        'timezone',
        'weight_unit',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'decimal:1',
            'starting_weight_kg' => 'decimal:2',
            'target_weight_kg' => 'decimal:2',
            'daily_calorie_target' => 'integer',
            'protein_target_g' => 'integer',
            'carbs_target_g' => 'integer',
            'fat_target_g' => 'integer',
            'daily_steps_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
