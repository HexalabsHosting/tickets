<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\Tickets\TicketResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
