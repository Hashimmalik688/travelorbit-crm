<div wire:poll.8s="$refresh" style="flex:1 1 auto;width:100%;display:flex;">
<style>
/* Class names are historical (neo-*); these surfaces are now FLAT to match
   the single white card language in layouts/sections/design-v5.blade.php.
   Geometry is unchanged — only fill, border and shadow. */
.neo-sb-wrap { background:#FFFFFF;border:1px solid var(--to-border);border-radius:14px;box-shadow:0 1px 2px rgba(15,23,42,0.04);overflow:hidden;flex:1 1 auto;width:100%;display:flex;flex-direction:column; }
.neo-sb-hdr { padding:12px 18px 9px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--to-border); }
.neo-sb-col { padding:11px 16px 13px; }
.neo-sb-label { font-size:0.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px; }
.neo-sb-row {
  display:flex;align-items:center;gap:10px;padding:6px 11px;border-radius:10px;margin-bottom:6px;
  background:var(--to-page);border:1px solid var(--to-border);box-shadow:none;
}
.neo-sb-avatar { width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.696rem;font-weight:800;flex-shrink:0; }
.neo-sb-count { font-size:0.84rem;font-weight:800; }
.neo-sb-chip {
  display:inline-block;font-size:0.792rem;color:#475569;background:var(--to-subtle);border:1px solid var(--to-border);
  border-radius:20px;padding:5px 13px;margin:0 6px 8px 0;box-shadow:none;
}
.neo-sb-empty { font-size:0.816rem;color:#94A3B8; }
</style>
  <div class="neo-sb-wrap">
    <div class="neo-sb-hdr">
      <h6 class="fw-bold mb-0" style="font-size:0.912rem;color:#0F172A;">Agent Leaderboard</h6>
      <span style="font-size:0.756rem;color:#475569;">{{ now()->format('d F Y') }}</span>
    </div>
    <div class="row g-0 flex-grow-1">
      <div class="col-md-6 neo-sb-col" style="border-right:1px solid rgba(51,46,158,.07);">
        <div class="neo-sb-label" style="color:#16A34A;">Selling Today</div>
        @forelse ($sellingToday as $idx => $ag)
          @php
            $initials = strtoupper(substr($ag->name,0,1).(strpos($ag->name,' ')!==false?substr($ag->name,strpos($ag->name,' ')+1,1):''));
            $colors = ['#332E9E','#D83F87','#D97706','#16A34A','#0EA5E9','#7C3AED','#DC2626','#F59E0B'];
            $c = $colors[$idx % count($colors)];
          @endphp
          <div wire:key="agent-{{ $ag->id }}" data-agent-id="{{ $ag->id }}" class="neo-sb-row">
            <div class="neo-sb-avatar" style="background:{{ $c }}18;color:{{ $c }};">{{ $initials }}</div>
            <div class="flex-grow-1 min-width-0" style="font-size:0.84rem;font-weight:600;color:#1E293B;">{{ $ag->name }}</div>
            <div class="neo-sb-count" style="color:{{ $c }};">{{ $ag->today_bookings }}</div>
          </div>
        @empty
          <div class="neo-sb-empty">No sales yet today.</div>
        @endforelse
      </div>
      <div class="col-md-6 neo-sb-col">
        <div class="neo-sb-label" style="color:#94A3B8;">😎 Chill Squad</div>
        @forelse ($chillToday as $ag)
          <span wire:key="agent-{{ $ag->id }}" data-agent-id="{{ $ag->id }}" class="neo-sb-chip">{{ $ag->name }}</span>
        @empty
          <div class="neo-sb-empty">Everyone's selling today 🔥</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
