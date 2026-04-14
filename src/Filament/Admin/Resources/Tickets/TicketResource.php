<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets;

use App\Filament\Admin\Resources\Servers\Pages\EditServer;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Components\Tables\Columns\DateTimeColumn;
use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages\CreateTicket;
use FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages\EditTicket;
use FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages\ListTickets;
use FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages\ViewTicket;
use FyWolf\Tickets\Filament\Admin\Resources\Tickets\RelationManagers\MessagesRelationManager;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsOverviewWidget;
use FyWolf\Tickets\Filament\Components\Actions\AnswerAction;
use FyWolf\Tickets\Filament\Components\Actions\AssignToMeAction;
use FyWolf\Tickets\Filament\Components\Actions\CloseAction;
use FyWolf\Tickets\Filament\Components\Actions\ReopenAction;
use FyWolf\Tickets\Filament\Components\Actions\WaitingAction;
use FyWolf\Tickets\Models\Ticket;
use FyWolf\Tickets\Models\TicketCategory;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Markdown;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return trans_choice('tickets::tickets.ticket', 2);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('tickets::tickets.ticket', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('tickets::tickets.ticket', 2);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Ticket::whereNot('status', TicketStatus::Closed->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(trans_choice('tickets::tickets.title', 1))
                    ->description(fn (Ticket $ticket) => Markdown::inline($ticket->description ?? ''))
                    ->sortable()
                    ->searchable()
                    ->grow(),
                TextColumn::make('category.name')
                    ->label(trans('tickets::tickets.category'))
                    ->badge()
                    ->color(fn (Ticket $ticket) => $ticket->category?->color ?? 'gray')
                    ->icon(fn (Ticket $ticket) => $ticket->category?->icon)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label(trans('tickets::tickets.priority'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(trans('tickets::tickets.status'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('sla')
                    ->label(trans('tickets::tickets.sla'))
                    ->badge()
                    ->state(function (Ticket $ticket): ?string {
                        if (!config('tickets.sla.enabled') || !$ticket->status->isOpen()) {
                            return null;
                        }
                        $hours = $ticket->getSlaRemainingHours();
                        if ($hours === null) {
                            return null;
                        }
                        if ($hours < 0) {
                            return trans('tickets::tickets.sla_overdue');
                        }
                        if ($hours < 2) {
                            return trans('tickets::tickets.sla_critical', ['hours' => round($hours, 1)]);
                        }

                        return trans('tickets::tickets.sla_ok', ['hours' => round($hours, 1)]);
                    })
                    ->color(function (Ticket $ticket): string {
                        $hours = $ticket->getSlaRemainingHours();
                        if ($hours === null || !$ticket->status->isOpen()) {
                            return 'gray';
                        }

                        return $hours < 0 ? 'danger' : ($hours < 2 ? 'warning' : 'success');
                    })
                    ->visible(config('tickets.sla.enabled', false))
                    ->toggleable(),
                TextColumn::make('assignedUser.username')
                    ->label(trans('tickets::tickets.assigned_to'))
                    ->icon('tabler-user')
                    ->placeholder(trans('tickets::tickets.noone'))
                    ->url(fn (Ticket $ticket) => $ticket->assignedUser && auth()->user()->can('update user', $ticket->assignedUser) ? EditUser::getUrl(['record' => $ticket->assignedUser]) : null)
                    ->toggleable(),
                TextColumn::make('server.name')
                    ->label(trans('tickets::tickets.server'))
                    ->icon('tabler-brand-docker')
                    ->url(fn (Ticket $ticket) => auth()->user()->can('update server', $ticket->server) ? EditServer::getUrl(['record' => $ticket->server]) : null)
                    ->toggleable(),
                TextColumn::make('author.username')
                    ->label(trans('tickets::tickets.created_by'))
                    ->icon('tabler-user')
                    ->placeholder(trans('tickets::tickets.unknown'))
                    ->url(fn (Ticket $ticket) => $ticket->author && auth()->user()->can('update user', $ticket->author) ? EditUser::getUrl(['record' => $ticket->author]) : null)
                    ->toggleable(),
                DateTimeColumn::make('created_at')
                    ->label(trans('tickets::tickets.created_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(),
                DateTimeColumn::make('closed_at')
                    ->label(trans('tickets::tickets.closed_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('tickets::tickets.status'))
                    ->options(TicketStatus::class),
                SelectFilter::make('priority')
                    ->label(trans('tickets::tickets.priority'))
                    ->options(TicketPriority::class),
                SelectFilter::make('category_id')
                    ->label(trans('tickets::tickets.category'))
                    ->options(fn () => TicketCategory::with('parent')
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($cat) => [$cat->id => $cat->full_name])
                    )
                    ->searchable(),
                SelectFilter::make('assigned_user_id')
                    ->label(trans('tickets::tickets.assigned_to'))
                    ->relationship('assignedUser', 'username')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('server_id')
                    ->label(trans('tickets::tickets.server'))
                    ->relationship('server', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->hidden(fn ($record) => static::getEditAuthorizationResponse($record)->allowed()),
                EditAction::make(),
                AnswerAction::make(),
                AssignToMeAction::make(),
                WaitingAction::make(),
                ReopenAction::make(),
                CloseAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->groups([
                Group::make('category.name')->label(trans('tickets::tickets.category')),
                Group::make('priority')->label(trans('tickets::tickets.priority')),
                Group::make('status')->label(trans('tickets::tickets.status')),
                Group::make('server.name')->label(trans('tickets::tickets.server')),
                Group::make('author.username')->label(trans('tickets::tickets.created_by')),
                Group::make('assignedUser.username')->label(trans('tickets::tickets.assigned_to')),
            ])
            ->emptyStateIcon('tabler-ticket')
            ->emptyStateDescription('')
            ->emptyStateHeading(trans('tickets::tickets.no_tickets'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->label(trans_choice('tickets::tickets.title', 1))
                    ->required()
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label(trans('tickets::tickets.category'))
                    ->options(fn () => TicketCategory::groupedOptions())
                    ->searchable(),
                Select::make('priority')
                    ->label(trans('tickets::tickets.priority'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketPriority::class)
                    ->default(TicketPriority::Normal),
                Select::make('status')
                    ->label(trans('tickets::tickets.status'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketStatus::class)
                    ->default(TicketStatus::Open),
                Select::make('server_id')
                    ->label(trans('tickets::tickets.server'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->relationship('server', 'name'),
                Select::make('assigned_user_id')
                    ->label(trans('tickets::tickets.assigned_to'))
                    ->relationship('assignedUser', 'username')
                    ->searchable()
                    ->preload()
                    ->placeholder(trans('tickets::tickets.noone')),
                MarkdownEditor::make('description')
                    ->label(trans('tickets::tickets.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2, 'lg' => 4])
                    ->schema([
                        TextEntry::make('title')
                            ->label(trans_choice('tickets::tickets.title', 1))
                            ->columnSpanFull(),
                        TextEntry::make('category.name')
                            ->label(trans('tickets::tickets.category'))
                            ->badge()
                            ->color(fn (Ticket $ticket) => $ticket->category?->color ?? 'gray')
                            ->icon(fn (Ticket $ticket) => $ticket->category?->icon)
                            ->placeholder('—'),
                        TextEntry::make('priority')
                            ->label(trans('tickets::tickets.priority'))
                            ->badge(),
                        TextEntry::make('status')
                            ->label(trans('tickets::tickets.status'))
                            ->badge(),
                        TextEntry::make('assignedUser.username')
                            ->label(trans('tickets::tickets.assigned_to'))
                            ->icon('tabler-user')
                            ->placeholder(trans('tickets::tickets.noone'))
                            ->url(fn (Ticket $ticket) => $ticket->assignedUser && auth()->user()->can('update user', $ticket->assignedUser) ? EditUser::getUrl(['record' => $ticket->assignedUser]) : null),
                        TextEntry::make('server.name')
                            ->label(trans('tickets::tickets.server'))
                            ->icon('tabler-brand-docker')
                            ->url(fn (Ticket $ticket) => auth()->user()->can('update server', $ticket->server) ? EditServer::getUrl(['record' => $ticket->server]) : null),
                        TextEntry::make('server.user.username')
                            ->label(trans('tickets::tickets.owner'))
                            ->icon('tabler-user')
                            ->url(fn (Ticket $ticket) => auth()->user()->can('update user', $ticket->server->user) ? EditUser::getUrl(['record' => $ticket->server->user]) : null),
                        TextEntry::make('author.username')
                            ->label(trans('tickets::tickets.created_by'))
                            ->icon('tabler-user')
                            ->placeholder(trans('tickets::tickets.unknown'))
                            ->url(fn (Ticket $ticket) => $ticket->author && auth()->user()->can('update user', $ticket->author) ? EditUser::getUrl(['record' => $ticket->author]) : null),
                        TextEntry::make('created_at')
                            ->label(trans('tickets::tickets.created_at'))
                            ->since(timezone: auth()->user()->timezone ?? config('app.timezone', 'UTC'))
                            ->dateTimeTooltip(timezone: auth()->user()->timezone ?? config('app.timezone', 'UTC')),
                        TextEntry::make('closed_at')
                            ->label(trans('tickets::tickets.closed_at'))
                            ->since(timezone: auth()->user()->timezone ?? config('app.timezone', 'UTC'))
                            ->dateTimeTooltip(timezone: auth()->user()->timezone ?? config('app.timezone', 'UTC'))
                            ->placeholder('—')
                            ->visible(fn (Ticket $ticket) => $ticket->closed_at !== null),
                    ]),
                Section::make(trans('tickets::tickets.description'))
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->markdown()
                            ->placeholder(trans('tickets::tickets.no_description')),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view'   => ViewTicket::route('/{record}'),
            'edit'   => EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TicketsOverviewWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
        ];
    }
}
