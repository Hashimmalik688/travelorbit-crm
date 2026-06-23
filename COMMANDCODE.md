# TravelOrbit CRM

## Stack
- Laravel 12 (PHP ^8.2), Livewire 3, Bootstrap 5 (Sneat admin template), PostgreSQL, Redis
- Custom session-based auth, no Sanctum/Passport
- Vite 6 for frontend build, Sass for CSS (custom Design System v4.0 in resources/css/app.css)
- Phosphor Icons via Iconify (`ph ph-*` classes with custom Vite plugin)

## Routing
Single file: `routes/web.php`. Role-gated groups via `role:admin,manager,etc` middleware.
No API routes, no Reverb channel routes (auto-discovery).

## Auth
- `LoginController`: session login + `is_active` check
- Roles: `admin`, `manager`, `operations`, `agent`, `accounts`, `issuance` — stored as string in `users.role`
- `CheckRole` middleware aliased as `role`
- Booking authorization: `BookingPolicy` with workflow gate-checks, `Booking::isLockedForRole()`, `User` helper methods
- Rate limits: login 10/min, web 300/min (AppServiceProvider)

## Architecture Pattern
**MVC + rich models + Livewire**: Most business logic lives in Livewire components and Eloquent models. Traditional controllers are thin routing shells.

## Key Directories
| Path | Purpose |
|------|---------|
| `app/Http/Controllers/` | Thin controllers (Auth, Dashboard, Crm, Finance, Mis, Settings) |
| `app/Livewire/` | All interactive logic — 14 components (BookingIndex, BookingShow, BookingEdit, CreateBooking, PaymentIndex, RefundIndex, SalesReport, AgentPerformance, GlobalSearch, NotificationBell, UserManagement, UserProfile, AuditLogViewer, IpWhitelistManager) |
| `app/Models/` | 17 Eloquent models — Booking is the central hub |
| `app/Policies/` | BookingPolicy only |
| `app/Services/` | AuditLogger only (booking-specific audit) |
| `app/Http/Middleware/` | CheckRole, BotDetection, LogUserActivity, SecurityHeaders |
| `resources/views/livewire/` | Livewire Blade views (kebab-case, matching component class names) |
| `resources/views/content/` | Traditional Blade views (dashboard, bookings, crm, finance, mis, reports, settings) |
| `resources/views/layouts/` | commonMaster → contentNavbarLayout (default); blankLayout for login |
| `resources/menu/verticalMenu.json` | Role-based navigation menu |
| `resources/css/app.css` | Design System v4.0 — custom brand CSS |
| `database/migrations/` | 37 migrations, all 2026 dates |

## Core Models
**Booking** is the central entity — belongsTo Customer/User, hasMany (passengers, comments, documents, flightCosts, hotels, transfers, refunds, paymentHistory, activityLogs), hasOne (flightDetail, payment).
Accessors: `total_cost_price`, `total_sale_price`, `total_margin` (computed).
SoftDeletes with `booking_number` auto-increment (withTrashed to avoid reuse).

## Workflow State Machine
```
PENDING → ISSUANCE_QUEUE → TICKET_IN_PROCESS → INVOICED → CONFIRMED
  ↓           ↓
CANCELLED   ← restoreToPending
  ↓
REFUND_QUEUE
```
- Agent: creates booking (PENDING), queues for issuance. Locked out once in issuance queue.
- Issuance: ISSUANCE_QUEUE → TICKET_IN_PROCESS. Can restore to PENDING.
- Accounts: TICKET_IN_PROCESS → INVOICED, manages payments.
- Admin/Manager: full access.
- Transitions in `BookingWorkflowController` (5 actions), gated by `BookingPolicy`.

## Key Conventions
- PHP: `Attribute::make` accessor style (Laravel 9+), no old `getXAttribute`
- Blade views: kebab-case directories, dot notation in routes
- Constants: UPPER_SNAKE on models (`Booking::STATUS_PENDING`, `User::ROLE_ADMIN`)
- Model relationships: explicit return type hints
- Middleware pipeline: SecurityHeaders → BotDetection → LogUserActivity (applied globally to web)
- Livewire namespace: `App\Livewire` (configured in config/livewire.php)

## Audit Systems
1. System-wide: `AuditLog` model + `AuditLog::logAction()` static
2. Booking-specific: `BookingActivityLog` model + `AuditLogger` service class (new/old value tracking)

## Taste Preferences (see .commandcode/taste/)
- Sneat SCSS variables for theming (no CSS overrides)
- Orbit design system colors: Primary #332E9E, Accent #FF6B35, Secondary #D83F87, Dark #20242B, Background #F6F1E8, Sidebar #13162A
- 24-hour time format throughout
- After features: `php artisan migrate` then `php artisan optimize:clear`
- Blade views extend contentNavbarLayout by default
