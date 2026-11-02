<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'serving_name',
        'serving_size_g',
        'calories_kcal',
        'protein_g',
        'carbs_g',
        'fat_g',
        'is_favorite',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'serving_size_g' => 'decimal:2',
            'calories_kcal' => 'decimal:2',
            'protein_g' => 'decimal:2',
            'carbs_g' => 'decimal:2',
            'fat_g' => 'decimal:2',
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }
}
