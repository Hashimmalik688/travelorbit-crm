<div wire:poll.8s="$refresh">
<style>
.neo-sb-wrap { background:#EAEEF3;border-radius:20px;box-shadow:8px 8px 16px #C4CBD6,-8px -8px 16px #FFFFFF;overflow:hidden;height:100%; }
.neo-sb-hdr { padding:16px 20px 12px;display:flex;align-items:center;justify-content:space-between; }
.neo-sb-col { padding:14px 18px 18px; }
.neo-sb-label { font-size:0.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px; }
.neo-sb-row {
  display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:12px;margin-bottom:8px;
  background:#EAEEF3;box-shadow:3px 3px 6px #C4CBD6,-3px -3px 6px #FFFFFF;
}
.neo-sb-avatar { width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.696rem;font-weight:800;flex-shrink:0; }
.neo-sb-count { font-size:0.84rem;font-weight:800; }
.neo-sb-chip {
  display:inline-block;font-size:0.792rem;color:#64748B;background:#EAEEF3;border-radius:20px;padding:5px 13px;margin:0 6px 8px 0;
  box-shadow:inset 2px 2px 4px #C4CBD6,inset -2px -2px 4px #FFFFFF;
}
.neo-sb-empty { font-size:0.816rem;color:#94A3B8; }
</style>
  <div class="neo-sb-wrap">
    <div class="neo-sb-hdr">
      <h6 class="fw-bold mb-0" style="font-size:0.912rem;color:#0F172A;">Agent Leaderboard</h6>
      <span style="font-size:0.756rem;color:#475569;">{{ now()->format('d F Y') }}</span>
    </div>
    <div class="row g-0">
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
