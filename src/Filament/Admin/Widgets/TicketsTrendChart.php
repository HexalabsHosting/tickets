<?php

namespace FyWolf\Tickets\Filament\Admin\Widgets;

use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TicketsTrendChart extends ChartWidget
{
    protected static ?string $maxHeight = '220px';

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return trans('tickets::tickets.reports.trend_heading');
    }

    protected function getData(): array
    {
        $days   = collect(range(29, 0))->map(fn (int $i) => now()->subDays($i)->toDateString());
        $opened = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $closed = Ticket::selectRaw('DATE(closed_at) as date, COUNT(*) as count')
            ->whereNotNull('closed_at')
            ->where('closed_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label'           => trans('tickets::tickets.reports.opened'),
                    'data'            => $days->map(fn ($d) => $opened[$d] ?? 0)->values()->toArray(),
                    'borderColor'     => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => trans('tickets::tickets.reports.closed'),
                    'data'            => $days->map(fn ($d) => $closed[$d] ?? 0)->values()->toArray(),
                    'borderColor'     => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('M j'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
