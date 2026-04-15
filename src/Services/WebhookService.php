<?php

namespace FyWolf\Tickets\Services;

use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Models\Ticket;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    public static function send(string $event, Ticket $ticket, ?string $body = null): void
    {
        $url = config('tickets.webhook.url');

        if (!$url || !config("tickets.webhook.{$event}", true)) {
            return;
        }

        try {
            Http::timeout(5)->post($url, static::buildPayload($event, $ticket, $body));
        } catch (\Exception) {
        }
    }

    private static function buildPayload(string $event, Ticket $ticket, ?string $body): array
    {
        $color = match ($ticket->priority) {
            TicketPriority::VeryHigh => 0xED4245,
            TicketPriority::High     => 0xFEE75C,
            TicketPriority::Normal   => 0x5865F2,
            TicketPriority::Low      => 0x57F287,
        };

        $title = match ($event) {
            'new_ticket' => trans('tickets::tickets.webhook.new_ticket'),
            'new_reply'  => trans('tickets::tickets.webhook.new_reply'),
            'closed'     => trans('tickets::tickets.webhook.closed'),
            'assigned'   => trans('tickets::tickets.webhook.assigned'),
            'reopened'   => trans('tickets::tickets.webhook.reopened'),
            default      => 'Ticket Update',
        };

        $fields = [
            ['name' => trans('tickets::tickets.priority'), 'value' => $ticket->priority->getLabel(), 'inline' => true],
            ['name' => trans('tickets::tickets.status'),   'value' => $ticket->status->getLabel(),   'inline' => true],
            ['name' => trans('tickets::tickets.server'),   'value' => $ticket->server?->name ?? '—', 'inline' => true],
        ];

        if ($ticket->assignedUser) {
            $fields[] = ['name' => trans('tickets::tickets.assigned_to'), 'value' => $ticket->assignedUser->username, 'inline' => true];
        }

        $embed = [
            'title'  => $title . ': ' . $ticket->title,
            'color'  => $color,
            'fields' => $fields,
        ];

        if ($body) {
            $embed['description'] = mb_substr(strip_tags($body), 0, 2048);
        }

        return ['embeds' => [$embed]];
    }
}
