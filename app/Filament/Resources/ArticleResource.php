<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
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

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Events & News';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\Section::make('Article Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Article::generateSlug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->placeholder('Auto-generated from title')
                            ->required(fn (string $operation): bool => $operation === 'edit')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('tag')
                            ->datalist(['Event', 'News', 'Announcement', 'Story', 'Exchange'])
                            ->maxLength(100),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('event_date')
                            ->label('Event Date'),
                        Forms\Components\Select::make('status')
                            ->options(fn (?Article $record): array => auth()->user()?->can('pages.publish') || $record?->status === 'published'
                                ? ['draft' => 'Draft', 'published' => 'Published']
                                : ['draft' => 'Draft']
                            )
                            ->in(fn (?Article $record): array => auth()->user()?->can('pages.publish') || $record?->status === 'published'
                                ? ['draft', 'published']
                                : ['draft']
                            )
                            ->default('draft')
                            ->helperText('Published articles appear on the Homepage Hero and Events & News page.')
                            ->required(),
                        Forms\Components\Toggle::make('featured')
                            ->label('Prioritize First on Hero')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published At'),
                        Forms\Components\Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('body')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('articles/attachments')
                            ->fileAttachmentsVisibility('public'),
                    ]),
                Forms\Components\Section::make('Cover Image & Gallery')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->disk('public')
                            ->directory('articles/covers')
                            ->image()
                            ->maxSize(10240)
                            ->imageEditor()
                            ->helperText('Automatically converted to WebP for faster loading.')
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'articles/covers')),
                        Forms\Components\Repeater::make('images')
                            ->label('Article Gallery Images')
                            ->relationship('images')
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->reorderable()
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                Forms\Components\FileUpload::make('path')
                                    ->label('Image')
                                    ->disk('public')
                                    ->directory('articles/gallery')
                                    ->image()
                                    ->maxSize(10240)
                                    ->required()
                                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'articles/gallery')),
                                Forms\Components\TextInput::make('caption')
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_url')
                    ->label('Cover')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('tag')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('featured')
                    ->label('Hero')
                    ->boolean(),
                Tables\Columns\TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Featured on Hero'),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
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
