<?php

/*
|--------------------------------------------------------------------------
| Permissions registry
|--------------------------------------------------------------------------
|
| Single source of truth for the permission-checkbox access model. `role`
| on the users table is now just a position badge; what a user can actually
| see/do is driven by the `permissions` JSON column, whose valid values are
| the keys below.
|
| `admin` is the only hard-wired super-user (see User::hasPermission) and is
| NOT governed by these checkboxes. Settings / user-management stays behind
| the `role:admin` route middleware and is intentionally not a permission.
|
| `groups` fixes the display order of the checkbox matrix. `presets` maps a
| role to the default set of permissions it starts with — these mirror the
| access each role had under the old role-based system, so backfilling from
| them keeps behaviour identical on deploy day.
|
*/

return [

    // Display order for the checkbox matrix, grouped by the `group` field below.
    'groups' => ['Bookings', 'Customers', 'Issuance', 'Accounts', 'Reports', 'Tools', 'Attendance', 'Data', 'System'],

    'permissions' => [
        // ── Bookings ──────────────────────────────────────────────
        'bookings.create' => [
            'label'       => 'Create & edit bookings',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-plus-circle',
            'description' => 'Create new bookings and edit them through the workflow.',
        ],
        'bookings.queue_issuance' => [
            'label'       => 'Send to issuance queue',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-paper-plane-tilt',
            'description' => 'Push a booking into the issuance queue.',
        ],
        'bookings.view_all' => [
            'label'       => 'View all bookings',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-list',
            'description' => 'See the company-wide "All Bookings" list.',
        ],
        'bookings.delete' => [
            'label'       => 'Delete bookings',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-trash',
            'description' => 'Soft-delete a booking.',
        ],
        'bookings.edit_any' => [
            'label'       => 'Edit any booking',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-lock-key-open',
            'description' => "Edit bookings that aren't yours, and edit past workflow locks (supervisor override).",
        ],
        'bookings.share_margin' => [
            'label'       => 'Share booking margin',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-share-network',
            'description' => 'Share part of a booking\'s margin with another user.',
        ],
        'bookings.transfer' => [
            'label'       => 'Transfer booking ownership',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-arrows-left-right',
            'description' => "Move a booking to another user — its sale, margin and refunds count toward the new owner from then on. Works at any stage, no workflow lock applies.",
        ],
        'bookings.own_issued' => [
            'label'       => 'Own issued Payment lists',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-calendar-check',
            'description' => "See your own Payment Plan and Payment Awaiting lists (issued bookings still owing).",
        ],
        'bookings.date_change' => [
            'label'       => 'Date change a booking',
            'group'       => 'Bookings',
            'icon'        => 'ph ph-calendar-x',
            'description' => 'Start a Date Change from an existing booking — opens a new, pre-filled booking linked back to the original.',
        ],

        // ── Customers ─────────────────────────────────────────────
        'customers.view' => [
            'label'       => 'Access customers',
            'group'       => 'Customers',
            'icon'        => 'ph ph-users',
            'description' => 'Browse the customers directory.',
        ],

        // ── Issuance ──────────────────────────────────────────────
        'issuance.access' => [
            'label'       => 'Access issuance queue',
            'group'       => 'Issuance',
            'icon'        => 'ph ph-ticket',
            'description' => 'Open the issuance dashboard / queue.',
        ],
        'issuance.manage' => [
            'label'       => 'Process issuance queue',
            'group'       => 'Issuance',
            'icon'        => 'ph ph-gear-six',
            'description' => 'Mark tickets in-process, remove from or restore the queue.',
        ],
        'bookings.change_status' => [
            'label'       => 'Change booking status',
            'group'       => 'Issuance',
            'icon'        => 'ph ph-arrows-left-right',
            'description' => 'Open the Status Change page and set any status on a booking in the issuance pipeline. Applies the same date stamps and audit trail as the normal workflow buttons.',
        ],

        // ── Accounts / Payments ──────────────────────────────────
        'accounts.access' => [
            'label'       => 'Access accounts & payments',
            'group'       => 'Accounts',
            'icon'        => 'ph ph-bank',
            'description' => 'Open the Accounts hub, Payments, Refunds and Charge Requests.',
        ],
        'payments.charge' => [
            'label'       => 'Charge & decline payments',
            'group'       => 'Accounts',
            'icon'        => 'ph ph-credit-card',
            'description' => 'Take card payments and decline charge requests.',
        ],
        'payments.invoice' => [
            'label'       => 'Invoice bookings',
            'group'       => 'Accounts',
            'icon'        => 'ph ph-receipt',
            'description' => 'Invoice an issued booking.',
        ],
        'payments.issue' => [
            'label'       => 'Issue tickets',
            'group'       => 'Accounts',
            'icon'        => 'ph ph-seal-check',
            'description' => 'Issue tickets for a ticket-in-process booking.',
        ],
        'refunds.manage' => [
            'label'       => 'M&R Auth Queue',
            'group'       => 'Accounts',
            'icon'        => 'ph ph-arrow-u-up-left',
            'description' => 'Approve or decline Refund to Customer payouts, adjust the amount actually paid, and choose whether to release or hold the margin claimed on the difference.',
        ],

        // ── Reports ──────────────────────────────────────────────
        'reports.view' => [
            'label'       => 'View reports & sales',
            'group'       => 'Reports',
            'icon'        => 'ph ph-chart-bar',
            'description' => 'Open the Reports hub and sales reports.',
        ],
        'reports.performance' => [
            'label'       => 'My performance report',
            'group'       => 'Reports',
            'icon'        => 'ph ph-trend-up',
            'description' => "Open the performance report scoped to the user's own bookings only.",
        ],
        'reports.performance_all' => [
            'label'       => 'All-agents performance report',
            'group'       => 'Reports',
            'icon'        => 'ph ph-users-four',
            'description' => "See every agent's numbers in the performance report (not just your own), with the agent/month filters.",
        ],
        'reports.departure_arrival' => [
            'label'       => 'Departure/Arrival report',
            'group'       => 'Reports',
            'icon'        => 'ph ph-airplane-tilt',
            'description' => "Open the Departure/Arrival report. Without data.view_all it's scoped to the user's own bookings.",
        ],
        'reports.apply_deduction' => [
            'label'       => 'Apply margin deductions',
            'group'       => 'Reports',
            'icon'        => 'ph ph-minus-circle',
            'description' => "Apply or remove a margin deduction against an agent's performance report.",
        ],

        // ── Tools ────────────────────────────────────────────────
        'eticket.access' => [
            'label'       => 'E-Ticket Builder',
            'group'       => 'Tools',
            'icon'        => 'ph ph-ticket',
            'description' => 'Open the standalone E-Ticket Builder print tool.',
        ],
        'calldesk.access' => [
            'label'       => 'Call Desk',
            'group'       => 'Tools',
            'icon'        => 'ph ph-phone-call',
            'description' => "Open the Call Desk (inquiries, callbacks, new call). Data is scoped by \"View all agents' data\".",
        ],

        // ── Data scope ───────────────────────────────────────────
        'data.view_all' => [
            'label'       => "View all agents' data",
            'group'       => 'Data',
            'icon'        => 'ph ph-globe',
            'description' => "See every agent's data (Call Desk, performance, notifications) instead of only your own.",
        ],

        // ── System / Settings ────────────────────────────────────
        // Admin is a hard-wired super-user and always passes these; granting a
        // key here lets a non-admin be delegated that one Settings area.
        'settings.users' => [
            'label'       => 'User management',
            'group'       => 'System',
            'icon'        => 'ph ph-users-three',
            'description' => 'Add, edit, delete users and manage their permissions.',
        ],
        'settings.activity' => [
            'label'       => 'Activity / audit log',
            'group'       => 'System',
            'icon'        => 'ph ph-clock-counter-clockwise',
            'description' => 'View the full activity / audit trail.',
        ],
        'settings.vendors' => [
            'label'       => 'Flight vendors',
            'group'       => 'System',
            'icon'        => 'ph ph-airplane-tilt',
            'description' => 'Manage the flight vendor options shown in the booking form.',
        ],
        'settings.gds' => [
            'label'       => 'GDS options',
            'group'       => 'System',
            'icon'        => 'ph ph-globe-hemisphere-west',
            'description' => 'Manage the GDS options (Amadeus, Sabre, etc.) shown in the booking form.',
        ],
        'settings.ip' => [
            'label'       => 'IP whitelist',
            'group'       => 'System',
            'icon'        => 'ph ph-shield-check',
            'description' => 'Manage the IP / CIDR access whitelist.',
        ],
        'settings.attendance' => [
            'label'       => 'Attendance settings',
            'group'       => 'System',
            'icon'        => 'ph ph-sliders',
            'description' => 'Set office hours, the late threshold, and company holidays.',
        ],

        // ── Attendance ────────────────────────────────────────────
        // Note: every user can mark their own attendance ("My Attendance");
        // this permission only gates the company-wide admin roster & history.
        'attendance.view' => [
            'label'       => 'View attendance records',
            'group'       => 'Attendance',
            'icon'        => 'ph ph-calendar-check',
            'description' => "See the company-wide attendance roster and history for all staff.",
        ],
        'attendance.edit' => [
            'label'       => 'Edit attendance records',
            'group'       => 'Attendance',
            'icon'        => 'ph ph-pencil-simple',
            'description' => "Correct staff attendance — edit check-in/out times and status, add a missing day, or remove a wrong entry.",
        ],
    ],

    /*
    | Role → default permission set. Mirrors the old role-based access exactly
    | so a backfill from these presets is behaviour-neutral. `manager` and
    | `admin` get everything; `admin` is a hard-wired super-user regardless.
    */
    'presets' => [
        'agent' => [
            'bookings.create',
            'bookings.queue_issuance',
            'bookings.share_margin',
            'bookings.own_issued',
            'reports.performance',
            'reports.departure_arrival',
            'eticket.access',
            'calldesk.access',
        ],
        'operations' => [
            'bookings.create',
            'bookings.queue_issuance',
            'bookings.share_margin',
            'bookings.own_issued',
            'customers.view',
            'reports.performance',
            'reports.departure_arrival',
            'eticket.access',
            'calldesk.access',
        ],
        'accounts' => [
            'customers.view',
            'accounts.access',
            'payments.charge',
            'payments.invoice',
            'payments.issue',
            'reports.view',
            'reports.performance_all',
            'data.view_all',
            'eticket.access',
            'calldesk.access',
        ],
        'issuance' => [
            'issuance.access',
            'issuance.manage',
            'bookings.change_status',
            'eticket.access',
            'calldesk.access',
        ],
        // Manager had full access EXCEPT the admin-only "All Bookings" page,
        // so its preset is everything but `bookings.view_all` (and the
        // System/Settings keys, which stay admin-delegated).
        'manager' => [
            'bookings.create', 'bookings.queue_issuance', 'bookings.own_issued',
            'bookings.delete', 'bookings.edit_any', 'bookings.share_margin', 'customers.view',
            'issuance.access', 'issuance.manage', 'accounts.access',
            'payments.charge', 'payments.invoice', 'payments.issue',
            'reports.view', 'reports.performance_all', 'reports.apply_deduction', 'refunds.manage', 'data.view_all',
            'eticket.access', 'calldesk.access', 'attendance.view', 'attendance.edit',
        ],
        // admin is a hard-wired super-user; '*' just pre-ticks every box in the UI.
        'admin' => ['*'],
    ],
];
