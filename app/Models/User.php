<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // ── Role constants ──────────────────────────────────────────────
    const ROLE_AGENT     = 'agent';
    const ROLE_ACCOUNTS  = 'accounts';
    const ROLE_ISSUANCE  = 'issuance';
    const ROLE_MANAGER   = 'manager';
    const ROLE_ADMIN     = 'admin';
    const ROLE_OPERATIONS = 'operations';

    const ROLE_LABELS = [
        'agent'      => 'Agent',
        'accounts'   => 'Accounts Manager',
        'issuance'   => 'Issuance Manager',
        'manager'    => 'Manager',
        'admin'      => 'Admin',
        'operations' => 'Operations',
    ];

    // ── Position badge helpers (role is now cosmetic) ────────────────
    public function isAdmin(): bool    { return $this->role === self::ROLE_ADMIN; }
    public function isManager(): bool  { return $this->role === self::ROLE_MANAGER; }
    public function isAgent(): bool    { return $this->role === self::ROLE_AGENT; }
    public function isAccounts(): bool { return $this->role === self::ROLE_ACCOUNTS; }
    public function isIssuance(): bool { return $this->role === self::ROLE_ISSUANCE; }

    // ── God mode ─────────────────────────────────────────────────────
    // The top-level 'admin' (CEO) operates untracked: their actions are
    // never written to any activity/audit trail.
    public function isGodMode(): bool { return $this->role === self::ROLE_ADMIN; }

    /** True when the currently authenticated actor is a god-mode admin. */
    public static function actingAsGodMode(): bool
    {
        return auth()->check() && auth()->user()->isGodMode();
    }

    // ── Permission checks ────────────────────────────────────────────
    /** Admin is a hard-wired super-user; everyone else is governed by the checkboxes. */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === self::ROLE_ADMIN) return true;
        return in_array($permission, $this->permissions ?? [], true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->role === self::ROLE_ADMIN) return true;
        return count(array_intersect($permissions, $this->permissions ?? [])) > 0;
    }

    /** Every permission key this user effectively holds (admin => everything). */
    public function allowedPermissions(): array
    {
        if ($this->role === self::ROLE_ADMIN) {
            return array_keys(config('permissions.permissions'));
        }
        return $this->permissions ?? [];
    }

    /** Whether the user sees all agents' data rather than only their own. */
    public function canViewAllData(): bool
    {
        return $this->hasPermission('data.view_all');
    }

    /** Default permission set for a role — pre-fills the checkbox matrix / backfill. */
    public static function presetPermissions(string $role): array
    {
        $preset = config("permissions.presets.$role", []);
        if ($preset === ['*']) {
            return array_keys(config('permissions.permissions'));
        }
        return $preset;
    }

    // ── Booking capability helpers (thin wrappers over permissions) ──
    public function canCreateBooking(): bool
    {
        return $this->hasPermission('bookings.create');
    }

    public function canEditBooking(Booking $booking): bool
    {
        if ($this->isAdmin()) return true;
        return !$booking->isLockedForRole($this->role);
    }

    public function canQueueForIssuance(Booking $booking): bool
    {
        return $this->hasPermission('bookings.queue_issuance')
            && $booking->canQueueForIssuance();
    }

    public function canManageIssuanceQueue(Booking $booking): bool
    {
        return $this->hasPermission('issuance.manage')
            && in_array($booking->booking_status, [Booking::STATUS_ISSUANCE_QUEUE, Booking::STATUS_TICKET_IN_PROCESS]);
    }

    public function canChargePayment(): bool
    {
        return $this->hasPermission('payments.charge');
    }

    public function canInvoice(Booking $booking): bool
    {
        return $this->hasPermission('payments.invoice')
            && $booking->canInvoice();
    }

    public function canIssue(Booking $booking): bool
    {
        return $this->hasPermission('payments.issue')
            && $booking->canIssue();
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst($this->role);
    }

    public function dashboardRoute(): string
    {
        return match($this->role) {
            'agent'      => route('agent.dashboard'),
            'accounts'   => route('accounts.dashboard'),
            'issuance'   => route('issuance.dashboard'),
            'manager',
            'admin'      => route('dashboard'),
            default      => route('dashboard'),
        };
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'password',
        'password_plaintext',
        'role',
        'permissions',
        'is_active',
        'status',
        'profile_photo_path',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_plaintext',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
