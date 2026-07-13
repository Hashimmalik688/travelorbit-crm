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
    'groups' => ['Bookings', 'Customers', 'Issuance', 'Accounts', 'Reports', 'Data'],

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
            'label'       => 'Approve & process refunds',
            'group'       => 'Accounts',
            'icon'        => 'ph ph-arrow-u-up-left',
            'description' => 'Change refund status (review, approve, reject, process).',
        ],

        // ── Reports ──────────────────────────────────────────────
        'reports.view' => [
            'label'       => 'View reports & sales',
            'group'       => 'Reports',
            'icon'        => 'ph ph-chart-bar',
            'description' => 'Open the Reports hub and sales reports.',
        ],
        'reports.performance' => [
            'label'       => 'Agent performance report',
            'group'       => 'Reports',
            'icon'        => 'ph ph-trend-up',
            'description' => 'Open the Agent Performance report.',
        ],

        // ── Data scope ───────────────────────────────────────────
        'data.view_all' => [
            'label'       => "View all agents' data",
            'group'       => 'Data',
            'icon'        => 'ph ph-globe',
            'description' => "See every agent's data (Call Desk, performance, notifications) instead of only your own.",
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
            'reports.performance',
        ],
        'operations' => [
            'bookings.create',
            'bookings.queue_issuance',
            'customers.view',
            'reports.performance',
        ],
        'accounts' => [
            'customers.view',
            'accounts.access',
            'payments.charge',
            'payments.invoice',
            'payments.issue',
            'reports.view',
            'reports.performance',
            'data.view_all',
        ],
        'issuance' => [
            'issuance.access',
            'issuance.manage',
        ],
        // Manager had full access EXCEPT the admin-only "All Bookings" page,
        // so its preset is everything but `bookings.view_all`.
        'manager' => [
            'bookings.create', 'bookings.queue_issuance',
            'bookings.delete', 'bookings.edit_any', 'customers.view',
            'issuance.access', 'issuance.manage', 'accounts.access',
            'payments.charge', 'payments.invoice', 'payments.issue',
            'reports.view', 'reports.performance', 'refunds.manage', 'data.view_all',
        ],
        // admin is a hard-wired super-user; '*' just pre-ticks every box in the UI.
        'admin' => ['*'],
    ],
];
