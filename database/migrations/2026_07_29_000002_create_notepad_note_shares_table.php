<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notepad — ported from taurus-crm. Pure pivot for NotepadNote::sharedWith():
 * which users a note's owner has given edit access to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notepad_note_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notepad_notes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notepad_note_shares');
    }
};
