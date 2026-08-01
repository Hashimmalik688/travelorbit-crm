<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#EEF1F8;font-family:'Segoe UI',Arial,Helvetica,sans-serif;color:#1E293B;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F8;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(51,46,158,0.12);">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#332E9E,#4A45B5);padding:28px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.65);margin-bottom:4px;">Travel Orbit MIS</div>
                    <div style="font-size:22px;font-weight:800;color:#ffffff;">&#8634;&nbsp; Flight Refund Request</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Amount highlight strip --}}
          <tr>
            <td style="background:#FEF2F2;border-bottom:1px solid #FECACA;padding:18px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#B91C1C;margin-bottom:2px;">Total Refund Requested</div>
                    <div style="font-size:30px;font-weight:800;color:#DC2626;">&pound;{{ number_format($refund->refund_amount, 2) }}</div>
                  </td>
                  <td align="right" style="vertical-align:middle;">
                    <span style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:#ffffff;background:#DC2626;padding:6px 14px;border-radius:20px;">Booking #{{ $booking->booking_number ?? $booking->id }}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Summary details — field set matches the internal reference / total
               refund requested / request sent by / refund reason table format
               used by supplier refund-request emails. --}}
          <tr>
            <td style="padding:28px 32px 4px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:10px 0;border-bottom:1px solid #F1F5F9;font-size:13px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;width:180px;vertical-align:top;">Internal Reference</td>
                  <td style="padding:10px 0;border-bottom:1px solid #F1F5F9;font-size:15px;color:#1E293B;font-weight:600;">#{{ $booking->booking_number ?? $booking->id }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 0;border-bottom:1px solid #F1F5F9;font-size:13px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">Customer</td>
                  <td style="padding:10px 0;border-bottom:1px solid #F1F5F9;font-size:15px;color:#1E293B;">{{ $booking->customer->full_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 0;border-bottom:1px solid #F1F5F9;font-size:13px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">Request Sent By</td>
                  <td style="padding:10px 0;border-bottom:1px solid #F1F5F9;font-size:15px;color:#1E293B;">{{ $refund->requestedBy->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 0;font-size:13px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.04em;vertical-align:top;">Refund Reason</td>
                  <td style="padding:10px 0;font-size:15px;color:#1E293B;white-space:pre-wrap;line-height:1.5;">{{ $refund->reason }}</td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Passenger details — S.No / Passenger Name / GDS PNR / Ticket Number /
               Airline Waiver Code, matching the supplier PAX DETAILS table.
               No Folder Number — that one genuinely isn't collected. --}}
          <tr>
            <td style="padding:12px 32px 32px 32px;">
              <div style="font-size:13px;font-weight:700;color:#332E9E;text-transform:uppercase;letter-spacing:0.06em;margin:16px 0 10px 0;">Pax Details</div>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-radius:10px;overflow:hidden;border:1px solid #E2E8F0;">
                <tr style="background:#332E9E;">
                  <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.03em;">S.No.</th>
                  <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.03em;">Passenger Name</th>
                  <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.03em;">GDS PNR</th>
                  <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.03em;">Ticket Number</th>
                  <th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.03em;">Airline Waiver Code</th>
                </tr>
                @foreach($refund->passengers as $i => $rp)
                  <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#F8FAFF' }};">
                    <td style="padding:11px 12px;font-size:13px;color:#64748B;border-top:1px solid #F1F5F9;">{{ $i + 1 }}</td>
                    <td style="padding:11px 12px;font-size:13px;color:#1E293B;font-weight:600;border-top:1px solid #F1F5F9;">{{ $rp->passenger?->display_name ?? 'N/A' }}</td>
                    <td style="padding:11px 12px;font-size:13px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->gds_locator ?: 'N/A' }}</td>
                    <td style="padding:11px 12px;font-size:13px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->e_ticket_number ?: 'N/A' }}</td>
                    <td style="padding:11px 12px;font-size:13px;color:#1E293B;font-family:'Courier New',monospace;border-top:1px solid #F1F5F9;">{{ $rp->airline_waiver_code ?: 'N/A' }}</td>
                  </tr>
                @endforeach
              </table>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background:#F8FAFF;padding:18px 32px;border-top:1px solid #E2E8F0;">
              <div style="font-size:12px;color:#64748B;line-height:1.5;">
                This document is computer generated and does not require the accounts team's signature or the company's stamp in order to be considered valid.
              </div>
            </td>
          </tr>
        </table>

        <div style="font-size:11px;color:#94A3B8;text-align:center;margin-top:16px;">Travel Orbit &middot; mis.travelorbit.co.uk</div>
      </td>
    </tr>
  </table>
</body>
</html>
