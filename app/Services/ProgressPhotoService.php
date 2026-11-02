<?php

namespace App\Services;

use App\Models\ProgressPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProgressPhotoService
{
    protected string $disk = 'local'; // Maps to storage/app/private

    /**
     * Store progress photo: strip EXIF, generate thumbnail, save privately.
     */
    public function storePhoto(User $user, UploadedFile $file, string $takenAt, string $poseLabel = 'front', ?string $notes = null): ProgressPhoto
    {
        $validMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = $file->getMimeType();

        if (! in_array($mime, $validMimes, true)) {
            throw new InvalidArgumentException('Format foto harus berupa JPG, PNG, atau WebP.');
        }

        // Limit size to 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new InvalidArgumentException('Ukuran foto maksimal adalah 10 MB.');
        }

        // Read image with GD to strip EXIF and re-encode
        $imageResource = $this->createImageResource($file);
        if (! $imageResource) {
            throw new InvalidArgumentException('Berkas gambar tidak dapat diproses.');
        }

        $uuid = (string) Str::uuid();
        $relativeDir = "progress-photos/{$user->id}";

        // Ensure private directory exists
        Storage::disk($this->disk)->makeDirectory($relativeDir);

        $mainPath = "{$relativeDir}/{$uuid}.jpg";
        $thumbPath = "{$relativeDir}/thumb_{$uuid}.jpg";

        // Save re-encoded JPEG (EXIF completely stripped by GD)
        ob_start();
        imagejpeg($imageResource, null, 88);
        $mainBinary = ob_get_clean();

        Storage::disk($this->disk)->put($mainPath, $mainBinary);
        $sizeBytes = strlen($mainBinary);

        // Generate thumbnail (max width 400px)
        $origWidth = imagesx($imageResource);
        $origHeight = imagesy($imageResource);

        $thumbWidth = min(400, $origWidth);
        $thumbHeight = (int) round(($origHeight / $origWidth) * $thumbWidth);

        $thumbResource = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumbResource, $imageResource, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);

        ob_start();
        imagejpeg($thumbResource, null, 80);
        $thumbBinary = ob_get_clean();

        Storage::disk($this->disk)->put($thumbPath, $thumbBinary);

        // Free memory
        imagedestroy($imageResource);
        imagedestroy($thumbResource);

        return ProgressPhoto::create([
            'user_id' => $user->id,
            'taken_at' => $takenAt,
            'photo_path' => $mainPath,
            'thumb_path' => $thumbPath,
            'mime' => 'image/jpeg',
            'size_bytes' => $sizeBytes,
            'pose_label' => $poseLabel,
            'notes' => $notes,
        ]);
    }

    /**
     * Delete progress photo and remove physical private files.
     */
    public function deletePhoto(ProgressPhoto $photo): bool
    {
        if ($photo->photo_path && Storage::disk($this->disk)->exists($photo->photo_path)) {
            Storage::disk($this->disk)->delete($photo->photo_path);
        }

        if ($photo->thumb_path && Storage::disk($this->disk)->exists($photo->thumb_path)) {
            Storage::disk($this->disk)->delete($photo->thumb_path);
        }

        return (bool) $photo->delete();
    }

    /**
     * Create GD resource from uploaded file.
     */
    protected function createImageResource(UploadedFile $file)
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();

        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => null,
        };
    }
}
