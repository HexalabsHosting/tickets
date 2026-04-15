<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\RelationManagers;

use FyWolf\Tickets\Models\TicketCategory;
use FyWolf\Tickets\Models\TicketCategoryField;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(trans_choice('tickets::tickets.custom_field', 1))
            ->pluralModelLabel(trans_choice('tickets::tickets.custom_field', 2))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label(trans('tickets::tickets.custom_field_label'))
                    ->searchable()
                    ->grow(),
                TextColumn::make('key')
                    ->label(trans('tickets::tickets.custom_field_key'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->label(trans('tickets::tickets.custom_field_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => trans("tickets::tickets.custom_field_type_{$state}")),
                IconColumn::make('required')
                    ->label(trans('tickets::tickets.custom_field_required'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['key'])) {
                            $data['key'] = Str::slug($data['label'], '_');
                        }

                        return $data;
                    }),
            ]);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('label')
                    ->label(trans('tickets::tickets.custom_field_label'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set, \Filament\Schemas\Components\Utilities\Get $get) {
                        if (!$get('key')) {
                            $set('key', Str::slug($state, '_'));
                        }
                    }),
                TextInput::make('key')
                    ->label(trans('tickets::tickets.custom_field_key'))
                    ->required()
                    ->alphaDash()
                    ->helperText(trans('tickets::tickets.custom_field_key_help')),
                Select::make('type')
                    ->label(trans('tickets::tickets.custom_field_type'))
                    ->options([
                        'text'     => trans('tickets::tickets.custom_field_type_text'),
                        'number'   => trans('tickets::tickets.custom_field_type_number'),
                        'textarea' => trans('tickets::tickets.custom_field_type_textarea'),
                        'select'   => trans('tickets::tickets.custom_field_type_select'),
                        'toggle'   => trans('tickets::tickets.custom_field_type_toggle'),
                    ])
                    ->required()
                    ->default('text')
                    ->live(),
                TextInput::make('sort_order')
                    ->label(trans('tickets::tickets.custom_field_sort'))
                    ->numeric()
                    ->default(0),
                Toggle::make('required')
                    ->label(trans('tickets::tickets.custom_field_required'))
                    ->columnSpanFull(),
                Repeater::make('options')
                    ->label(trans('tickets::tickets.custom_field_options'))
                    ->schema([
                        TextInput::make('label')->label(trans('tickets::tickets.custom_field_option_label'))->required(),
                        TextInput::make('value')->label(trans('tickets::tickets.custom_field_option_value'))->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('type') === 'select'),
            ]);
    }
}
