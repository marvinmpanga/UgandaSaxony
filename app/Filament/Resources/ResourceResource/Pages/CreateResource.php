<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Filament\Resources\ResourceResource;
use App\Models\ResourceItem;
use Filament\Resources\Pages\CreateRecord;

class CreateResource extends CreateRecord
{
    protected static string $resource = ResourceResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : ResourceItem::generateSlug((string) $data['title']);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if (($data['status'] ?? 'draft') === 'published') {
            $data['published_at'] ??= now();
        }

        return $data;
    }
}
