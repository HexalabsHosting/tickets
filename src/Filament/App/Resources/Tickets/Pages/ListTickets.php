<?php

namespace FyWolf\Tickets\Filament\App\Resources\Tickets\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\App\Resources\Tickets\TicketsResource;
use FyWolf\Tickets\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $userId = auth()->user()->id;

        return [
            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('author_id', $userId)->whereNot('status', TicketStatus::Closed->value)->count()),

            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('author_id', $userId)->where('status', TicketStatus::Closed->value)->count()),

            'all' => Tab::make('All')
                ->badge(fn () => Ticket::where('author_id', $userId)->count()),
        ];
    }
}
