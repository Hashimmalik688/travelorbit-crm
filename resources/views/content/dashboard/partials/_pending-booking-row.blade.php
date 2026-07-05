@php
  $ti = ['flight'=>'ph-airplane','hotel'=>'ph-buildings','holiday'=>'ph-island','umrah'=>'ph-mosque','visa'=>'ph-identification-card','transfers'=>'ph-van','excursion'=>'ph-binoculars'][$bk->booking_type ?? ''] ?? 'ph-ticket';
  $rowMargin = (float) $bk->total_margin - (float) ($bk->payment->cc_charges ?? 0);
@endphp
<div class="ad-brow">
  <div style="width:34px;height:34px;border-radius:9px;background:rgba(51,46,158,.07);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
    <i class="ph {{ $ti }}" style="font-size:.95rem;color:#332E9E;"></i>
  </div>
  <div class="flex-grow-1 min-width-0">
    <div class="d-flex align-items-center gap-2">
      <span class="fw-semibold" style="font-size:.79rem;color:#1E293B;">{{ $bk->booking_number }}</span>
      <span style="font-size:.6rem;background:rgba(217,119,6,.10);color:#D97706;padding:1px 8px;border-radius:20px;font-weight:700;text-transform:capitalize;">{{ str_replace('_', ' ', $bk->booking_status) }}</span>
    </div>
    <div style="font-size:.7rem;color:#64748B;margin-top:1px;">{{ $bk->booker_first_name }} {{ $bk->booker_last_name }}@if($bk->booking_type) · {{ ucfirst($bk->booking_type) }}@endif</div>
  </div>
  <div class="text-end flex-shrink-0">
    <div style="font-size:.79rem;color:#D97706;font-weight:700;">£{{ number_format($rowMargin, 2) }}</div>
    <div style="font-size:.67rem;color:#94A3B8;">{{ $bk->created_at->format('d M Y') }}</div>
  </div>
  <a href="{{ route('bookings.show', $bk->id) }}" style="color:#332E9E;opacity:.35;font-size:.84rem;flex-shrink:0;margin-left:4px;"><i class="ph ph-arrow-right"></i></a>
</div>
