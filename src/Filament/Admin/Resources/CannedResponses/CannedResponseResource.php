<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\CannedResponses;

use App\Filament\Components\Tables\Columns\DateTimeColumn;
use FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\Pages\CreateCannedResponse;
use FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\Pages\EditCannedResponse;
use FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\Pages\ListCannedResponses;
use FyWolf\Tickets\Models\CannedResponse;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CannedResponseResource extends Resource
{
    protected static ?string $model = CannedResponse::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-messages';

    protected static ?string $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 98;

    public static function getNavigationLabel(): string
    {
        return trans('tickets::tickets.canned_responses');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('tickets::tickets.canned_response', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('tickets::tickets.canned_response', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(trans('tickets::tickets.canned_response_name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->label(trans('tickets::tickets.canned_response_category'))
                    ->placeholder(trans('tickets::tickets.canned_response_category_placeholder'))
                    ->maxLength(255),
                MarkdownEditor::make('content')
                    ->label(trans('tickets::tickets.canned_response_content'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('tickets::tickets.canned_response_name'))
                    ->searchable()
                    ->sortable()
                    ->grow(),
                TextColumn::make('category')
                    ->label(trans('tickets::tickets.canned_response_category'))
                    ->badge()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                DateTimeColumn::make('updated_at')
                    ->label(trans('tickets::tickets.updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->emptyStateIcon('tabler-messages')
            ->emptyStateHeading(trans('tickets::tickets.no_canned_responses'))
            ->emptyStateDescription('');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCannedResponses::route('/'),
            'create' => CreateCannedResponse::route('/create'),
            'edit'   => EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
