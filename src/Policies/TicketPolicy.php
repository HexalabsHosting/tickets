<?php

namespace FyWolf\Tickets\Policies;

use App\Policies\DefaultAdminPolicies;

class TicketPolicy
{
    use DefaultAdminPolicies;

    protected string $modelName = 'ticket';
}
