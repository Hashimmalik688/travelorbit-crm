<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;padding:0;background:#EEF1F8;font-family:'Segoe UI',Arial,Helvetica,sans-serif;color:#1E293B;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F8;padding:20px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="680" cellpadding="0" cellspacing="0"
          style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(51,46,158,0.10);">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#332E9E,#4A45B5);padding:16px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div
                      style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.65);">
                      Travel Orbit MIS</div>
                    <div style="font-size:18px;font-weight:800;color:#ffffff;">&#9992;&nbsp; Ticket Order Form</div>
                  </td>
                  <td align="right" style="vertical-align:middle;">
                    <span
                      style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:#332E9E;background:#ffffff;padding:4px 12px;border-radius:16px;">Booking
                      #{{ $booking->booking_number ?? $booking->id }}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Agent / Date strip — no Booking No. here, the badge in the
               header already carries it. --}}
          <tr>
            <td style="background:#F8FAFF;border-bottom:1px solid #E2E8F0;padding:12px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#94A3B8;">Agent</div>
                    <div style="font-size:14px;font-weight:700;color:#1E293B;">{{ $ticketOrder->requestedBy->name ?? 'N/A' }}</div>
                  </td>
                  <td align="right">
                    <div style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#94A3B8;">Date</div>
                    <div style="font-size:14px;font-weight:700;color:#1E293B;">{{ $ticketOrder->created_at?->format('d-m-Y') }}</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- To --}}
          <tr>
            <td style="padding:16px 24px 2px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:6px 0;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;width:160px;vertical-align:top;">
                    To</td>
                  <td style="padding:6px 0;font-size:14px;color:#1E293B;font-weight:700;">
                    {{ $ticketOrder->issued_to }}</td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Pax Names --}}
          <tr>
            <td style="padding:10px 24px 20px 24px;">
              <div style="font-size:12px;font-weight:700;color:#332E9E;text-transform:uppercase;letter-spacing:0.06em;margin:10px 0 6px 0;">
                Pax Name(s)</div>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;">
                <tr style="background:#332E9E;">
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Name</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    DOB</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Passport</th>
                </tr>
                @foreach ($ticketOrder->passengers as $i => $pax)
                  <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#F8FAFF' }};">
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-weight:600;border-top:1px solid #F1F5F9;">
                      {{ $i + 1 }} {{ $pax->name }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $pax->date_of_birth?->format('Y-m-d') ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">
                      {{ $pax->passport_number ?: 'N/A' }}</td>
                  </tr>
                @endforeach
              </table>
            </td>
          </tr>

          {{-- Flight Details — one mini-table per segment (Locator/Booked In/
               Issue From/Airline), followed by its raw GDS itinerary lines
               when we have them, matching the old hand-filled form. --}}
          <tr>
            <td style="padding:0 24px 20px 24px;">
              <div style="font-size:12px;font-weight:700;color:#332E9E;text-transform:uppercase;letter-spacing:0.06em;margin:0 0 6px 0;">
                Flight Details</div>
              @foreach ($ticketOrder->segments as $seg)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                  style="border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;margin-bottom:10px;">
                  <tr style="background:#332E9E;">
                    <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                      Locator</th>
                    <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                      Booked In</th>
                    <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                      Issue From</th>
                    <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                      Airline</th>
                  </tr>
                  <tr style="background:#ffffff;">
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">
                      {{ $seg->locator ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->booked_in ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->issue_from ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->airline ?: 'N/A' }}</td>
                  </tr>
                  @if ($seg->pnr)
                    <tr style="background:#F8FAFF;">
                      <td colspan="4" style="padding:8px;font-size:12px;color:#334155;font-family:'Courier New',monospace;white-space:pre-wrap;line-height:1.5;border-top:1px solid #F1F5F9;">
                        {{ $seg->pnr }}</td>
                    </tr>
                  @endif
                </table>
              @endforeach
            </td>
          </tr>

          {{-- Cost — compact chips, one per pax type that's actually non-zero,
               plus ATOL/SAFI when it applies and Total. Nothing stretches to
               fill three even columns when only one or two are ever used. --}}
          @php
            $costChips = collect([
              ['label' => 'Adult', 'value' => $ticketOrder->cost_adult, 'color' => '#332E9E'],
              ['label' => 'Child', 'value' => $ticketOrder->cost_child, 'color' => '#0891B2'],
              ['label' => 'Infant', 'value' => $ticketOrder->cost_infant, 'color' => '#C026D3'],
            ])->filter(fn ($c) => (float) $c['value'] > 0)->values();
            if ($ticketOrder->atol_safi_label) {
              $costChips->push(['label' => $ticketOrder->atol_safi_label, 'value' => $ticketOrder->safi_charges, 'color' => '#D97706']);
            }
          @endphp
          <tr>
            <td style="padding:0 24px 22px 24px;">
              <div style="font-size:12px;font-weight:700;color:#332E9E;text-transform:uppercase;letter-spacing:0.06em;margin:0 0 6px 0;">
                Cost</div>
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  @foreach ($costChips as $chip)
                    <td style="background:{{ $chip['color'] }}12;border:1px solid {{ $chip['color'] }}33;border-radius:10px;padding:8px 16px;text-align:center;">
                      <div style="font-size:10px;font-weight:700;color:{{ $chip['color'] }};text-transform:uppercase;letter-spacing:0.04em;">{{ $chip['label'] }}</div>
                      <div style="font-size:14px;font-weight:800;color:#1E293B;white-space:nowrap;">GBP {{ number_format($chip['value'], 2) }}</div>
                    </td>
                    <td width="8"></td>
                  @endforeach
                  <td style="background:#16A34A12;border:1px solid #16A34A33;border-radius:10px;padding:8px 16px;text-align:center;">
                    <div style="font-size:10px;font-weight:700;color:#16A34A;text-transform:uppercase;letter-spacing:0.04em;">Total</div>
                    <div style="font-size:14px;font-weight:800;color:#16A34A;white-space:nowrap;">GBP {{ number_format($ticketOrder->total_cost, 2) }}</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <div style="font-size:10px;color:#94A3B8;text-align:center;margin-top:10px;">Travel Orbit &middot;
          mis.travelorbit.co.uk</div>
      </td>
    </tr>
  </table>
</body>

</html>
