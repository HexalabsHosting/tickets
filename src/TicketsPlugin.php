<?php

namespace FyWolf\Tickets;

use App\Contracts\Plugins\HasPluginSettings;
use App\Models\Server;
use App\Traits\EnvironmentWriterTrait;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Schemas\Components\Section;

/**
 * Support, delegated to the billing app.
 *
 * This plugin used to be the whole helpdesk — models, migrations, Filament
 * resources, policies, widgets and reports, all inside the panel. That is gone,
 * for the same reasons billing itself left the panel:
 *
 *  - support history lived in the panel database, so a panel restore rolled
 *    tickets back along with everything else;
 *  - an agent answering a ticket could not see the customer's orders, invoices
 *    or payments, which is most of what a hosting question is actually about;
 *  - it was several thousand lines of Filament, and a Filament major bump is
 *    precisely what broke the previous in-panel billing plugin.
 *
 * What remains is a link. No models, no migrations, no Filament resources — so
 * a panel upgrade has nothing here to break.
 *
 * The link carries the server's uuid; the billing app resolves it to the order
 * and pre-fills the form. The panel is already an OAuth client of billing, so
 * the customer arrives signed in.
 *
 * **The old tables are deliberately left in place.** `tickets`,
 * `ticket_messages`, `ticket_categories`, `ticket_category_fields`,
 * `ticket_canned_responses` and `ticket_automation_rules` are no longer read or
 * written. Export anything worth keeping, then drop them by hand — shipping a
 * migration that destroys a support archive is not a thing to do automatically.
 */
class TicketsPlugin implements HasPluginSettings, Plugin
{
    use EnvironmentWriterTrait;

    public function getId(): string
    {
        return 'tickets';
    }

    public function register(Panel $panel): void
    {
        // Server panel only: the link needs a server for its context, and
        // support is customer-facing rather than an admin tool.
        if ($panel->getId() !== 'server') {
            return;
        }

        $panel->navigationItems([
            NavigationItem::make('Support')
                ->icon('tabler-lifebuoy')
                ->sort(20)
                ->visible(fn (): bool => filled(config('tickets.billing_url')))
                ->url(fn (): string => $this->supportUrl(), shouldOpenInNewTab: true),
        ]);
    }

    public function boot(Panel $panel): void {}

    /**
     * `/support/new?server={uuid}&from=panel`.
     *
     * The uuid rather than the panel's own server id: billing stores the uuid
     * on the order, and it is the only identifier both sides share.
     */
    private function supportUrl(): string
    {
        $base = rtrim((string) config('tickets.billing_url'), '/') . '/support/new';

        /** @var Server|null $server */
        $server = Filament::getTenant();

        if (! $server) {
            return $base;
        }

        return $base . '?' . http_build_query([
            'server' => $server->uuid,
            'from'   => 'panel',
        ]);
    }

    public function getSettingsForm(): array
    {
        return [
            Section::make('Support')
                ->description('Tickets are handled by the billing app; this plugin only links to it.')
                ->schema([
                    TextInput::make('TICKETS_BILLING_URL')
                        ->label('Billing app URL')
                        ->url()
                        ->placeholder('https://billing.example.com')
                        ->helperText('Customers are sent to /support/new there, with their server pre-selected.')
                        ->default(config('tickets.billing_url')),
                ]),
        ];
    }

    /**
     * Current values for the settings slide-over.
     *
     * Was `getSettings()`, which no version of `HasPluginSettings` ever declared —
     * so it was never called by anything and the form ran on its `->default()`
     * alone. The panel now requires this name and passes it to `->fillForm()`,
     * which replaces those defaults, so the keys must be the field names.
     *
     * @return array<string, mixed>
     */
    public function getSettingsFormData(): array
    {
        return ['TICKETS_BILLING_URL' => config('tickets.billing_url')];
    }

    public function saveSettings(array $data): void
    {
        $this->writeToEnvironment(['TICKETS_BILLING_URL' => $data['TICKETS_BILLING_URL'] ?? '']);

        Notification::make()->title('Support settings saved')->success()->send();
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
