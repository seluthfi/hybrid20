<?php

namespace Database\Factories;

use App\Models\ProgressPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgressPhotoFactory extends Factory
{
    protected $model = ProgressPhoto::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'taken_at' => now()->toDateString(),
            'photo_path' => 'progress-photos/demo-photo.jpg',
            'thumb_path' => 'progress-photos/demo-thumb.jpg',
            'mime' => 'image/jpeg',
            'size_bytes' => 102400,
            'pose_label' => 'front',
            'notes' => null,
        ];
    }
}
