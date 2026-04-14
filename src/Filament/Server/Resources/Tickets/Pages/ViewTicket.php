<?php

namespace FyWolf\Tickets\Filament\Server\Resources\Tickets\Pages;

use FyWolf\Tickets\Filament\Server\Resources\Tickets\TicketResource;
use FyWolf\Tickets\Filament\Components\Actions\CloseAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CloseAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
