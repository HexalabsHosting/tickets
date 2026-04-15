<?php

namespace FyWolf\Tickets\Filament\Admin\Pages;

use FyWolf\Tickets\Filament\Admin\Widgets\TicketsCategoryChart;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsTrendChart;
use Filament\Pages\Page;

class TicketsReportPage extends Page
{
    protected string $view = 'tickets::pages.reports';

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 98;

    public static function getNavigationLabel(): string
    {
        return trans('tickets::tickets.reports.nav_label');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TicketsTrendChart::class,
            TicketsCategoryChart::class,
        ];
    }

    public function getTitle(): string
    {
        return trans('tickets::tickets.reports.nav_label');
    }
}
