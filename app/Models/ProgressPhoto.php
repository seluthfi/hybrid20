<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'taken_at',
        'photo_path',
        'thumb_path',
        'mime',
        'size_bytes',
        'pose_label',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'date',
            'size_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
