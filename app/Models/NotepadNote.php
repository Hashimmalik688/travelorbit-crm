<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One note per user (see NotepadController::index — always firstOrCreate'd
 * on user_id, never a second row). Managers/admins can read everyone's note
 * via canViewAllData(); only the owner can edit theirs.
 */
class NotepadNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
