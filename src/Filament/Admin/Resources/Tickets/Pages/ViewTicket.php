<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\Tickets\TicketResource;
use FyWolf\Tickets\Filament\Components\Actions\AnswerAction;
use FyWolf\Tickets\Filament\Components\Actions\AssignAction;
use FyWolf\Tickets\Filament\Components\Actions\AssignToMeAction;
use FyWolf\Tickets\Filament\Components\Actions\CloseAction;
use FyWolf\Tickets\Filament\Components\Actions\ReopenAction;
use FyWolf\Tickets\Filament\Components\Actions\WaitingAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->tooltip(fn (\Filament\Actions\EditAction $action) => $action->getLabel())
                ->hiddenLabel()
                ->icon('tabler-pencil'),
            AssignAction::make(),
            AssignToMeAction::make(),
            WaitingAction::make(),
            AnswerAction::make(),
            CloseAction::make(),
            ReopenAction::make(),
        ];
    }
}
