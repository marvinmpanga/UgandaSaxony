<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use App\Services\WebpImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Partnerships';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

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
                Forms\Components\Section::make('Partner Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Partner::generateSlug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->placeholder('Auto-generated from name')
                            ->required(fn (string $operation): bool => $operation === 'edit')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('category')
                            ->datalist([
                                'Primary Schools',
                                'Secondary Schools',
                                'Vocational Institutes',
                                'Non Government Organisations',
                                'Community Based Organisations',
                            ])
                            ->helperText('School/Vocational categories appear on Partner Schools; others appear on Partner Organisations.')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('country')
                            ->datalist(['Uganda', 'Germany (Saxony)'])
                            ->maxLength(100),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website_url')
                            ->label('Website URL')
                            ->url()
                            ->rule('url:http,https')
                            ->maxLength(500),
                        Forms\Components\FileUpload::make('logo_url')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('partners/logos')
                            ->image()
                            ->maxSize(5120)
                            ->helperText('Automatically optimized to WebP.')
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'partners/logos', maxWidth: 600))
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('partners/attachments')
                            ->fileAttachmentsVisibility('public')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Visibility & Ordering')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Toggle::make('in_partnership')
                            ->label('Active Partnership')
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Visible on Website')
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
                Tables\Columns\ImageColumn::make('logo_image_url')
                    ->label('Logo')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('in_partnership')
                    ->label('In Partnership')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('in_partnership')
                    ->label('In Partnership'),
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
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
