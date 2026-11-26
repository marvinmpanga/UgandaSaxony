<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResourceResource\Pages;
use App\Models\ResourceItem;
use App\Services\WebpImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceItem::class;

    protected static ?string $slug = 'resource-centre';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Resource Centre';

    protected static ?string $modelLabel = 'Resource';

    protected static ?string $pluralModelLabel = 'Resource Centre';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('pages.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('pages.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('pages.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('pages.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Resource Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', ResourceItem::generateSlug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->placeholder('Auto-generated from title')
                            ->required(fn (string $operation): bool => $operation === 'edit')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('category')
                            ->datalist([
                                'Uganda Information',
                                'Saxony Information',
                                'Funding Opportunities',
                                'Guides & Reports',
                                'General Information',
                            ])
                            ->placeholder('e.g. Guides & Reports, Funding Opportunities')
                            ->maxLength(100),
                        Forms\Components\Select::make('status')
                            ->options(fn (?ResourceItem $record): array => auth()->user()?->can('pages.publish') || $record?->status === 'published'
                                ? ['draft' => 'Draft', 'published' => 'Published']
                                : ['draft' => 'Draft']
                            )
                            ->in(fn (?ResourceItem $record): array => auth()->user()?->can('pages.publish') || $record?->status === 'published'
                                ? ['draft', 'published']
                                : ['draft']
                            )
                            ->default('draft')
                            ->required(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published At'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('body')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('resources/attachments')
                            ->fileAttachmentsVisibility('public'),
                    ]),
                Forms\Components\Section::make('Media & Downloadable File')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->disk('public')
                            ->directory('resources/covers')
                            ->image()
                            ->maxSize(10240)
                            ->imageEditor()
                            ->helperText('Automatically converted to WebP.')
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'resources/covers')),
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Downloadable Document (Optional)')
                            ->disk('public')
                            ->directory('resources/documents')
                            ->maxSize(20480)
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_url')
                    ->label('Cover')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn (ResourceItem $record): bool => filled($record->attachment_path)),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
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
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
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
