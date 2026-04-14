<?php

namespace FyWolf\Tickets\Filament\Components\Actions;

use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ReopenAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reopen';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->status !== TicketStatus::Closed);

        $this->tooltip(trans('tickets::tickets.reopen'));

        $this->icon('tabler-restore');

        $this->color('warning');

        $this->requiresConfirmation();

        $this->action(function (Ticket $ticket) {
            $ticket->reopen();

            Notification::make()
                ->title(trans('tickets::tickets.notifications.reopened'))
                ->success()
                ->send();
        });
    }
}
