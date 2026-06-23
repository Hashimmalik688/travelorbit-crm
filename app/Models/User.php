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

    // ── Role permission helpers ──────────────────────────────────────
    public function isAdmin(): bool   { return in_array($this->role, ['admin', 'manager']); }
    public function isAgent(): bool   { return $this->role === self::ROLE_AGENT; }
    public function isAccounts(): bool { return $this->role === self::ROLE_ACCOUNTS; }
    public function isIssuance(): bool { return $this->role === self::ROLE_ISSUANCE; }
    public function isManager(): bool  { return in_array($this->role, ['admin', 'manager']); }

    public function canCreateBooking(): bool
    {
        return in_array($this->role, ['agent', 'admin', 'manager', 'operations']);
    }

    public function canEditBooking(Booking $booking): bool
    {
        if ($this->isAdmin()) return true;
        return !$booking->isLockedForRole($this->role);
    }

    public function canQueueForIssuance(Booking $booking): bool
    {
        return in_array($this->role, ['agent', 'admin', 'manager', 'operations'])
            && $booking->canQueueForIssuance();
    }

    public function canManageIssuanceQueue(Booking $booking): bool
    {
        return in_array($this->role, ['issuance', 'admin', 'manager'])
            && in_array($booking->booking_status, [Booking::STATUS_ISSUANCE_QUEUE, Booking::STATUS_TICKET_IN_PROCESS]);
    }

    public function canChargePayment(): bool
    {
        return in_array($this->role, ['accounts', 'admin', 'manager']);
    }

    public function canInvoice(Booking $booking): bool
    {
        return in_array($this->role, ['accounts', 'admin', 'manager'])
            && $booking->canInvoice();
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
        'password',
        'password_plaintext',
        'role',
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
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
