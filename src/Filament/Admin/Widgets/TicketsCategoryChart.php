<?php

namespace FyWolf\Tickets\Filament\Admin\Widgets;

use FyWolf\Tickets\Models\Ticket;
use FyWolf\Tickets\Models\TicketCategory;
use Filament\Widgets\ChartWidget;

class TicketsCategoryChart extends ChartWidget
{
    protected ?string $maxHeight = '220px';

    public function getHeading(): string
    {
        return trans('tickets::tickets.reports.category_heading');
    }

    protected function getData(): array
    {
        $categories = TicketCategory::withCount('tickets')
            ->having('tickets_count', '>', 0)
            ->orderByDesc('tickets_count')
            ->limit(10)
            ->get();

        $uncategorized = Ticket::whereNull('category_id')->count();

        $labels = $categories->pluck('name')->toArray();
        $data   = $categories->pluck('tickets_count')->toArray();

        if ($uncategorized > 0) {
            $labels[] = trans('tickets::tickets.reports.uncategorized');
            $data[]   = $uncategorized;
        }

        $colors = [
            'rgba(99, 102, 241, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(14, 165, 233, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(100, 116, 139, 0.8)',
            'rgba(156, 163, 175, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
