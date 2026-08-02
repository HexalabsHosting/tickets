<?php

/**
 * Support is handled by the billing app; this plugin only links to it.
 * Everything the old in-panel helpdesk configured — SLA, canned responses,
 * auto-close, webhooks — now lives in the billing app's own config.
 */
return [
    'billing_url' => env('TICKETS_BILLING_URL', ''),
];
