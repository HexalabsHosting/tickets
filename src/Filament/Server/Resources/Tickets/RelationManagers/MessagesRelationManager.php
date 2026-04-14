<?php

namespace FyWolf\Tickets\Filament\Server\Resources\Tickets\RelationManagers;

use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Components\Tables\Columns\DateTimeColumn;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\Ticket;
use FyWolf\Tickets\Models\TicketMessage;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method Ticket getOwnerRecord()
 */
class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('hidden', false))
            ->modelLabel(trans_choice('tickets::tickets.message', 1))
            ->pluralModelLabel(trans_choice('tickets::tickets.message', 2))
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Stack::make([
                    TextColumn::make('message')
                        ->markdown(),
                    Split::make([
                        TextColumn::make('author.username')
                            ->grow(false)
                            ->placeholder(trans('tickets::tickets.unknown'))
                            ->icon('tabler-user')
                            ->url(fn (TicketMessage $ticketMessage) => $ticketMessage->author && auth()->user()->can('edit user', $ticketMessage->author) ? EditUser::getUrl(['record' => $ticketMessage->author], panel: 'admin') : null),
                        DateTimeColumn::make('created_at')
                            ->grow(false)
                            ->since(),
                        TextColumn::make('is_author')
                            ->grow(false)
                            ->badge()
                            ->color('success')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->author_id === $this->getOwnerRecord()->author_id ? trans('tickets::tickets.author') : null),
                        TextColumn::make('is_assigned')
                            ->grow(false)
                            ->badge()
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->author_id === $this->getOwnerRecord()->assigned_user_id ? trans('tickets::tickets.admin') : null),
                    ]),
                ])->space(3),
            ])
            ->headerActions([
                CreateAction::make()
                    ->hidden(fn () => $this->getOwnerRecord()->status === TicketStatus::Closed)
                    ->createAnother(false)
                    ->schema([
                        MarkdownEditor::make('message')
                            ->label(trans_choice('tickets::tickets.message', 1))
                            ->required(),
                    ])
                    ->after(function () {
                        $ticket = $this->getOwnerRecord();
                        if ($ticket->status === TicketStatus::WaitingForCustomer) {
                            $ticket->update(['status' => TicketStatus::InProgress]);
                        }

                        if (config('tickets.notifications.new_reply') && $ticket->assignedUser) {
                            \Filament\Notifications\Notification::make()
                                ->title(trans('tickets::tickets.notifications.new_reply'))
                                ->sendToDatabase($ticket->assignedUser);
                        }
                    }),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
