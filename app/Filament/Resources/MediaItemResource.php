<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaItemResource\Pages;
use App\Models\MediaItem;
use App\Services\WebpImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaItemResource extends Resource
{
    protected static ?string $model = MediaItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationGroup = 'Media & Gallery';

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('File & Metadata')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('path')
                            ->label('File')
                            ->disk('public')
                            ->directory('media')
                            ->storeFileNamesIn('original_name')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                                'image/bmp',
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'application/vnd.oasis.opendocument.text',
                            ])
                            ->maxSize(10240)
                            ->required()
                            ->helperText('Uploaded raster images are automatically converted to WebP. Max size: 10 MB.')
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'media'))
                            ->columnSpanFull(),
                        Forms\Components\Select::make('folder_id')
                            ->label('Folder')
                            ->relationship('folder', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('collection')
                            ->default('general')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('alt_text')
                            ->label('Alt Text')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('caption')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Preview')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('folder.name')
                    ->label('Folder')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('size')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('folder_id')
                    ->label('Folder')
                    ->relationship('folder', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListMediaItems::route('/'),
            'create' => Pages\CreateMediaItem::route('/create'),
            'edit' => Pages\EditMediaItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
