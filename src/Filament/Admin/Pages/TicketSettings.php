<?php

namespace FyWolf\Tickets\Filament\Admin\Pages;

use App\Traits\EnvironmentWriterTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use FyWolf\Tickets\Enums\TicketPriority;

class TicketSettings extends Page implements HasSchemas
{
    use EnvironmentWriterTrait;
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-settings';

    protected static ?string $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return trans('tickets::tickets.settings.nav_label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'TICKETS_USER_CREATION'       => config('tickets.user_creation'),
            'TICKETS_MAX_OPEN_TICKETS'    => config('tickets.max_open_tickets'),
            'TICKETS_DEFAULT_PRIORITY'    => config('tickets.default_priority'),
            'TICKETS_AUTO_CLOSE_DAYS'     => config('tickets.auto_close_days'),
            'TICKETS_SLA_ENABLED'         => config('tickets.sla.enabled'),
            'TICKETS_SLA_LOW_HOURS'       => config('tickets.sla.low_hours'),
            'TICKETS_SLA_NORMAL_HOURS'    => config('tickets.sla.normal_hours'),
            'TICKETS_SLA_HIGH_HOURS'      => config('tickets.sla.high_hours'),
            'TICKETS_SLA_VERY_HIGH_HOURS' => config('tickets.sla.very_high_hours'),
            'TICKETS_NOTIFY_NEW_TICKET'   => config('tickets.notifications.new_ticket'),
            'TICKETS_NOTIFY_NEW_REPLY'    => config('tickets.notifications.new_reply'),
            'TICKETS_NOTIFY_ASSIGNED'     => config('tickets.notifications.ticket_assigned'),
            'TICKETS_NOTIFY_CLOSED'       => config('tickets.notifications.ticket_closed'),
            'TICKETS_NOTIFY_REOPENED'     => config('tickets.notifications.ticket_reopened'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->tabs([
                    Tab::make(trans('tickets::tickets.settings.tab_general'))->schema([
                        Section::make()->columns(2)->schema([
                            Toggle::make('TICKETS_USER_CREATION')
                                ->label(trans('tickets::tickets.settings.user_creation'))
                                ->helperText(trans('tickets::tickets.settings.user_creation_help'))
                                ->columnSpanFull(),
                            TextInput::make('TICKETS_MAX_OPEN_TICKETS')
                                ->label(trans('tickets::tickets.settings.max_open_tickets'))
                                ->helperText(trans('tickets::tickets.settings.max_open_tickets_help'))
                                ->numeric()
                                ->minValue(0),
                            Select::make('TICKETS_DEFAULT_PRIORITY')
                                ->label(trans('tickets::tickets.settings.default_priority'))
                                ->options(TicketPriority::class)
                                ->selectablePlaceholder(false),
                            TextInput::make('TICKETS_AUTO_CLOSE_DAYS')
                                ->label(trans('tickets::tickets.settings.auto_close_days'))
                                ->helperText(trans('tickets::tickets.settings.auto_close_days_help'))
                                ->numeric()
                                ->minValue(0)
                                ->suffix(trans('tickets::tickets.settings.days')),
                        ]),
                    ]),

                    Tab::make(trans('tickets::tickets.settings.tab_sla'))->schema([
                        Section::make()->columns(2)->schema([
                            Toggle::make('TICKETS_SLA_ENABLED')
                                ->label(trans('tickets::tickets.settings.sla_enabled'))
                                ->helperText(trans('tickets::tickets.settings.sla_enabled_help'))
                                ->columnSpanFull(),
                            TextInput::make('TICKETS_SLA_LOW_HOURS')
                                ->label(trans('tickets::tickets.settings.sla_low'))
                                ->numeric()->minValue(1)
                                ->suffix(trans('tickets::tickets.settings.hours')),
                            TextInput::make('TICKETS_SLA_NORMAL_HOURS')
                                ->label(trans('tickets::tickets.settings.sla_normal'))
                                ->numeric()->minValue(1)
                                ->suffix(trans('tickets::tickets.settings.hours')),
                            TextInput::make('TICKETS_SLA_HIGH_HOURS')
                                ->label(trans('tickets::tickets.settings.sla_high'))
                                ->numeric()->minValue(1)
                                ->suffix(trans('tickets::tickets.settings.hours')),
                            TextInput::make('TICKETS_SLA_VERY_HIGH_HOURS')
                                ->label(trans('tickets::tickets.settings.sla_very_high'))
                                ->numeric()->minValue(1)
                                ->suffix(trans('tickets::tickets.settings.hours')),
                        ]),
                    ]),

                    Tab::make(trans('tickets::tickets.settings.tab_notifications'))->schema([
                        Section::make()->schema([
                            Toggle::make('TICKETS_NOTIFY_NEW_TICKET')
                                ->label(trans('tickets::tickets.settings.notify_new_ticket'))
                                ->helperText(trans('tickets::tickets.settings.notify_new_ticket_help')),
                            Toggle::make('TICKETS_NOTIFY_NEW_REPLY')
                                ->label(trans('tickets::tickets.settings.notify_new_reply'))
                                ->helperText(trans('tickets::tickets.settings.notify_new_reply_help')),
                            Toggle::make('TICKETS_NOTIFY_ASSIGNED')
                                ->label(trans('tickets::tickets.settings.notify_assigned'))
                                ->helperText(trans('tickets::tickets.settings.notify_assigned_help')),
                            Toggle::make('TICKETS_NOTIFY_CLOSED')
                                ->label(trans('tickets::tickets.settings.notify_closed'))
                                ->helperText(trans('tickets::tickets.settings.notify_closed_help')),
                            Toggle::make('TICKETS_NOTIFY_REOPENED')
                                ->label(trans('tickets::tickets.settings.notify_reopened'))
                                ->helperText(trans('tickets::tickets.settings.notify_reopened_help')),
                        ]),
                    ]),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('tickets::tickets.settings.save'))
                ->icon('tabler-device-floppy')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->writeToEnvironment([
            'TICKETS_USER_CREATION'       => $data['TICKETS_USER_CREATION'] ? 'true' : 'false',
            'TICKETS_MAX_OPEN_TICKETS'    => $data['TICKETS_MAX_OPEN_TICKETS'],
            'TICKETS_DEFAULT_PRIORITY'    => $data['TICKETS_DEFAULT_PRIORITY'],
            'TICKETS_AUTO_CLOSE_DAYS'     => $data['TICKETS_AUTO_CLOSE_DAYS'],
            'TICKETS_SLA_ENABLED'         => $data['TICKETS_SLA_ENABLED'] ? 'true' : 'false',
            'TICKETS_SLA_LOW_HOURS'       => $data['TICKETS_SLA_LOW_HOURS'],
            'TICKETS_SLA_NORMAL_HOURS'    => $data['TICKETS_SLA_NORMAL_HOURS'],
            'TICKETS_SLA_HIGH_HOURS'      => $data['TICKETS_SLA_HIGH_HOURS'],
            'TICKETS_SLA_VERY_HIGH_HOURS' => $data['TICKETS_SLA_VERY_HIGH_HOURS'],
            'TICKETS_NOTIFY_NEW_TICKET'   => $data['TICKETS_NOTIFY_NEW_TICKET'] ? 'true' : 'false',
            'TICKETS_NOTIFY_NEW_REPLY'    => $data['TICKETS_NOTIFY_NEW_REPLY'] ? 'true' : 'false',
            'TICKETS_NOTIFY_ASSIGNED'     => $data['TICKETS_NOTIFY_ASSIGNED'] ? 'true' : 'false',
            'TICKETS_NOTIFY_CLOSED'       => $data['TICKETS_NOTIFY_CLOSED'] ? 'true' : 'false',
            'TICKETS_NOTIFY_REOPENED'     => $data['TICKETS_NOTIFY_REOPENED'] ? 'true' : 'false',
        ]);

        Notification::make()
            ->title(trans('tickets::tickets.settings.saved'))
            ->success()
            ->send();
    }
}
