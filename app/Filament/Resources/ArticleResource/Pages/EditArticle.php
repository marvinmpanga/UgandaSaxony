<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPublic')
                ->label('View Article')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => '/events/'.$this->record->slug)
                ->visible(fn (): bool => $this->record->status === 'published')
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        if (($data['status'] ?? 'draft') === 'published' && ! $this->record->published_at) {
            $data['published_at'] ??= now();
        }

        return $data;
    }
}
