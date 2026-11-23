<?php

namespace App\Filament\Resources\ProfileTemplateResource\Pages;

use App\Filament\Resources\ProfileTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfileTemplate extends CreateRecord
{
    protected static string $resource = ProfileTemplateResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
