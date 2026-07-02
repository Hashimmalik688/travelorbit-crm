# Taste (Continuously Learned by [CommandCode][cmd])

[cmd]: https://commandcode.ai/

# Server Configuration
- When user says "you have access" and "do the remaining steps yourself", proceed autonomously with server configuration (Nginx, SSL, .env updates). Confidence: 0.85

# Code Preferences
See [code-preferences/taste.md](code-preferences/taste.md)
# Styling
- Edit Sneat SCSS source variables directly for theming; do not create override CSS files. Confidence: 0.80
- Use gradients and thoughtfully-designed color combinations for UI theming instead of flat solid color mapping; apply design judgment rather than mechanical hex substitution. Confidence: 0.75
- Use Orbit's design system colors: Primary #332E9E (Royal Indigo), Accent #FF6B35 (Electric Orange), Secondary #D83F87 (Magenta), Dark #20242B (Deep Charcoal), Background #F6F1E8 (Warm Ivory), Sidebar #13162A (Dark Navy). Confidence: 0.80

# Design
See [design/taste.md](design/taste.md)
# Tech Stack
- Use Laravel 12, Livewire 3, Bootstrap 5, Sneat template styles, and PostgreSQL for all features. Confidence: 0.85
- Blade views should extend contentNavbarLayout by default. Confidence: 0.70

# Time Format
- Use 24-hour clock format throughout the CRM for all time inputs and displays (not 12-hour AM/PM). Confidence: 0.75

# Workflow
- After building features, run php artisan migrate followed by php artisan optimize:clear. Confidence: 0.65

# Activity Logging
- Every action in the CRM must be logged in the activity log — including payment charge request approvals, view actions, and any action regardless of who performs it. Nothing is exempt from logging; logging applies to every account and every action, including view/access events. Confidence: 0.92
- Activity log entries and comments are immutable once created — snapshot agent name, avatar_url, and avatar_initials into the log entry at write time; never re-resolve from current user records at read time. Old log entries must never change even if users are renamed, deleted, or their profile photos change. Confidence: 0.85

# Dashboard
- Agent dashboard Fresh KPI must show margin (sale_price minus cost_price) for non-issued bookings, not just raw sold/revenue amounts. Confidence: 0.65
- Dashboard KPI values (Fresh, Issued, Pending) should display with up to 2 decimal places (e.g., £1,234.56). Confidence: 0.70

# Bootstrap 5
- When modals need `backdrop: false`, initialize them manually via `new bootstrap.Modal(el, { backdrop: false })` in JavaScript rather than relying on `data-bs-backdrop="false"` HTML attribute, which is unreliable. Remove `data-bs-toggle` and `data-bs-target` from trigger buttons and use `.show(btn)` to pass `relatedTarget`. Confidence: 0.70

# Communication
- Keep responses brief and action-focused; avoid extra explanation, verbose descriptions, or commentary unless explicitly requested. Confidence: 0.80

# Project Scope
- Never edit files outside the TravelOrbit CRM project directory (/opt/travelorbit-crm) — do not navigate to, read, or modify files in other projects (e.g., taurus-crm) even when investigating errors or searching for patterns. Stay strictly within the current project. Confidence: 0.90

# Role-Based Access
- Only admin has access to user management; regular users, agents, and other roles have no profile page, settings view, or self-service account modification (password, avatar, etc.). Admin manages all user passwords and profile images centrally. Confidence: 0.85
- Admin has universal edit access — every field should be editable for any user and any booking, regardless of status or state restrictions applied to other roles. This includes bypassing "View-Only Access" UI banners and restrictions at both the PHP logic layer and the Blade template layer. Confidence: 0.85
- Sidebar menu visibility and route access must be synchronized — if a role cannot see a page in the sidebar, that role should also be blocked from accessing that page via direct URL. Add role middleware checks to routes to match sidebar role restrictions. Confidence: 0.88

# User Management
- Display the stored plain text password as a visible column in the user management table (index/list view), not only in the edit modal. Confidence: 0.65

# Booking View
See [booking-view/taste.md](booking-view/taste.md)
