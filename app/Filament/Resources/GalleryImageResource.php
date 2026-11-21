<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryImageResource\Pages;
use App\Models\GalleryImage;
use App\Services\WebpImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Media & Gallery';

    protected static ?string $navigationLabel = 'Gallery Images';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('media.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('media.upload') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('media.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('media.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Gallery Image')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('Image')
                            ->disk('public')
                            ->directory('gallery')
                            ->image()
                            ->imageEditor()
                            ->required()
                            ->helperText('Automatically converted to WebP for fast gallery loading.')
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'gallery'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('caption')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('album')
                            ->datalist(fn (): array => GalleryImage::query()->whereNotNull('album')->distinct()->orderBy('album')->pluck('album')->all())
                            ->maxLength(100),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible in Gallery')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('Preview')
                    ->square(),
                Tables\Columns\TextColumn::make('caption')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('album')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Visible'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visible'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryImages::route('/'),
            'create' => Pages\CreateGalleryImage::route('/create'),
            'edit' => Pages\EditGalleryImage::route('/{record}/edit'),
        ];
    }
}
