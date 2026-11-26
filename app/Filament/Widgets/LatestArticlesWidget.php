<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestArticlesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Events & News (Live on Homepage Hero)';

    public static function canView(): bool
    {
        return auth()->user()?->can('pages.view') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Article::query()
                    ->orderByDesc('featured')
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_url')
                    ->label('Cover')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(55)
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('tag')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('featured')
                    ->label('Hero Priority')
                    ->boolean(),
                Tables\Columns\TextColumn::make('event_date')
                    ->label('Date')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (): bool => auth()->user()?->can('pages.edit') ?? false),
                Tables\Actions\Action::make('view')
                    ->label('View Live')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Article $record): string => '/events/'.$record->slug)
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record): bool => $record->status === 'published'),
            ]);
    }
}
