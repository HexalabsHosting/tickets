<?php

namespace FyWolf\Tickets\Providers;

use App\Models\Role;
use App\Models\Server;
use FyWolf\Tickets\Models\Ticket;
use Illuminate\Support\ServiceProvider;

class TicketsPluginProvider extends ServiceProvider
{
    public function register(): void
    {
        Role::registerCustomDefaultPermissions('ticket');
        Role::registerCustomModelIcon('ticket', 'tabler-ticket');
    }

    public function boot(): void
    {
        Server::resolveRelationUsing('tickets', fn (Server $server) => $server->hasMany(Ticket::class, 'server_id', 'id'));
    }
}
