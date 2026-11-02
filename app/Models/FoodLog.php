<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'food_id',
        'logged_at',
        'logged_on',
        'meal_type',
        'food_name_snapshot',
        'serving_name_snapshot',
        'serving_size_g_snapshot',
        'servings',
        'calories_kcal',
        'protein_g',
        'carbs_g',
        'fat_g',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
            'logged_on' => 'date',
            'serving_size_g_snapshot' => 'decimal:2',
            'servings' => 'decimal:2',
            'calories_kcal' => 'decimal:2',
            'protein_g' => 'decimal:2',
            'carbs_g' => 'decimal:2',
            'fat_g' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
