<?php

namespace App\Filament\Resources\ProfileTemplateResource\Pages;

use App\Filament\Resources\ProfileTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfileTemplate extends EditRecord
{
    protected static string $resource = ProfileTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
