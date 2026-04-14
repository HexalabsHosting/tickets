<?php

return [
    'user_creation' => (bool) env('TICKETS_USER_CREATION', true),
    'max_open_tickets' => (int) env('TICKETS_MAX_OPEN_TICKETS', 0),
    'default_priority' => env('TICKETS_DEFAULT_PRIORITY', 'normal'),
    'auto_close_days' => (int) env('TICKETS_AUTO_CLOSE_DAYS', 0),

    'sla' => [
        'enabled'         => (bool) env('TICKETS_SLA_ENABLED', false),
        'low_hours'       => (int) env('TICKETS_SLA_LOW_HOURS', 72),
        'normal_hours'    => (int) env('TICKETS_SLA_NORMAL_HOURS', 48),
        'high_hours'      => (int) env('TICKETS_SLA_HIGH_HOURS', 24),
        'very_high_hours' => (int) env('TICKETS_SLA_VERY_HIGH_HOURS', 4),
    ],

    'notifications' => [
        'new_ticket'      => (bool) env('TICKETS_NOTIFY_NEW_TICKET', true),
        'new_reply'       => (bool) env('TICKETS_NOTIFY_NEW_REPLY', true),
        'ticket_assigned' => (bool) env('TICKETS_NOTIFY_ASSIGNED', true),
        'ticket_closed'   => (bool) env('TICKETS_NOTIFY_CLOSED', true),
        'ticket_reopened' => (bool) env('TICKETS_NOTIFY_REOPENED', true),
    ],
];
