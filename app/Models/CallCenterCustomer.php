<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CallCenterCustomer extends Model
{
    protected $table = 'callcenter_customers';

    protected $fillable = ['user_id', 'name', 'phone', 'email', 'city'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(CallCenterInquiry::class, 'customer_id');
    }

    /**
     * Managers see the shared pool; other agents only ever match customers
     * they personally created, so a repeat caller another agent already
     * logged looks like a new customer to them (siloed per agent by design).
     */
    public static function findByPhone(string $phone, ?User $scopeTo = null): ?self
    {
        return static::where('phone', $phone)
            ->when($scopeTo && ! $scopeTo->canViewAllData(), fn ($q) => $q->where('user_id', $scopeTo->id))
            ->first();
    }
}
