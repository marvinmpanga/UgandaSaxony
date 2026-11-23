<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileTemplateResource\Pages;
use App\Models\ProfileTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProfileTemplateResource extends Resource
{
    protected static ?string $model = ProfileTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Profile Templates';

    protected static ?string $modelLabel = 'Profile Template';

    protected static ?string $pluralModelLabel = 'Profile Templates';

    protected static ?int $navigationSort = 4;

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
                Forms\Components\Section::make('Template Details')
                    ->description('Displayed on the Join the Partnership page under "Download a profile template".')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Template for Uganda-Saxony Organisation Profile')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Short instructions on who should fill out and submit this template.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link_label')
                            ->label('Button Label')
                            ->maxLength(100)
                            ->placeholder('e.g. Download (DOCX) or Download (PDF)')
                            ->helperText('Optional. Automatically detected from the uploaded file extension if left blank.'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Visible on Website')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Template File')
                    ->description('Upload a downloadable document directly, or provide an external URL as a fallback.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload Document')
                            ->disk('public')
                            ->directory('templates')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'application/vnd.oasis.opendocument.text',
                                'application/zip',
                            ])
                            ->helperText('Supported formats: DOCX, DOC, PDF, XLSX, XLS, PPTX, ODT, ZIP.'),
                        Forms\Components\TextInput::make('external_url')
                            ->label('External Download URL (Optional)')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://...')
                            ->helperText('Used only if no file is uploaded above.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(55),
                Tables\Columns\TextColumn::make('resolved_link_label')
                    ->label('Button Label')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('Uploaded File')
                    ->boolean()
                    ->getStateUsing(fn (ProfileTemplate $record): bool => filled($record->file_path)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visible on Website'),
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
            'index' => Pages\ListProfileTemplates::route('/'),
            'create' => Pages\CreateProfileTemplate::route('/create'),
            'edit' => Pages\EditProfileTemplate::route('/{record}/edit'),
        ];
    }
}
