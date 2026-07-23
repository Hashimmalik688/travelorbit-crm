{{-- Agents Performance — photo wall of every agent with their today-activity
     status and this-month figures. Shared by the admin Operations Centre and
     the agent dashboard.

     Expects:
       $agentsPerformance  collection of {name, profile_photo_path,
                           made_booking_today, margin, count}
       $performanceLabel   'Fresh Margin' | 'Issued Margin'
       $showMargin         bool — agents must NOT see colleagues' margins, so
                           the agent dashboard passes false. When false the
                           £ figure is replaced by the booking count, the
                           header badge is dropped, and the tooltip omits the
                           amount. The caller must also sort by count rather
                           than margin in that case, otherwise the tile ORDER
                           still leaks the ranking. --}}
@php $showMargin = $showMargin ?? true; @endphp
<style>
/* Class names are historical (neo-*); these surfaces are now FLAT to match
   the single white card language in layouts/sections/design-v5.blade.php. */
.neo-ap-wrap { background:#FFFFFF;border:1px solid var(--to-border);border-radius:14px;box-shadow:0 1px 2px rgba(15,23,42,0.04);overflow:hidden;height:100%; }
.neo-ap-hdr { padding:16px 20px 12px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--to-border); }
.neo-ap-grid { display:grid;grid-template-columns:repeat(auto-fill, minmax(92px,1fr));gap:10px;padding:4px 18px 18px; }
/* Tile is full-bleed: the portrait fills its whole width so the face is
   shown properly rather than cropped into a small circle. The green/red
   today-activity ring lives on the tile edge. */
.neo-ap-tile { border-radius:10px;background:var(--to-page);border:2px solid var(--to-border);box-shadow:none;padding:0;overflow:hidden;text-align:center; }
.neo-ap-photo { width:100%;aspect-ratio:3/4;position:relative;background:linear-gradient(135deg,#4F46E5 0%,#8B5CF6 100%);display:flex;align-items:center;justify-content:center;overflow:hidden; }
.neo-ap-dot { position:absolute;top:5px;right:5px;width:10px;height:10px;border-radius:50%;border:2px solid #FFFFFF; }
.neo-ap-name { font-size:0.75rem;font-weight:700;color:#1E293B;padding:6px 4px 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.neo-ap-metric { font-size:0.72rem;font-weight:800;padding:0 4px 7px;white-space:nowrap;display:flex;align-items:center;justify-content:center;gap:5px; }
/* Booking count reads as a number, not a footnote. The ticket glyph carries
   the meaning so the word "bookings" isn't needed on a 92px tile. */
.neo-ap-count { display:inline-flex;align-items:center;gap:3px;font-size:0.78rem;font-weight:800;color:var(--to-indigo);background:rgba(79,70,229,.10);border:1px solid rgba(79,70,229,.22);border-radius:20px;padding:1px 7px; }
.neo-ap-count i { font-size:0.78rem;color:var(--to-indigo);opacity:.7; }
/* Count-only variant (margin hidden) gets the full weight to itself. */
.neo-ap-count.is-solo { font-size:0.9rem;padding:2px 10px; }

/* Top seller. The green/red ring already encodes today-activity, so the
   crown gets its own gold halo rather than fighting for the border.
   NOTE: .neo-ap-tile sets overflow:hidden, so the crown must sit fully
   INSIDE the photo — a negative offset gets clipped to a sliver. */
.neo-ap-tile.is-top { box-shadow:0 0 0 3px rgba(245,158,11,.45); }
.neo-ap-crown {
  position:absolute;top:5px;left:5px;z-index:2;
  width:23px;height:23px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:0.92rem;line-height:1;
  background:#FFFFFF;
  box-shadow:0 1px 5px rgba(15,23,42,.35);
  animation:neoCrownPop .45s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes neoCrownPop { from{transform:translateY(-6px) rotate(-14deg);opacity:0} to{transform:none;opacity:1} }
@media (prefers-reduced-motion: reduce) { .neo-ap-crown { animation:none; } }
</style>
<div class="neo-ap-wrap">
  <div class="neo-ap-hdr">
    <h6 class="fw-bold mb-0" style="font-size:0.912rem;color:#0F172A;">Agents Performance</h6>
    @if ($showMargin)
      <span style="font-size:0.696rem;font-weight:800;color:#7C3AED;background:rgba(124,58,237,.10);border-radius:20px;padding:2px 10px;">{{ $performanceLabel }}</span>
    @else
      <span style="font-size:0.696rem;font-weight:800;color:#475569;background:var(--to-subtle);border-radius:20px;padding:2px 10px;">{{ now()->format('F Y') }}</span>
    @endif
  </div>
  @php
    // It's a booking-count competition — the crown goes to whoever has the most
    // bookings this month, matching the order the controller sorted by. Margin
    // never enters into it. Ties are all crowned, since picking one arbitrarily
    // would be a lie. Nobody is crowned at zero: a crown for no sales is noise.
    $apTopValue = $agentsPerformance->max('count') ?? 0;
  @endphp
  <div class="neo-ap-grid">
    @forelse ($agentsPerformance as $ap)
      @php
        $apIsTop = $apTopValue > 0 && $ap->count === $apTopValue;
        $apParts     = explode(' ', $ap->name);
        $apFirstName = $apParts[0];
        $apInitials  = strtoupper(mb_substr($apParts[0], 0, 1) . (count($apParts) > 1 ? mb_substr(end($apParts), 0, 1) : ''));
        $apPhotoUrl  = $ap->profile_photo_path ? asset('storage/' . $ap->profile_photo_path) : null;
        $apActive    = $ap->made_booking_today;
        $apColor     = $apActive ? '#10B981' : '#F43F5E';
        $apBookings  = $ap->count . ' booking' . ($ap->count !== 1 ? 's' : '') . ' this month';
        $apTitle     = $ap->name . ' — ' . ($apActive ? 'made a booking today' : 'no bookings today') . ' · '
                     . ($showMargin ? '£' . number_format($ap->margin, 0) . ' margin, ' . $apBookings : $apBookings)
                     . ($apIsTop ? ' · top seller this month' : '');
      @endphp
      <div class="neo-ap-tile{{ $apIsTop ? ' is-top' : '' }}" style="border-color:{{ $apColor }};" title="{{ $apTitle }}">
        <div class="neo-ap-photo">
          @if ($apPhotoUrl)
            <img src="{{ $apPhotoUrl }}" alt="{{ $apFirstName }}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">
          @else
            <div style="font-size:1.32rem;font-weight:800;color:rgba(255,255,255,.9);letter-spacing:.03em;">{{ $apInitials }}</div>
          @endif
          @if ($apIsTop)
            <span class="neo-ap-crown" aria-label="Top seller" role="img">👑</span>
          @endif
          <span class="neo-ap-dot" style="background:{{ $apColor }};"></span>
        </div>
        <div class="neo-ap-name">{{ $apFirstName }}</div>
        @if ($showMargin)
          <div class="neo-ap-metric" style="color:{{ $ap->margin >= 0 ? '#16A34A' : '#DC2626' }};">
            £{{ number_format($ap->margin, 0) }}
            <span class="neo-ap-count"><i class="ph ph-ticket"></i>{{ $ap->count }}</span>
          </div>
        @else
          <div class="neo-ap-metric">
            <span class="neo-ap-count is-solo"><i class="ph ph-ticket"></i>{{ $ap->count }}</span>
          </div>
        @endif
      </div>
    @empty
      <div style="font-size:0.816rem;color:#94A3B8;">No agents yet.</div>
    @endforelse
  </div>
</div>
