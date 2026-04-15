<?php

namespace FyWolf\Tickets\Services;

use App\Models\User;
use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Models\AutomationRule;
use FyWolf\Tickets\Models\Ticket;

class AutomationService
{
    private static bool $running = false;

    public static function evaluate(string $trigger, Ticket $ticket): void
    {
        if (static::$running) {
            return;
        }

        static::$running = true;

        try {
            AutomationRule::active()
                ->forTrigger($trigger)
                ->orderBy('sort_order')
                ->get()
                ->each(function (AutomationRule $rule) use ($ticket) {
                    if (static::conditionsMatch($rule->conditions, $ticket)) {
                        static::executeActions($rule->actions, $ticket);
                    }
                });
        } finally {
            static::$running = false;
        }
    }

    private static function conditionsMatch(array $conditions, Ticket $ticket): bool
    {
        foreach ($conditions as $condition) {
            $field    = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value    = $condition['value'] ?? '';

            $actual = match ($field) {
                'priority'    => $ticket->priority?->value,
                'category_id' => (string) $ticket->category_id,
                'status'      => $ticket->status?->value,
                'assigned'    => $ticket->assigned_user_id ? 'yes' : 'no',
                default       => null,
            };

            $matches = match ($operator) {
                '!=' => $actual !== $value,
                default => $actual === $value,
            };

            if (!$matches) {
                return false;
            }
        }

        return true;
    }

    private static function executeActions(array $actions, Ticket $ticket): void
    {
        foreach ($actions as $action) {
            $type  = $action['type'] ?? '';
            $value = $action['value'] ?? '';

            match ($type) {
                'set_priority' => $ticket->update(['priority' => TicketPriority::from($value)]),
                'set_status'   => $ticket->update(['status' => TicketStatus::from($value)]),
                'assign_to'    => static::assignTo($ticket, (int) $value),
                default        => null,
            };
        }
    }

    private static function assignTo(Ticket $ticket, int $userId): void
    {
        $user = User::find($userId);
        if ($user && $ticket->assigned_user_id === null) {
            $ticket->assignTo($user);
        }
    }
}
