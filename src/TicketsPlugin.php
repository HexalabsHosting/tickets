<?php

namespace FyWolf\Tickets;

use App\Contracts\Plugins\HasPluginSettings;
use App\Traits\EnvironmentWriterTrait;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Filament\Admin\Pages\TicketsReportPage;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsCategoryChart;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsOverviewWidget;
use FyWolf\Tickets\Filament\Admin\Widgets\TicketsTrendChart;

class TicketsPlugin implements HasPluginSettings, Plugin
{
    use EnvironmentWriterTrait;

    public function getId(): string
    {
        return 'tickets';
    }

    public function register(Panel $panel): void
    {
        $id = str($panel->getId())->title();

        $panel->discoverResources(plugin_path($this->getId(), "src/Filament/$id/Resources"), "FyWolf\\Tickets\\Filament\\$id\\Resources");

        if ($panel->getId() === 'admin') {
            $panel->pages([TicketsReportPage::class]);
            $panel->widgets([TicketsOverviewWidget::class, TicketsTrendChart::class, TicketsCategoryChart::class]);
        }
    }

    public function boot(Panel $panel): void {}

    public function getSettingsForm(): array
    {
        return [
            Tabs::make()->tabs([
                Tab::make(trans('tickets::tickets.settings.tab_general'))->schema([
                    Section::make()->columns(2)->schema([
                        Toggle::make('user_creation')
                            ->label(trans('tickets::tickets.settings.user_creation'))
                            ->helperText(trans('tickets::tickets.settings.user_creation_help'))
                            ->default(fn () => config('tickets.user_creation'))
                            ->columnSpanFull(),
                        TextInput::make('max_open_tickets')
                            ->label(trans('tickets::tickets.settings.max_open_tickets'))
                            ->helperText(trans('tickets::tickets.settings.max_open_tickets_help'))
                            ->numeric()
                            ->minValue(0)
                            ->default(fn () => config('tickets.max_open_tickets')),
                        Select::make('default_priority')
                            ->label(trans('tickets::tickets.settings.default_priority'))
                            ->options(TicketPriority::class)
                            ->selectablePlaceholder(false)
                            ->default(fn () => config('tickets.default_priority')),
                        TextInput::make('auto_close_days')
                            ->label(trans('tickets::tickets.settings.auto_close_days'))
                            ->helperText(trans('tickets::tickets.settings.auto_close_days_help'))
                            ->numeric()
                            ->minValue(0)
                            ->suffix(trans('tickets::tickets.settings.days'))
                            ->default(fn () => config('tickets.auto_close_days')),
                    ]),
                ]),

                Tab::make(trans('tickets::tickets.settings.tab_sla'))->schema([
                    Section::make()->columns(2)->schema([
                        Toggle::make('sla_enabled')
                            ->label(trans('tickets::tickets.settings.sla_enabled'))
                            ->helperText(trans('tickets::tickets.settings.sla_enabled_help'))
                            ->default(fn () => config('tickets.sla.enabled'))
                            ->columnSpanFull(),
                        TextInput::make('sla_low_hours')
                            ->label(trans('tickets::tickets.settings.sla_low'))
                            ->numeric()->minValue(1)
                            ->suffix(trans('tickets::tickets.settings.hours'))
                            ->default(fn () => config('tickets.sla.low_hours')),
                        TextInput::make('sla_normal_hours')
                            ->label(trans('tickets::tickets.settings.sla_normal'))
                            ->numeric()->minValue(1)
                            ->suffix(trans('tickets::tickets.settings.hours'))
                            ->default(fn () => config('tickets.sla.normal_hours')),
                        TextInput::make('sla_high_hours')
                            ->label(trans('tickets::tickets.settings.sla_high'))
                            ->numeric()->minValue(1)
                            ->suffix(trans('tickets::tickets.settings.hours'))
                            ->default(fn () => config('tickets.sla.high_hours')),
                        TextInput::make('sla_very_high_hours')
                            ->label(trans('tickets::tickets.settings.sla_very_high'))
                            ->numeric()->minValue(1)
                            ->suffix(trans('tickets::tickets.settings.hours'))
                            ->default(fn () => config('tickets.sla.very_high_hours')),
                    ]),
                ]),

                Tab::make(trans('tickets::tickets.settings.tab_notifications'))->schema([
                    Section::make()->schema([
                        Toggle::make('notify_new_ticket')
                            ->label(trans('tickets::tickets.settings.notify_new_ticket'))
                            ->helperText(trans('tickets::tickets.settings.notify_new_ticket_help'))
                            ->default(fn () => config('tickets.notifications.new_ticket')),
                        Toggle::make('notify_new_reply')
                            ->label(trans('tickets::tickets.settings.notify_new_reply'))
                            ->helperText(trans('tickets::tickets.settings.notify_new_reply_help'))
                            ->default(fn () => config('tickets.notifications.new_reply')),
                        Toggle::make('notify_assigned')
                            ->label(trans('tickets::tickets.settings.notify_assigned'))
                            ->helperText(trans('tickets::tickets.settings.notify_assigned_help'))
                            ->default(fn () => config('tickets.notifications.ticket_assigned')),
                        Toggle::make('notify_closed')
                            ->label(trans('tickets::tickets.settings.notify_closed'))
                            ->helperText(trans('tickets::tickets.settings.notify_closed_help'))
                            ->default(fn () => config('tickets.notifications.ticket_closed')),
                        Toggle::make('notify_reopened')
                            ->label(trans('tickets::tickets.settings.notify_reopened'))
                            ->helperText(trans('tickets::tickets.settings.notify_reopened_help'))
                            ->default(fn () => config('tickets.notifications.ticket_reopened')),
                    ]),
                ]),

                Tab::make(trans('tickets::tickets.settings.tab_webhook'))->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('webhook_url')
                            ->label(trans('tickets::tickets.settings.webhook_url'))
                            ->helperText(trans('tickets::tickets.settings.webhook_url_help'))
                            ->url()
                            ->default(fn () => config('tickets.webhook.url'))
                            ->columnSpanFull(),
                        Toggle::make('webhook_new_ticket')
                            ->label(trans('tickets::tickets.settings.webhook_new_ticket'))
                            ->default(fn () => config('tickets.webhook.new_ticket')),
                        Toggle::make('webhook_new_reply')
                            ->label(trans('tickets::tickets.settings.webhook_new_reply'))
                            ->default(fn () => config('tickets.webhook.new_reply')),
                        Toggle::make('webhook_closed')
                            ->label(trans('tickets::tickets.settings.webhook_closed'))
                            ->default(fn () => config('tickets.webhook.closed')),
                        Toggle::make('webhook_assigned')
                            ->label(trans('tickets::tickets.settings.webhook_assigned'))
                            ->default(fn () => config('tickets.webhook.assigned')),
                        Toggle::make('webhook_reopened')
                            ->label(trans('tickets::tickets.settings.webhook_reopened'))
                            ->default(fn () => config('tickets.webhook.reopened')),
                    ]),
                ]),
            ]),
        ];
    }

    public function saveSettings(array $data): void
    {
        $this->writeToEnvironment([
            'TICKETS_USER_CREATION'       => ($data['user_creation'] ?? false) ? 'true' : 'false',
            'TICKETS_MAX_OPEN_TICKETS'    => $data['max_open_tickets'] ?? 0,
            'TICKETS_DEFAULT_PRIORITY'    => $data['default_priority'] ?? 'normal',
            'TICKETS_AUTO_CLOSE_DAYS'     => $data['auto_close_days'] ?? 0,
            'TICKETS_SLA_ENABLED'         => ($data['sla_enabled'] ?? false) ? 'true' : 'false',
            'TICKETS_SLA_LOW_HOURS'       => $data['sla_low_hours'] ?? 72,
            'TICKETS_SLA_NORMAL_HOURS'    => $data['sla_normal_hours'] ?? 48,
            'TICKETS_SLA_HIGH_HOURS'      => $data['sla_high_hours'] ?? 24,
            'TICKETS_SLA_VERY_HIGH_HOURS' => $data['sla_very_high_hours'] ?? 4,
            'TICKETS_NOTIFY_NEW_TICKET'   => ($data['notify_new_ticket'] ?? true) ? 'true' : 'false',
            'TICKETS_NOTIFY_NEW_REPLY'    => ($data['notify_new_reply'] ?? true) ? 'true' : 'false',
            'TICKETS_NOTIFY_ASSIGNED'     => ($data['notify_assigned'] ?? true) ? 'true' : 'false',
            'TICKETS_NOTIFY_CLOSED'       => ($data['notify_closed'] ?? true) ? 'true' : 'false',
            'TICKETS_NOTIFY_REOPENED'     => ($data['notify_reopened'] ?? true) ? 'true' : 'false',
            'TICKETS_WEBHOOK_URL'         => $data['webhook_url'] ?? '',
            'TICKETS_WEBHOOK_NEW_TICKET'  => ($data['webhook_new_ticket'] ?? true) ? 'true' : 'false',
            'TICKETS_WEBHOOK_NEW_REPLY'   => ($data['webhook_new_reply'] ?? true) ? 'true' : 'false',
            'TICKETS_WEBHOOK_CLOSED'      => ($data['webhook_closed'] ?? true) ? 'true' : 'false',
            'TICKETS_WEBHOOK_ASSIGNED'    => ($data['webhook_assigned'] ?? true) ? 'true' : 'false',
            'TICKETS_WEBHOOK_REOPENED'    => ($data['webhook_reopened'] ?? true) ? 'true' : 'false',
        ]);

        Notification::make()
            ->title(trans('tickets::tickets.settings.saved'))
            ->success()
            ->send();
    }
}
