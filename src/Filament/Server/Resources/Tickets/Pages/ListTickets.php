<?php

namespace FyWolf\Tickets\Filament\Server\Resources\Tickets\Pages;

use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\Server\Resources\Tickets\TicketResource;
use FyWolf\Tickets\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'open' => Tab::make(trans('tickets::tickets.open'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('server_id', Filament::getTenant()->getKey())->whereNot('status', TicketStatus::Closed->value)->count()),

            'closed' => Tab::make(trans('tickets::tickets.closed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('server_id', Filament::getTenant()->getKey())->where('status', TicketStatus::Closed->value)->count()),

            'all' => Tab::make(trans('tickets::tickets.all'))
                ->badge(fn () => Ticket::count()),
        ];
    }
}
