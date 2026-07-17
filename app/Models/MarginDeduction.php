<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarginDeduction extends Model
{
    protected $fillable = [
        'user_id',
        'applied_by_user_id',
        'amount',
        'reason',
        'deduction_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'deduction_date'  => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }
}
