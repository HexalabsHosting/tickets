<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\AutomationRules;

use App\Models\User;
use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\Admin\Resources\AutomationRules\Pages\CreateAutomationRule;
use FyWolf\Tickets\Filament\Admin\Resources\AutomationRules\Pages\EditAutomationRule;
use FyWolf\Tickets\Filament\Admin\Resources\AutomationRules\Pages\ListAutomationRules;
use FyWolf\Tickets\Models\AutomationRule;
use FyWolf\Tickets\Models\TicketCategory;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AutomationRuleResource extends Resource
{
    protected static ?string $model = AutomationRule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-robot';

    protected static string|\UnitEnum|null $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 96;

    public static function getNavigationLabel(): string
    {
        return trans('tickets::tickets.automation.nav_label');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('tickets::tickets.automation.rule', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('tickets::tickets.automation.rule', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()->columns(2)->schema([
                    TextInput::make('name')
                        ->label(trans('tickets::tickets.automation.name'))
                        ->required()
                        ->columnSpanFull(),
                    Select::make('trigger')
                        ->label(trans('tickets::tickets.automation.trigger'))
                        ->options([
                            'ticket_created' => trans('tickets::tickets.automation.trigger_ticket_created'),
                        ])
                        ->required()
                        ->default('ticket_created')
                        ->selectablePlaceholder(false),
                    TextInput::make('sort_order')
                        ->label(trans('tickets::tickets.automation.sort_order'))
                        ->numeric()
                        ->default(0),
                    Toggle::make('active')
                        ->label(trans('tickets::tickets.automation.active'))
                        ->default(true)
                        ->columnSpanFull(),
                ]),

                Section::make(trans('tickets::tickets.automation.conditions'))
                    ->description(trans('tickets::tickets.automation.conditions_help'))
                    ->schema([
                        Repeater::make('conditions')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('field')
                                    ->label(trans('tickets::tickets.automation.condition_field'))
                                    ->options([
                                        'priority'    => trans('tickets::tickets.priority'),
                                        'category_id' => trans('tickets::tickets.category'),
                                        'status'      => trans('tickets::tickets.status'),
                                        'assigned'    => trans('tickets::tickets.automation.condition_field_assigned'),
                                    ])
                                    ->required()
                                    ->live(),
                                Select::make('operator')
                                    ->label(trans('tickets::tickets.automation.condition_operator'))
                                    ->options([
                                        '='  => trans('tickets::tickets.automation.op_equals'),
                                        '!=' => trans('tickets::tickets.automation.op_not_equals'),
                                    ])
                                    ->required()
                                    ->default('='),
                                Select::make('value')
                                    ->label(trans('tickets::tickets.automation.condition_value'))
                                    ->required()
                                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get): array {
                                        return match ($get('field')) {
                                            'priority'    => collect(TicketPriority::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->toArray(),
                                            'status'      => collect(TicketStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->toArray(),
                                            'category_id' => TicketCategory::with('parent')->get()->mapWithKeys(fn ($c) => [$c->id => $c->full_name])->toArray(),
                                            'assigned'    => ['yes' => trans('tickets::tickets.automation.assigned_yes'), 'no' => trans('tickets::tickets.automation.assigned_no')],
                                            default       => [],
                                        };
                                    })
                                    ->searchable(),
                            ])
                            ->columns(3)
                            ->addActionLabel(trans('tickets::tickets.automation.add_condition'))
                            ->defaultItems(0),
                    ]),

                Section::make(trans('tickets::tickets.automation.actions'))
                    ->description(trans('tickets::tickets.automation.actions_help'))
                    ->schema([
                        Repeater::make('actions')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('type')
                                    ->label(trans('tickets::tickets.automation.action_type'))
                                    ->options([
                                        'assign_to'    => trans('tickets::tickets.automation.action_assign_to'),
                                        'set_priority' => trans('tickets::tickets.automation.action_set_priority'),
                                        'set_status'   => trans('tickets::tickets.automation.action_set_status'),
                                    ])
                                    ->required()
                                    ->live(),
                                Select::make('value')
                                    ->label(trans('tickets::tickets.automation.action_value'))
                                    ->required()
                                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get): array {
                                        return match ($get('type')) {
                                            'assign_to'    => User::orderBy('username')->pluck('username', 'id')->toArray(),
                                            'set_priority' => collect(TicketPriority::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->toArray(),
                                            'set_status'   => collect(TicketStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->toArray(),
                                            default        => [],
                                        };
                                    })
                                    ->searchable(),
                            ])
                            ->columns(2)
                            ->addActionLabel(trans('tickets::tickets.automation.add_action'))
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('tickets::tickets.automation.name'))
                    ->searchable()
                    ->grow(),
                TextColumn::make('trigger')
                    ->label(trans('tickets::tickets.automation.trigger'))
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => trans("tickets::tickets.automation.trigger_{$state}")),
                TextColumn::make('conditions')
                    ->label(trans('tickets::tickets.automation.conditions'))
                    ->badge()
                    ->state(fn (AutomationRule $r) => count($r->conditions) . ' ' . trans_choice('tickets::tickets.automation.condition_count', count($r->conditions)))
                    ->color('gray'),
                TextColumn::make('actions')
                    ->label(trans('tickets::tickets.automation.actions'))
                    ->badge()
                    ->state(fn (AutomationRule $r) => count($r->actions) . ' ' . trans_choice('tickets::tickets.automation.action_count', count($r->actions)))
                    ->color('gray'),
                IconColumn::make('active')
                    ->label(trans('tickets::tickets.automation.active'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->emptyStateIcon('tabler-robot')
            ->emptyStateHeading(trans('tickets::tickets.automation.no_rules'))
            ->emptyStateDescription('');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAutomationRules::route('/'),
            'create' => CreateAutomationRule::route('/create'),
            'edit'   => EditAutomationRule::route('/{record}/edit'),
        ];
    }
}
