<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use App\Services\WebpImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Team Members';

    protected static ?string $modelLabel = 'Team Member';

    protected static ?string $pluralModelLabel = 'Team Members';

    protected static ?int $navigationSort = 5;

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
                Forms\Components\Section::make('Member Profile')
                    ->description('Displayed in the "Meet the coordinating team" section on the Contact Us page.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Nakato Margaret'),
                        Forms\Components\TextInput::make('role')
                            ->label('Role / Title')
                            ->maxLength(255)
                            ->placeholder('e.g. Project Coordinator'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('name@organisation.org'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Repeater::make('phones')
                            ->label('Phone Numbers')
                            ->simple(
                                Forms\Components\TextInput::make('number')
                                    ->tel()
                                    ->placeholder('+256 772 587 427')
                                    ->required()
                            )
                            ->addActionLabel('Add phone number')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Visible on Website')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Portrait Photo')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Portrait Image')
                            ->disk('public')
                            ->directory('team')
                            ->image()
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Automatically converted to WebP for fast loading. Recommended aspect ratio 4:5.')
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'team')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('role')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
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
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
