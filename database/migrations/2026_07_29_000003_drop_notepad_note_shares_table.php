<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notepad is now one note per user, visible to managers via canViewAllData()
 * rather than per-note sharing — the sharing pivot is no longer used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('notepad_note_shares');
    }

    public function down(): void
    {
        Schema::create('notepad_note_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notepad_notes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['note_id', 'user_id']);
        });
    }
};
