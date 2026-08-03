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
        <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(51,46,158,0.10);">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#332E9E,#4A45B5);padding:16px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.65);">Travel Orbit MIS</div>
                    <div style="font-size:18px;font-weight:800;color:#ffffff;">&#8634;&nbsp; Flight Refund Request</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Amount highlight strip --}}
          <tr>
            <td style="background:#FEF2F2;border-bottom:1px solid #FECACA;padding:12px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#B91C1C;">Total Refund Requested</div>
                    <div style="font-size:24px;font-weight:800;color:#DC2626;line-height:1.3;">&pound;{{ number_format($refund->refund_amount, 2) }}</div>
                  </td>
                  <td align="right" style="vertical-align:middle;">
                    <span style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:#ffffff;background:#DC2626;padding:4px 12px;border-radius:16px;">Booking #{{ $booking->booking_number ?? $booking->id }}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Summary details — Internal Reference / Customer / Request Sent By /
               Refund Reason. "Customer" uses the booker/caller name, not the
               linked Customer record — every booking in this system currently
               points at the same shared "Default Customer" placeholder, so
               that field is never the real name (see Booking::booker_name /
               EticketController::resolveName for the same fix elsewhere). --}}
          <tr>
            <td style="padding:16px 24px 2px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;width:160px;vertical-align:top;">Customer</td>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:14px;color:#1E293B;">{{ $booking->booker_name ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">Request Sent By</td>
                  <td style="padding:6px 0;border-bottom:1px solid #F1F5F9;font-size:14px;color:#1E293B;">{{ $refund->requestedBy->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">Refund Reason</td>
                  <td style="padding:6px 0;font-size:14px;color:#1E293B;white-space:pre-wrap;line-height:1.4;">{{ $refund->reason }}</td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Passenger details — S.No / Passenger Name / GDS Locator / Airline
               Locator / Airline Waiver Code / Ticket Number. No Folder Number —
               that one genuinely isn't collected on the refund form. --}}
          <tr>
            <td style="padding:6px 24px 20px 24px;">
              <div style="font-size:12px;font-weight:700;color:#332E9E;text-transform:uppercase;letter-spacing:0.06em;margin:10px 0 6px 0;">Pax Details</div>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;">
                <tr style="background:#332E9E;">
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">S.No.</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">Passenger</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">GDS Locator</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">Airline Locator</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">Waiver Code</th>
                  <th style="padding:7px 8px;text-align:left;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.02em;">Ticket Number</th>
                </tr>
                @foreach($refund->passengers as $i => $rp)
                  <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#F8FAFF' }};">
                    <td style="padding:8px;font-size:12px;color:#64748B;border-top:1px solid #F1F5F9;">{{ $i + 1 }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-weight:600;border-top:1px solid #F1F5F9;">{{ $rp->passenger?->display_name ?? 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->gds_locator ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->airline_locator ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->airline_waiver_code ?: 'N/A' }}</td>
                    <td style="padding:8px;font-size:12px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->e_ticket_number ?: 'N/A' }}</td>
                  </tr>
                @endforeach
              </table>
            </td>
          </tr>
        </table>

        <div style="font-size:10px;color:#94A3B8;text-align:center;margin-top:10px;">Travel Orbit &middot; mis.travelorbit.co.uk</div>
      </td>
    </tr>
  </table>
</body>
</html>
