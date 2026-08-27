<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\Media;
use App\Models\User;
use App\Models\Website;
use App\Services\Website\QuotaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function __construct(private readonly QuotaService $quotas) {}

    public function store(User $user, UploadedFile $file, ?Website $website = null, ?string $folder = null): Media
    {
        $this->quotas->assertCanUpload($user, $file->getSize() ?: 0);

        $disk = (string) config('filesystems.default', 'public');
        $dir = trim('media/'.$user->id.'/'.($folder ? Str::slug($folder) : date('Y/m')), '/');

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $name.'-'.Str::lower(Str::random(6)).'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($dir, $fileName, ['disk' => $disk]);

        [$width, $height] = $this->dimensions($file);

        return Media::create([
            'user_id' => $user->id,
            'website_id' => $website?->id,
            'name' => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getClientMimeType(),
            'type' => $this->typeFor($file),
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'folder' => $folder,
        ]);
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    private function typeFor(UploadedFile $file): string
    {
        $mime = (string) $file->getClientMimeType();

        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            default => 'document',
        };
    }

    /** @return array{0: ?int, 1: ?int} */
    private function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getClientMimeType(), 'image/')) {
            return [null, null];
        }

        try {
            $info = @getimagesize($file->getRealPath());

            return $info ? [(int) $info[0], (int) $info[1]] : [null, null];
        } catch (\Throwable) {
            return [null, null];
        }
    }
}
