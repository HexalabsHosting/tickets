<?php

namespace FyWolf\Tickets\Policies;

use App\Policies\DefaultAdminPolicies;

class AutomationRulePolicy
{
    use DefaultAdminPolicies;

    protected string $modelName = 'automation_rule';
}
