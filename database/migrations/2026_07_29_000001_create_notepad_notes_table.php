<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notepad — ported from taurus-crm. A personal, VS-Code-style note editor
 * available to every authenticated user, with optional per-note sharing
 * (see notepad_note_shares).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notepad_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('color', 20)->default('#ffffff');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notepad_notes');
    }
};
