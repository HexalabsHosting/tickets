<?php

namespace FyWolf\Tickets\Policies;

use App\Policies\DefaultAdminPolicies;

class TicketCategoryPolicy
{
    use DefaultAdminPolicies;

    protected string $modelName = 'ticket_category';
}
