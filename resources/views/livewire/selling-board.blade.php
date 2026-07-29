<div wire:poll.8s="$refresh" style="flex:1 1 auto;width:100%;display:flex;">
  <style>
    /* Class names are historical (neo-*); these surfaces are now FLAT to match
   the single white card language in layouts/sections/design-v5.blade.php.
   Geometry is unchanged — only fill, border and shadow. */
    .neo-sb-wrap {
      background: #FFFFFF;
      border: 1px solid var(--to-border);
      border-radius: 14px;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
      overflow: hidden;
      flex: 1 1 auto;
      width: 100%;
      display: flex;
      flex-direction: column;
    }

    .neo-sb-hdr {
      padding: 12px 18px 9px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--to-border);
    }

    .neo-sb-col {
      padding: 11px 16px 13px;
    }

    .neo-sb-label {
      font-size: 0.66rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 8px;
    }

    .neo-sb-row {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 6px 11px;
      border-radius: 10px;
      margin-bottom: 6px;
      background: var(--to-page);
      border: 1px solid var(--to-border);
      box-shadow: none;
    }

    .neo-sb-avatar {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.696rem;
      font-weight: 800;
      flex-shrink: 0;
    }

    .neo-sb-count {
      font-size: 0.84rem;
      font-weight: 800;
    }

    .neo-sb-empty {
      font-size: 0.816rem;
      color: #94A3B8;
    }

    /* Chill Party — "Wall of Shame". Photos are desaturated (nothing to show off
   yet) and ringed in a pulsing red so the panel reads as a mild alarm, not a
   roster. The ring calms back to grey the moment an agent picks up a sale
   and drops off this list entirely. */
    .neo-cp-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
      gap: 14px 8px;
    }

    .neo-cp-tile {
      text-align: center;
    }

    .neo-cp-photo {
      width: 52px;
      height: 52px;
      margin: 0 auto;
      border-radius: 50%;
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #94A3B8 0%, #64748B 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 0 2.5px #FFFFFF, 0 0 0 4px rgba(220, 38, 38, .55);
      animation: neoCpPulse 2.2s ease-in-out infinite;
    }

    .neo-cp-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: grayscale(100%) contrast(1.05);
    }

    .neo-cp-initials {
      font-size: 0.84rem;
      font-weight: 800;
      color: rgba(255, 255, 255, .9);
    }

    @keyframes neoCpPulse {

      0%,
      100% {
        box-shadow: 0 0 0 2.5px #FFFFFF, 0 0 0 4px rgba(220, 38, 38, .55);
      }

      50% {
        box-shadow: 0 0 0 2.5px #FFFFFF, 0 0 0 6px rgba(220, 38, 38, .22);
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .neo-cp-photo {
        animation: none;
      }
    }

    .neo-cp-name {
      font-size: 0.732rem;
      font-weight: 700;
      color: #334155;
      margin-top: 6px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .neo-cp-taunt {
      font-size: 0.636rem;
      font-weight: 600;
      color: #DC2626;
      opacity: .85;
      line-height: 1.2;
      margin-top: 1px;
    }
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
            $initials = strtoupper(
                substr($ag->name, 0, 1) .
                    (strpos($ag->name, ' ') !== false ? substr($ag->name, strpos($ag->name, ' ') + 1, 1) : ''),
            );
            $colors = ['#332E9E', '#D83F87', '#D97706', '#16A34A', '#0EA5E9', '#7C3AED', '#DC2626', '#F59E0B'];
            $c = $colors[$idx % count($colors)];
          @endphp
          <div wire:key="agent-{{ $ag->id }}" data-agent-id="{{ $ag->id }}" class="neo-sb-row">
            <div class="neo-sb-avatar" style="background:{{ $c }}18;color:{{ $c }};">
              {{ $initials }}</div>
            <div class="flex-grow-1 min-width-0" style="font-size:0.84rem;font-weight:600;color:#1E293B;">
              {{ $ag->name }}</div>
            <div class="neo-sb-count" style="color:{{ $c }};">{{ $ag->today_bookings }}</div>
          </div>
        @empty
          <div class="neo-sb-empty">No sales yet today.</div>
        @endforelse
      </div>
      <div class="col-md-6 neo-sb-col">
        <div class="neo-sb-label" style="color:#94A3B8;">🥳 Chill Party</div>
        @php
          // Stable per agent per day — a taunt shouldn't reshuffle on every
// 8s poll, but it's fair game to change tomorrow.
          $cpTaunts = [
              'Still warming up…',
              'Coffee break?',
              'Waiting on inspiration ☕',
              'Loading sales pitch…',
              'Napping on the job? 😴',
              "Phone's not going to ring itself",
              'Zero bookings, zero worries?',
              'The board is watching 👀',
          ];
        @endphp
        @if ($chillToday->isEmpty())
          <div class="neo-sb-empty">Everyone's selling today 🔥</div>
        @else
          <div class="neo-cp-grid">
            @foreach ($chillToday as $ag)
              @php
                $cpInitials = strtoupper(
                    substr($ag->name, 0, 1) .
                        (strpos($ag->name, ' ') !== false ? substr($ag->name, strpos($ag->name, ' ') + 1, 1) : ''),
                );
                $cpPhotoUrl = $ag->profile_photo_path ? asset('storage/' . $ag->profile_photo_path) : null;
                $cpTaunt = $cpTaunts[($ag->id + now()->dayOfYear) % count($cpTaunts)];
              @endphp
              <div wire:key="agent-{{ $ag->id }}" data-agent-id="{{ $ag->id }}" class="neo-cp-tile"
                title="{{ $ag->name }} — no bookings today">
                <div class="neo-cp-photo">
                  @if ($cpPhotoUrl)
                    <img src="{{ $cpPhotoUrl }}" alt="{{ $ag->name }}">
                  @else
                    <span class="neo-cp-initials">{{ $cpInitials }}</span>
                  @endif
                </div>
                <div class="neo-cp-name">{{ $ag->name }}</div>
                <div class="neo-cp-taunt">{{ $cpTaunt }}</div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
