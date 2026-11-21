<?php

namespace App\Filament\Resources\MediaFolderResource\Pages;

use App\Filament\Resources\MediaFolderResource;
use App\Models\MediaFolder;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaFolder extends CreateRecord
{
    protected static string $resource = MediaFolderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : MediaFolder::generateSlug((string) $data['name']);
        $data['created_by'] = auth()->id();

        return $data;
    }
}
