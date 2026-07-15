@props(['date'])
@if($date)
  @php
    $isPast = $date->isPast();
    $diff = $date->copy()->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE);
    $label = $diff . ($isPast ? ' ago' : ' to go');
  @endphp
  <div style="font-size:0.816rem;color:#334155;">{{ $date->format('d/m/Y') }}</div>
  <span class="badge {{ $isPast ? 'bg-label-danger' : 'bg-label-success' }}" style="font-size:0.72rem;">{{ $label }}</span>
@else
  <span class="text-muted" style="font-size:0.84rem;">—</span>
@endif
