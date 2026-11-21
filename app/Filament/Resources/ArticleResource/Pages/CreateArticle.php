<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : Article::generateSlug((string) $data['title']);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if (($data['status'] ?? 'draft') === 'published') {
            $data['published_at'] ??= now();
        }

        return $data;
    }
}
