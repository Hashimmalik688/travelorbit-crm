<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallCenterFollowup extends Model
{
    protected $table = 'callcenter_followups';

    protected $fillable = ['inquiry_id', 'user_id', 'due_at', 'status', 'notes'];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(CallCenterInquiry::class, 'inquiry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
