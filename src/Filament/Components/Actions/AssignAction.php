<?php

namespace FyWolf\Tickets\Filament\Components\Actions;

use App\Models\User;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class AssignAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'assign';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->status === TicketStatus::Closed);

        $this->tooltip(trans('tickets::tickets.assign'));

        $this->icon('tabler-user-check');

        $this->color('primary');

        $this->schema([
            Select::make('user_id')
                ->label(trans('tickets::tickets.assigned_to'))
                ->default(fn (Ticket $ticket) => $ticket->assigned_user_id)
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => User::where('username', 'like', "%{$search}%")
                    ->orderBy('username')
                    ->limit(50)
                    ->pluck('username', 'id')
                    ->toArray()
                )
                ->getOptionLabelUsing(fn ($value): string => User::find($value)?->username ?? (string) $value)
                ->required(),
        ]);

        $this->action(function (Ticket $ticket, array $data) {
            $user = User::find($data['user_id']);
            if ($user) {
                $ticket->assignTo($user);
            }
        });
    }
}
