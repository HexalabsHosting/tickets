<?php

namespace FyWolf\Tickets;

use Filament\Contracts\Plugin;
use Filament\Panel;
use FyWolf\Tickets\Filament\Admin\Pages\TicketSettings;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsOverviewWidget;

class TicketsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'tickets';
    }

    public function register(Panel $panel): void
    {
        $id = str($panel->getId())->title();

        $panel->discoverResources(plugin_path($this->getId(), "src/Filament/$id/Resources"), "FyWolf\\Tickets\\Filament\\$id\\Resources");

        if ($panel->getId() === 'admin') {
            $panel->pages([TicketSettings::class]);
            $panel->widgets([TicketsOverviewWidget::class]);
        }
    }

    public function boot(Panel $panel): void {}
}
