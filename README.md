# Tickets — panel → billing helpdesk link

Adds a **Support** item to the server panel that opens the billing app's
helpdesk with the current server already selected.

That is all it does. No models, no migrations, no Filament resources, no
composer dependencies — so a panel upgrade has nothing here to break.

## Why v2 threw the helpdesk away

v1 was a full in-panel ticketing system. It was replaced because:

- **Support history lived in the panel database.** A panel restore rolled
  tickets back with everything else — the same problem that moved billing out of
  the panel in the first place.
- **No customer context.** An agent answering "my server keeps crashing" could
  not see the plan, the price, the payment history, or whether the last invoice
  had failed. In the billing app that is one relation away.
- **No email.** v1 sent in-app Filament notifications only, so a customer who
  closed the panel never learned they had a reply.
- **Filament surface area.** Several thousand lines of resources, widgets and
  pages — precisely what broke the previous in-panel billing plugin on a
  Filament major bump.

## Setup

1. Install and enable the plugin.
2. Settings → Tickets → **Billing app URL**, e.g. `https://billing.example.com`.

The nav item stays hidden until that is set.

The link is `/support/new?server={uuid}&from=panel`. The uuid is the identifier
both systems share: billing stores it on the order, resolves it, and pre-fills
the form. The panel is already an OAuth client of billing, so the customer
arrives signed in.

## Upgrading from v1 — read this

**The v1 tables are left in place on purpose.** `tickets`, `ticket_messages`,
`ticket_categories`, `ticket_category_fields`, `ticket_canned_responses` and
`ticket_automation_rules` are no longer read or written, but nothing drops them:
shipping a migration that destroys a support archive is not something to do
automatically.

Export anything worth keeping first:

```sql
SELECT t.id, t.title, t.status, t.priority, t.created_at,
       u.email AS author, m.message, m.created_at AS message_at
FROM tickets t
LEFT JOIN users u ON u.id = t.author_id
LEFT JOIN ticket_messages m ON m.ticket_id = t.id
ORDER BY t.id, m.id;
```

Then, once you are satisfied:

```sql
DROP TABLE ticket_automation_rules, ticket_canned_responses,
           ticket_category_fields, ticket_messages, tickets, ticket_categories;
```

Old ticket history is **not** migrated into the billing app. The schema differs
— tickets there hang off an order and a customer, not a bare server — and a
partial import would leave threads with nobody attached to them.
