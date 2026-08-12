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

          {{-- Booking No / Consultant / Date strip --}}
          <tr>
            <td style="background:#F8FAFF;border-bottom:1px solid #E2E8F0;padding:12px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#94A3B8;">Booking No.</div>
                    <div style="font-size:14px;font-weight:700;color:#1E293B;">{{ $booking->booking_number ?? $booking->id }}</div>
                  </td>
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

          {{-- To / Ref --}}
          <tr>
            <td style="padding:16px 24px 2px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;width:160px;vertical-align:top;">
                    To</td>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:14px;color:#1E293B;font-weight:700;">
                    {{ $ticketOrder->issued_to }}</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">
                    Ref #</td>
                  <td style="padding:6px 0;font-size:14px;color:#1E293B;">
                    {{ $ticketOrder->ref_number ?: '—' }}</td>
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

          {{-- Flight Details --}}
          <tr>
            <td style="padding:0 24px 20px 24px;">
              <div style="font-size:12px;font-weight:700;color:#332E9E;text-transform:uppercase;letter-spacing:0.06em;margin:0 0 6px 0;">
                Flight Details</div>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;">
                <tr style="background:#332E9E;">
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Locator</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Folder</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Type</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Booked In</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Issue From</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Airline</th>
                </tr>
                @foreach ($ticketOrder->segments as $i => $seg)
                  <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#F8FAFF' }};">
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">
                      {{ $seg->locator ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->folder ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->type ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->booked_in ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->issue_from ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">
                      {{ $seg->airline ?: 'N/A' }}</td>
                  </tr>
                @endforeach
              </table>
            </td>
          </tr>

          {{-- Sold / Cost / Margin --}}
          <tr>
            <td style="padding:0 24px 20px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                style="border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;">
                <tr style="background:#332E9E;">
                  <th colspan="3" style="padding:7px 8px;text-align:center;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;border-right:1px solid rgba(255,255,255,.2);">
                    Sold For</th>
                  <th colspan="3" style="padding:7px 8px;text-align:center;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;border-right:1px solid rgba(255,255,255,.2);">
                    Cost</th>
                  <th style="padding:7px 8px;text-align:center;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">
                    Margin</th>
                </tr>
                <tr style="background:#F8FAFF;">
                  <th style="padding:5px 8px;text-align:center;font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">Adult</th>
                  <th style="padding:5px 8px;text-align:center;font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">Child</th>
                  <th style="padding:5px 8px;text-align:center;font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;border-right:1px solid #E2E8F0;">Infant</th>
                  <th style="padding:5px 8px;text-align:center;font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">Adult</th>
                  <th style="padding:5px 8px;text-align:center;font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">Child</th>
                  <th style="padding:5px 8px;text-align:center;font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;border-right:1px solid #E2E8F0;">Infant</th>
                  <th></th>
                </tr>
                <tr style="background:#ffffff;">
                  <td style="padding:8px;text-align:center;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->sold_adult, 2) }}</td>
                  <td style="padding:8px;text-align:center;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->sold_child, 2) }}</td>
                  <td style="padding:8px;text-align:center;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;border-right:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->sold_infant, 2) }}</td>
                  <td style="padding:8px;text-align:center;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->cost_adult, 2) }}</td>
                  <td style="padding:8px;text-align:center;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->cost_child, 2) }}</td>
                  <td style="padding:8px;text-align:center;font-size:12px;color:#1E293B;border-top:1px solid #F1F5F9;border-right:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->cost_infant, 2) }}</td>
                  <td style="padding:8px;text-align:center;font-size:12px;font-weight:700;color:#16A34A;border-top:1px solid #F1F5F9;">GBP {{ number_format($ticketOrder->margin, 2) }}</td>
                </tr>
                <tr style="background:#F8FAFF;">
                  <td colspan="7" style="padding:6px 8px;text-align:center;font-size:12px;color:#475569;border-top:1px solid #F1F5F9;">
                    <strong>Safi Charges:</strong> {{ number_format($ticketOrder->safi_charges, 2) }}</td>
                </tr>
                <tr style="background:#ffffff;">
                  <td colspan="3" style="padding:10px 8px;text-align:center;font-size:13px;font-weight:800;color:#1E293B;border-top:1px solid #E2E8F0;">
                    Total: GBP {{ number_format($ticketOrder->total_sold, 2) }}</td>
                  <td colspan="3" style="padding:10px 8px;text-align:center;font-size:13px;font-weight:800;color:#1E293B;border-top:1px solid #E2E8F0;">
                    Total: GBP {{ number_format($ticketOrder->total_cost, 2) }}</td>
                  <td style="padding:10px 8px;text-align:center;font-size:13px;font-weight:800;color:#16A34A;border-top:1px solid #E2E8F0;">
                    {{ number_format($ticketOrder->margin, 2) }}</td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Payment / Clearance / Notes --}}
          <tr>
            <td style="padding:0 24px 22px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;width:160px;vertical-align:top;">
                    Payment</td>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:14px;color:#1E293B;">
                    GBP {{ number_format($ticketOrder->payment_amount, 2) }}</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">
                    Clearance Date</td>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:14px;color:#1E293B;">
                    {{ $ticketOrder->clearance_date?->format('d M y') ?: '—' }}</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">
                    Notes</td>
                  <td style="padding:6px 0;font-size:14px;color:#1E293B;white-space:pre-wrap;line-height:1.4;">
                    {{ $ticketOrder->notes ?: '—' }}</td>
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
