<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CallCenterCustomer extends Model
{
    protected $table = 'callcenter_customers';

    protected $fillable = ['name', 'phone', 'email', 'city'];

    public function inquiries(): HasMany
    {
        return $this->hasMany(CallCenterInquiry::class, 'customer_id');
    }

    public static function findByPhone(string $phone): ?self
    {
        return static::where('phone', $phone)->first();
    }
}
