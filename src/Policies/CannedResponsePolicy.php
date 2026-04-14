<?php

namespace FyWolf\Tickets\Policies;

use App\Policies\DefaultAdminPolicies;

class CannedResponsePolicy
{
    use DefaultAdminPolicies;

    protected string $modelName = 'canned_response';
}
