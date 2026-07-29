<?php

namespace App\Http\Controllers;

use App\Models\NotepadNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Notepad — one note per user, no permission gate to use it. Managers/admins
 * additionally see (read-only) every other user's note, via the same
 * canViewAllData() gate the rest of the app uses for cross-agent visibility.
 */
class NotepadController extends Controller
{
    public function index()
    {
        $note = NotepadNote::firstOrCreate(
            ['user_id' => Auth::id()],
            ['content' => '']
        );

        $teamNotes = Auth::user()->canViewAllData()
            ? NotepadNote::with('user:id,name')
                ->where('user_id', '!=', Auth::id())
                ->orderByDesc('updated_at')
                ->get()
            : null;

        return view('notepad.index', compact('note', 'teamNotes'));
    }

    /** Updates the current user's own note. There is never a second one to target. */
    public function update(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
        ]);

        $note = NotepadNote::firstOrCreate(
            ['user_id' => Auth::id()],
            ['content' => '']
        );
        $note->update(['content' => $request->input('content', '')]);

        return response()->json([
            'success'     => true,
            'updated_at'  => $note->updated_at->toIso8601String(),
            'updated_ago' => $note->updated_at->diffForHumans(),
        ]);
    }
}
