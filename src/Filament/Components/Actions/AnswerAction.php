<?php

namespace FyWolf\Tickets\Filament\Components\Actions;

use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;

class AnswerAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'answer';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->status === TicketStatus::Closed);

        $this->tooltip(trans('tickets::tickets.answer_verb'));

        $this->modalHeading(trans('tickets::tickets.close_with_reply'));

        $this->icon('tabler-edit');

        $this->color('primary');

        $this->schema([
            MarkdownEditor::make('answer')
                ->nullable()
                ->hiddenLabel(),
        ]);

        $this->action(function (Ticket $ticket, array $data) {
            $ticket->close($data['answer']);

            Notification::make()
                ->title(trans('tickets::tickets.notifications.closed'))
                ->success()
                ->send();
        });
    }
}
