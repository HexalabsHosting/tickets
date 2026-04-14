<?php

namespace FyWolf\Tickets\Filament\Components\Actions;

use FyWolf\Tickets\Models\Ticket;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AssignToMeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'assign_to_me';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->assignedUser);

        $this->tooltip(trans('tickets::tickets.assign_to_me'));

        $this->icon('tabler-user-share');

        $this->color('primary');

        $this->action(function (Ticket $ticket) {
            $ticket->assignTo(auth()->user());

            Notification::make()
                ->title(trans('tickets::tickets.notifications.assigned_to_you'))
                ->success()
                ->send();
        });
    }
}
