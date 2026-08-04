{{-- Notepad — one note per user. Explicit Save button only (no autosave).
     Managers/admins additionally see everyone else's note, read-only.
     Opens in its own tab (see verticalMenu.json), so it uses the bare
     layout — no sidebar, no top search bar, just the page. --}}
@extends('layouts/blankLayout')

@section('title', 'Notepad')

@section('page-style')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lora:ital@1&display=swap');

body {
    background: radial-gradient(1200px 500px at 15% -10%, rgba(51,46,158,.05), transparent),
                radial-gradient(900px 500px at 100% 0%, rgba(124,58,237,.05), transparent),
                #F6F7FC;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

#npShell {
    --accent: #332E9E;
    --accent-2: #7C3AED;
    --bg-card: #FFFFFF;
    --bg-page: #F8FAFF;
    --text-primary: #0F172A;
    --text-secondary: #475569;
    --text-muted: #94A3B8;
    --border-color: rgba(51,46,158,.09);
    max-width: 1360px;
    margin: 0 auto;
    padding: 40px 24px 24px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.np-eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--accent);
    opacity: .7;
    margin-bottom: 4px;
}
.np-page-title {
    font-size: 1.56rem;
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--text-primary);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.np-page-title-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 16px rgba(51,46,158,.25);
    flex-shrink: 0;
}
.np-page-title-icon i { color: #fff; font-size: 1.2rem; }

.np-wrap { display: flex; gap: 20px; align-items: flex-start; }

.np-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(15,23,42,.03), 0 16px 40px -20px rgba(51,46,158,.18);
    overflow: hidden;
}

/* ── Editor ── */
.np-editor-card { flex: 1; min-width: 0; }
.np-editor-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 18px 26px;
    background: linear-gradient(180deg, #FCFCFF, #FFFFFF);
    border-bottom: 1px solid var(--border-color);
    flex-wrap: wrap;
}
.np-editor-title { font-size: 1.06rem; font-weight: 700; color: var(--text-primary); letter-spacing: -.01em; }
.np-status {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.np-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .5; }
.np-status.dirty { color: #D97706; }
.np-status.saving { color: var(--accent); }
.np-status.saved { color: #16A34A; }
.np-save-btn {
    background: linear-gradient(135deg, var(--accent), #241F7A);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: 0.876rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 6px 16px rgba(51,46,158,.28);
    transition: opacity .12s, transform .12s;
}
.np-save-btn:hover:not(:disabled) { opacity: .92; transform: translateY(-1px); }
.np-save-btn:disabled { opacity: .35; cursor: default; box-shadow: none; }

.np-textarea {
    width: 100%;
    height: calc(100vh - 260px);
    min-height: 360px;
    background: var(--bg-card);
    border: none;
    color: var(--text-primary);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 15.5px;
    font-weight: 400;
    line-height: 1.85;
    letter-spacing: -.003em;
    padding: 2rem 2.4rem;
    resize: none;
    outline: none;
}
.np-textarea::placeholder { color: var(--text-muted); opacity: .55; font-style: italic; font-family: 'Lora', Georgia, serif; }
.np-textarea[readonly] { background: var(--bg-page); cursor: default; }
.np-textarea::selection { background: rgba(51,46,158,.15); }

.np-statusbar {
    display: flex;
    justify-content: flex-end;
    gap: 1.2rem;
    padding: 10px 26px;
    background: #FCFCFF;
    border-top: 1px solid var(--border-color);
    font-size: 0.74rem;
    font-weight: 500;
    color: var(--text-muted);
}

/* ── Team panel (managers/admins only) ── */
.np-team-card { width: 320px; flex-shrink: 0; }
.np-team-hdr {
    padding: 18px 22px;
    background: linear-gradient(180deg, #FCFCFF, #FFFFFF);
    border-bottom: 1px solid var(--border-color);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-secondary);
}
.np-team-list { max-height: calc(100vh - 220px); overflow-y: auto; }
.np-team-item { border-bottom: 1px solid var(--border-color); }
.np-team-item:last-child { border-bottom: none; }
.np-team-item-hdr {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px 22px;
    cursor: pointer;
    transition: background .12s;
}
.np-team-item-hdr:hover { background: var(--bg-page); }
.np-team-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-2), #C026D3);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.np-team-item-name { font-size: 0.888rem; font-weight: 600; color: var(--text-primary); }
.np-team-item-meta { font-size: 0.68rem; color: var(--text-muted); font-weight: 500; margin-top: 1px; }
.np-team-item-body {
    padding: 2px 22px 16px 65px;
    font-size: 0.828rem;
    color: var(--text-secondary);
    white-space: pre-wrap;
    word-break: break-word;
    line-height: 1.7;
}
.np-team-empty { padding: 2.5rem 1rem; text-align: center; font-size: 0.8rem; color: var(--text-muted); opacity: .55; }

/* ── Team note full-view modal ── */
.np-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.66);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    z-index: 1050;
}
.np-modal {
    /* Hardcoded (not var(--bg-card) etc.) — this markup is teleported to
       <body> via x-teleport, outside the #npShell scope those custom
       properties are defined on, so they'd resolve to nothing here and
       leave the card transparent. */
    background: #FFFFFF;
    border-radius: 18px;
    box-shadow: 0 24px 60px -20px rgba(15,23,42,.35);
    width: 100%;
    max-width: 640px;
    max-height: 82vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.np-modal-hdr {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(51,46,158,.09);
    flex-shrink: 0;
}
.np-modal-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7C3AED, #C026D3);
    color: #fff;
    font-size: 0.86rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.np-modal-name { font-size: 1.02rem; font-weight: 700; color: #0F172A; }
.np-modal-meta { font-size: 0.76rem; color: #94A3B8; font-weight: 500; margin-top: 1px; }
.np-modal-close {
    margin-left: auto;
    width: 32px; height: 32px;
    border-radius: 8px;
    border: none;
    background: #F8FAFF;
    color: #475569;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
}
.np-modal-close:hover { background: rgba(51,46,158,.09); }
.np-modal-body {
    padding: 24px;
    overflow-y: auto;
    font-size: 0.94rem;
    line-height: 1.85;
    color: #0F172A;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>
@endsection

@section('content')
<div id="npShell"
     x-data="{
        content: {{ Js::from($note->content ?? '') }},
        savedContent: {{ Js::from($note->content ?? '') }},
        saving: false,
        savedAt: {{ Js::from($note->updated_at?->diffForHumans()) }},
        get dirty() { return this.content !== this.savedContent; },
        get words() { const t = this.content.trim(); return t ? t.split(/\s+/).length : 0; },
        async save() {
            if (this.saving) return;
            this.saving = true;
            try {
                const res = await fetch('{{ route('notepad.update') }}', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ content: this.content }),
                });
                const data = await res.json();
                if (data.success) {
                    this.savedContent = this.content;
                    this.savedAt = data.updated_ago;
                }
            } finally {
                this.saving = false;
            }
        }
     }"
     @keydown.window.ctrl.s.prevent="save()"
     @keydown.window.meta.s.prevent="save()"
     @beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }">

  <div class="np-eyebrow">Personal</div>
  <div class="np-page-title">
    <div class="np-page-title-icon"><i class="ph ph-note-pencil"></i></div>
    My Notepad
  </div>

  <div class="np-wrap">
    <div class="np-card np-editor-card">
      <div class="np-editor-hdr">
        <div class="np-editor-title">Notes</div>
        <div class="d-flex align-items-center gap-3">
          <span class="np-status" :class="{ dirty, saving }" x-text="saving ? 'Saving…' : (dirty ? 'Unsaved changes' : (savedAt ? 'Saved ' + savedAt : ''))"></span>
          <button type="button" class="np-save-btn" :disabled="saving || !dirty" @click="save()">
            <i class="ph ph-floppy-disk"></i> Save
          </button>
        </div>
      </div>
      {{-- data-gramm/data-gramm_editor/data-enable-grammarly tell Grammarly
           (and similar writing-assistant extensions) to skip this field —
           this is a work notepad, not prose that needs a floating checker
           icon covering the corner of it. --}}
      <textarea class="np-textarea" placeholder="Start typing…" x-model="content"
                spellcheck="false"
                data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false"></textarea>
      <div class="np-statusbar">
        <span x-text="words + ' word' + (words !== 1 ? 's' : '')"></span>
        <span x-text="content.length + ' char' + (content.length !== 1 ? 's' : '')"></span>
      </div>
    </div>

    @if($teamNotes !== null)
      <div class="np-card np-team-card" x-data="{ selected: null }">
        <div class="np-team-hdr">Team Notes</div>
        <div class="np-team-list">
          @forelse($teamNotes as $tn)
            @php
              $tnName = $tn->user->name ?? 'Unknown';
              $tnParts = explode(' ', trim($tnName));
              $tnInitials = strtoupper(mb_substr($tnParts[0] ?? '?', 0, 1) . (count($tnParts) > 1 ? mb_substr(end($tnParts), 0, 1) : ''));
              $tnMeta = [
                'id' => $tn->id,
                'name' => $tnName,
                'initials' => $tnInitials,
                'meta' => $tn->updated_at->diffForHumans(),
                'content' => $tn->content ?: '(empty)',
              ];
            @endphp
            <div class="np-team-item">
              <div class="np-team-item-hdr" @click="selected = {{ Js::from($tnMeta) }}">
                <div class="np-team-avatar">{{ $tnInitials }}</div>
                <div class="flex-grow-1 min-width-0">
                  <div class="np-team-item-name">{{ $tnName }}</div>
                  <div class="np-team-item-meta">{{ $tn->updated_at->diffForHumans() }}</div>
                </div>
              </div>
            </div>
          @empty
            <div class="np-team-empty">No other users yet.</div>
          @endforelse
        </div>

        <template x-teleport="body">
          <div class="np-modal-overlay" x-show="selected" x-cloak @click.self="selected = null" @keydown.window.escape="selected = null">
            <div class="np-modal" x-show="selected">
              <template x-if="selected">
                <div class="np-modal-hdr">
                  <div class="np-modal-avatar" x-text="selected.initials"></div>
                  <div class="flex-grow-1 min-width-0">
                    <div class="np-modal-name" x-text="selected.name"></div>
                    <div class="np-modal-meta" x-text="selected.meta"></div>
                  </div>
                  <button type="button" class="np-modal-close" @click="selected = null"><i class="ph ph-x"></i></button>
                </div>
              </template>
              <div class="np-modal-body" x-text="selected ? selected.content : ''"></div>
            </div>
          </div>
        </template>
      </div>
    @endif
  </div>
</div>
@endsection
