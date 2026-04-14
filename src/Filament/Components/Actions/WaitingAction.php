<?php

namespace FyWolf\Tickets\Filament\Components\Actions;

use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class WaitingAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'set_waiting';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->status === TicketStatus::Closed || $ticket->status === TicketStatus::WaitingForCustomer);

        $this->tooltip(trans('tickets::tickets.waiting_for_customer'));

        $this->icon('tabler-clock-pause');

        $this->color('warning');

        $this->requiresConfirmation();

        $this->action(function (Ticket $ticket) {
            $ticket->update(['status' => TicketStatus::WaitingForCustomer]);

            Notification::make()
                ->title(trans('tickets::tickets.notifications.set_waiting'))
                ->success()
                ->send();
        });
    }
}
