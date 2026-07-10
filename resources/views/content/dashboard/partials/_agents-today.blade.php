{{-- Agents Today — passport-photo wall of every agent with their booking count for today.
     Shared by the agent dashboard and the admin/manager Operations Centre.
     Expects: $agents (User collection, each with a `bookings_count` of today's bookings). --}}
<div class="ad-up d3" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:20px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);overflow:hidden;">
  <div class="px-4 pt-4 pb-2" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <h6 class="fw-bold mb-0" style="font-size:.87rem;color:#0F172A;">Agents Today</h6>
    <div style="font-size:.65rem;color:#94A3B8;">{{ now()->format('d F Y') }}</div>
  </div>
  <div class="p-3">
    <div style="display:grid;grid-template-columns:repeat(auto-fill, 96px);justify-content:start;gap:8px;">
      @foreach ($agents as $agent)
        @php
          $parts = explode(' ', $agent->name);
          $firstName = $parts[0];
          $initials = strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
          $madeBooking = $agent->bookings_count > 0;
          $photoUrl = $agent->profile_photo_path ? asset('storage/' . $agent->profile_photo_path) : null;
        @endphp
        <div title="{{ $agent->name }} — {{ $madeBooking ? $agent->bookings_count.' booking'.($agent->bookings_count!==1?'s':'').' today' : 'No bookings today' }}"
             style="position:relative;border-radius:10px;overflow:hidden;transition:all .2s ease;border:2px solid {{ $madeBooking ? 'rgba(16,185,129,0.6)' : 'rgba(244,63,94,0.45)' }};box-shadow:0 3px 10px {{ $madeBooking ? 'rgba(16,185,129,0.2)' : 'rgba(244,63,94,0.15)' }};">
          {{-- Passport-photo-sized portrait --}}
          <div style="aspect-ratio:3/4;background:linear-gradient(135deg,#4F46E5 0%,#6366F1 50%,#8B5CF6 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
            @if ($photoUrl)
              <img src="{{ $photoUrl }}" alt="{{ $firstName }}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">
            @else
              <div style="font-size:1.35rem;font-weight:800;color:rgba(255,255,255,0.9);letter-spacing:.04em;">{{ $initials }}</div>
            @endif
            {{-- Status dot --}}
            <span style="position:absolute;top:4px;right:4px;width:9px;height:9px;border-radius:50%;background:{{ $madeBooking ? '#10B981' : '#F43F5E' }};border:2px solid #fff;"></span>
          </div>
          <div style="padding:4px 6px;text-align:center;background:{{ $madeBooking ? 'rgba(16,185,129,0.06)' : 'rgba(244,63,94,0.04)' }};">
            <div style="font-size:.68rem;font-weight:700;color:#1E293B;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $firstName }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
