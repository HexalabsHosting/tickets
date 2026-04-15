<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\Tickets\RelationManagers;

use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Components\Tables\Columns\DateTimeColumn;
use FyWolf\Tickets\Enums\MessageType;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\CannedResponse;
use FyWolf\Tickets\Models\Ticket;
use FyWolf\Tickets\Models\TicketMessage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * @method Ticket getOwnerRecord()
 */
class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(trans_choice('tickets::tickets.message', 1))
            ->pluralModelLabel(trans_choice('tickets::tickets.message', 2))
            ->paginated(false)
            ->defaultSort('created_at', 'asc')
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
                        TextColumn::make('is_note')
                            ->grow(false)
                            ->badge()
                            ->color('warning')
                            ->icon('tabler-note')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->type === MessageType::Note ? trans('tickets::tickets.message_type_note') : null),
                        TextColumn::make('is_hidden')
                            ->grow(false)
                            ->badge()
                            ->color('warning')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->hidden ? trans('tickets::tickets.hidden') : null),
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
                    TextColumn::make('attachments')
                        ->html()
                        ->state(function (TicketMessage $msg): string {
                            if (empty($msg->attachments)) {
                                return '';
                            }

                            return collect($msg->attachments)
                                ->map(function (string $path): string {
                                    $url  = Storage::disk('public')->url($path);
                                    $name = basename($path);

                                    return '<a href="' . e($url) . '" target="_blank" rel="noopener" '
                                        . 'class="text-xs text-primary-600 hover:underline inline-flex items-center gap-1">'
                                        . '📎 ' . e($name) . '</a>';
                                })
                                ->implode('&ensp;');
                        }),
                ])->space(3),
            ])
            ->recordActions([
                Action::make('toggle_hidden')
                    ->tooltip(fn (TicketMessage $message) => $message->hidden ? trans('tickets::tickets.unhide_message') : trans('tickets::tickets.hide_message'))
                    ->icon(fn (TicketMessage $message) => $message->hidden ? 'tabler-eye' : 'tabler-eye-off')
                    ->color('warning')
                    ->action(fn (TicketMessage $message) => $message->update(['hidden' => !$message->hidden])),
                DeleteAction::make()
                    ->tooltip(fn (\Filament\Actions\DeleteAction $action) => $action->getLabel())
                    ->hiddenLabel(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->hiddenLabel()
                    ->icon('tabler-plus')
                    ->schema([
                        Select::make('canned_response_id')
                            ->label(trans('tickets::tickets.canned_response_insert'))
                            ->placeholder(trans('tickets::tickets.canned_response_select'))
                            ->options(fn () => CannedResponse::orderBy('category')->orderBy('name')->get()
                                ->groupBy('category')
                                ->mapWithKeys(fn ($items, $category) => [
                                    ($category ?: trans('tickets::tickets.canned_response_uncategorized')) => $items->pluck('name', 'id'),
                                ])
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $response = CannedResponse::find($state);
                                    if ($response) {
                                        $set('message', $response->content);
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->hidden(fn () => CannedResponse::count() === 0),
                        ToggleButtons::make('type')
                            ->label(trans('tickets::tickets.message_type'))
                            ->options(MessageType::class)
                            ->default(MessageType::Reply)
                            ->inline(),
                        MarkdownEditor::make('message')
                            ->label(trans_choice('tickets::tickets.message', 1))
                            ->required(),
                        Toggle::make('hidden')
                            ->label(trans('tickets::tickets.hidden') . '?'),
                        FileUpload::make('attachments')
                            ->label(trans('tickets::tickets.attachments'))
                            ->multiple()
                            ->disk('public')
                            ->directory('ticket-attachments')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ])
                    ->after(function () {
                        $ticket = $this->getOwnerRecord();
                        if ($ticket->status === TicketStatus::WaitingForCustomer || $ticket->status === TicketStatus::Open) {
                            $ticket->update(['status' => TicketStatus::InProgress]);
                        }
                    }),
            ]);
    }
}
