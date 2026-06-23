# Agent Wall — Agent Dashboard Feature

## Goal
Add a visual "Agent Wall" section to the agent dashboard showing all agents with large avatars and names. Agents who have made a booking **today** show green; those who haven't show red. This is a daily accountability board visible to all agents.

---

## Approach

### Backend: Add today's agent stats to `DashboardController::agentDashboard()`

**File:** `app/Http/Controllers/DashboardController.php`

In the `agentDashboard()` method, add a query that fetches all agent-role users with their today's booking count:

```php
$allAgents = User::where('role', 'agent')
    ->withCount(['bookings' => function ($query) {
        $query->whereDate('created_at', today());
    }])
    ->orderBy('first_name')
    ->get();
```

Pass `$allAgents` to the view.

### Frontend: Add Agent Wall section to the agent dashboard view

**File:** `resources/views/content/dashboard/agent-dashboard.blade.php`

Add a new section between the "Count Chips" row and the "Main 2-Column Layout" (after count chips, before Recent Bookings + Calendar).

#### Design
- Glass card section with "Agents Today" header
- Responsive grid: `col-6 col-sm-4 col-md-3 col-lg-2`
- Each card: large gradient avatar with initials (like sidebar user avatar), name below
- Status dot bottom-right of avatar: green glow (#10B981) if bookings today > 0, red glow (#F43F5E) if 0
- Hover lift effect, consistent with existing card animations

#### When no agents:
Don't render the section.

---

## Files to Modify

| File | Change |
|------|--------|
| `app/Http/Controllers/DashboardController.php` | Add `$allAgents` query + pass to view |
| `resources/views/content/dashboard/agent-dashboard.blade.php` | Add Agent Wall section |

---

## Verification
1. Agent dashboard shows all agents with avatars
2. Green dot = bookings today, red dot = no bookings today
3. Creating a booking turns that agent green on reload
4. Resets daily (tomorrow all red)
5. Responsive grid works on mobile/desktop
