<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\AutomationRules\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\AutomationRules\AutomationRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAutomationRule extends CreateRecord
{
    protected static string $resource = AutomationRuleResource::class;

    protected static bool $canCreateAnother = false;
}
