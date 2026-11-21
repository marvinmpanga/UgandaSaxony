<?php

namespace App\Filament\Resources\MediaItemResource\Pages;

use App\Filament\Resources\MediaItemResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMediaItem extends CreateRecord
{
    protected static string $resource = MediaItemResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $disk = 'public';
        $path = (string) ($data['path'] ?? '');

        $data['disk'] = $disk;
        $data['file_name'] = basename($path);
        $data['original_name'] = filled($data['original_name'] ?? null) ? $data['original_name'] : basename($path);
        $data['uploaded_by'] = auth()->id();

        if ($path !== '' && Storage::disk($disk)->exists($path)) {
            $data['mime_type'] = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
            $data['size'] = Storage::disk($disk)->size($path);

            if (str_starts_with($data['mime_type'], 'image/')) {
                $dimensions = @getimagesize(Storage::disk($disk)->path($path));
                if (is_array($dimensions)) {
                    $data['width'] = $dimensions[0];
                    $data['height'] = $dimensions[1];
                }
            }
        } else {
            $data['mime_type'] ??= 'application/octet-stream';
            $data['size'] ??= 0;
        }

        return $data;
    }
}
