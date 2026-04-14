# Tickets

A simple ticket system plugin for [Pelican Panel](https://pelican.dev) that allows users to submit support requests from the server panel and administrators to manage them.

## Features

- Users can open tickets directly from their server panel
- Ticket categories: Question, Issue, Feedback
- Ticket priorities: Low, Normal, High, Very High
- Ticket status tracking: Open, In Progress, Closed
- Admin assignment — assign tickets to specific administrators
- "Assign to me" quick action for admins
- Admin notes (hidden messages, not visible to users)
- In-app notifications sent to ticket authors on closure
- Tabbed views for filtering tickets (Open, Closed, All, Assigned to me)
- English and German (de) translations included

## Requirements

- [Pelican Panel](https://pelican.dev) (latest)

## Installation

1. Download the plugin and place it in your panel's `plugins/` directory under the `tickets` folder.
2. Run the plugin migrations:
   ```bash
   php artisan migrate
   ```
3. The plugin will be automatically discovered and loaded by the panel.

## License

This plugin is open-sourced under the [GNU General Public License v3.0](LICENSE).
