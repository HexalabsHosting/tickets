<?php

namespace FyWolf\Tickets\Filament\Admin\Widgets;

use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $open = Ticket::where('status', TicketStatus::Open->value)->count();
        $inProgress = Ticket::where('status', TicketStatus::InProgress->value)->count();
        $waiting = Ticket::where('status', TicketStatus::WaitingForCustomer->value)->count();
        $unassigned = Ticket::whereNull('assigned_user_id')
            ->whereNot('status', TicketStatus::Closed->value)
            ->count();
        $urgent = Ticket::where('priority', TicketPriority::VeryHigh->value)
            ->whereNot('status', TicketStatus::Closed->value)
            ->count();

        $stats = [
            Stat::make(trans('tickets::tickets.stats.open'), $open)
                ->icon('tabler-circle-dashed')
                ->color('primary'),
            Stat::make(trans('tickets::tickets.stats.in_progress'), $inProgress)
                ->icon('tabler-progress')
                ->color('success'),
            Stat::make(trans('tickets::tickets.stats.waiting'), $waiting)
                ->icon('tabler-clock-pause')
                ->color('warning'),
            Stat::make(trans('tickets::tickets.stats.unassigned'), $unassigned)
                ->icon('tabler-user-question')
                ->color($unassigned > 0 ? 'danger' : 'gray'),
            Stat::make(trans('tickets::tickets.stats.urgent'), $urgent)
                ->icon('tabler-chevrons-up')
                ->color($urgent > 0 ? 'danger' : 'gray'),
        ];

        if (config('tickets.sla.enabled')) {
            $overdue = Ticket::whereNot('status', TicketStatus::Closed->value)->get()
                ->filter(fn (Ticket $t) => $t->isOverdue())
                ->count();

            $stats[] = Stat::make(trans('tickets::tickets.stats.overdue'), $overdue)
                ->icon('tabler-alarm')
                ->color($overdue > 0 ? 'danger' : 'success');
        }

        $avgMinutes = Ticket::whereNotNull('first_replied_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->avg(fn (Ticket $t) => $t->created_at->diffInMinutes($t->first_replied_at));

        $avgLabel = $avgMinutes === null
            ? '—'
            : ($avgMinutes < 60
                ? round($avgMinutes) . ' min'
                : round($avgMinutes / 60, 1) . ' h');

        $stats[] = Stat::make(trans('tickets::tickets.stats.avg_first_reply'), $avgLabel)
            ->icon('tabler-clock-bolt')
            ->color('info')
            ->description(trans('tickets::tickets.stats.avg_first_reply_period'));

        return $stats;
    }
}
