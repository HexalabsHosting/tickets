<?php

namespace FyWolf\Tickets\Filament\Components\Actions;

use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CloseAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'close';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->status === TicketStatus::Closed);

        $this->tooltip(trans('tickets::tickets.close'));

        $this->icon('tabler-circle-x');

        $this->color('danger');

        $this->requiresConfirmation();

        $this->action(function (Ticket $ticket) {
            $ticket->close();

            Notification::make()
                ->title(trans('tickets::tickets.notifications.closed'))
                ->success()
                ->send();
        });
    }
}
