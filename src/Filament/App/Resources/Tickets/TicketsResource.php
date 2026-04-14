<?php

namespace FyWolf\Tickets\Filament\App\Resources\Tickets;

use App\Filament\Components\Tables\Columns\DateTimeColumn;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\App\Resources\Tickets\Pages\ListTickets;
use FyWolf\Tickets\Filament\Server\Resources\Tickets\Pages\ViewTicket;
use FyWolf\Tickets\Models\Ticket;
use FyWolf\Tickets\Models\TicketCategory;
use Illuminate\Database\Eloquent\Builder;

class TicketsResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-ticket';

    protected static ?string $navigationLabel = 'Tickets';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('author_id', auth()->user()->id);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->whereNot('status', TicketStatus::Closed->value)
            ->count();

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
                    ->label('Title')
                    ->sortable()
                    ->searchable()
                    ->grow(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn (Ticket $ticket) => $ticket->category?->color ?? 'gray')
                    ->icon(fn (Ticket $ticket) => $ticket->category?->icon)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('server.name')
                    ->label('Server')
                    ->icon('tabler-brand-docker')
                    ->toggleable(),
                DateTimeColumn::make('created_at')
                    ->label('Opened')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('tabler-eye')
                    ->color('gray')
                    ->url(fn (Ticket $ticket) => ViewTicket::getUrl(
                        ['record' => $ticket->id],
                        panel: 'server',
                        tenant: $ticket->server,
                    )),
            ])
            ->emptyStateHeading('No Tickets')
            ->emptyStateDescription('')
            ->emptyStateIcon('tabler-ticket');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
        ];
    }
}
