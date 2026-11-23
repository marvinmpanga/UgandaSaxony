<?php

namespace App\Filament\Resources\ProfileTemplateResource\Pages;

use App\Filament\Resources\ProfileTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfileTemplates extends ListRecords
{
    protected static string $resource = ProfileTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
