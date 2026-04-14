<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages;

use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\Admin\Resources\Tickets\TicketResource;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsOverviewWidget;
use FyWolf\Tickets\Models\Ticket;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    public function getTabs(): array
    {
        $userId = auth()->user()->id;

        return [
            'my' => Tab::make(trans('tickets::tickets.assigned_to_me'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value)->where('assigned_user_id', $userId))
                ->badge(fn () => Ticket::whereNot('status', TicketStatus::Closed->value)->where('assigned_user_id', $userId)->count()),

            'unassigned' => Tab::make(trans('tickets::tickets.unassigned'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('assigned_user_id')->whereNot('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::whereNull('assigned_user_id')->whereNot('status', TicketStatus::Closed->value)->count())
                ->badgeColor('warning'),

            'waiting' => Tab::make(trans('tickets::tickets.waiting'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TicketStatus::WaitingForCustomer->value))
                ->badge(fn () => Ticket::where('status', TicketStatus::WaitingForCustomer->value)->count())
                ->badgeColor('warning'),

            'urgent' => Tab::make(trans('tickets::tickets.urgent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('priority', TicketPriority::VeryHigh->value)->whereNot('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('priority', TicketPriority::VeryHigh->value)->whereNot('status', TicketStatus::Closed->value)->count())
                ->badgeColor('danger'),

            'open' => Tab::make(trans('tickets::tickets.open'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::whereNot('status', TicketStatus::Closed->value)->count()),

            'closed' => Tab::make(trans('tickets::tickets.closed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('status', TicketStatus::Closed->value)->count()),

            'all' => Tab::make(trans('tickets::tickets.all'))
                ->badge(fn () => Ticket::count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TicketsOverviewWidget::class,
        ];
    }
}
