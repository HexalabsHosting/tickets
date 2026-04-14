<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\Tickets\TicketResource;
use FyWolf\Tickets\Filament\Components\Actions\AnswerAction;
use FyWolf\Tickets\Filament\Components\Actions\AssignToMeAction;
use FyWolf\Tickets\Filament\Components\Actions\ReopenAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            AssignToMeAction::make(),
            AnswerAction::make(),
            ReopenAction::make(),
        ];
    }
}
