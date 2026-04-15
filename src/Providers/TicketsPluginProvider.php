<?php

namespace FyWolf\Tickets\Providers;

use App\Models\Role;
use App\Models\Server;
use FyWolf\Tickets\Models\AutomationRule;
use FyWolf\Tickets\Models\CannedResponse;
use FyWolf\Tickets\Models\Ticket;
use FyWolf\Tickets\Models\TicketCategory;
use FyWolf\Tickets\Policies\AutomationRulePolicy;
use FyWolf\Tickets\Policies\CannedResponsePolicy;
use FyWolf\Tickets\Policies\TicketCategoryPolicy;
use FyWolf\Tickets\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TicketsPluginProvider extends ServiceProvider
{
    public function register(): void
    {
        Role::registerCustomDefaultPermissions('ticket');
        Role::registerCustomDefaultPermissions('canned_response');
        Role::registerCustomDefaultPermissions('ticket_category');
        Role::registerCustomDefaultPermissions('automation_rule');
        Role::registerCustomModelIcon('ticket', 'tabler-ticket');
        Role::registerCustomModelIcon('canned_response', 'tabler-messages');
        Role::registerCustomModelIcon('ticket_category', 'tabler-category');
        Role::registerCustomModelIcon('automation_rule', 'tabler-robot');
    }

    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(CannedResponse::class, CannedResponsePolicy::class);
        Gate::policy(TicketCategory::class, TicketCategoryPolicy::class);
        Gate::policy(AutomationRule::class, AutomationRulePolicy::class);

        Server::resolveRelationUsing('tickets', fn (Server $server) => $server->hasMany(Ticket::class, 'server_id', 'id'));
    }
}
