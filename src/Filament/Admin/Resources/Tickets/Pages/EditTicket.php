<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\Tickets\TicketResource;
use FyWolf\Tickets\Filament\Components\Actions\AnswerAction;
use FyWolf\Tickets\Filament\Components\Actions\AssignToMeAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCancelFormAction()->formId('form')
                ->tooltip(fn (Action $action) => $action->getLabel())
                ->hiddenLabel()
                ->icon('tabler-arrow-left'),
            AnswerAction::make(),
            AssignToMeAction::make(),
            $this->getSaveFormAction()->formId('form')
                ->tooltip(fn (Action $action) => $action->getLabel())
                ->hiddenLabel()
                ->icon('tabler-device-floppy'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
