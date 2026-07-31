<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
</head>
<body style="margin:0;padding:0;background:#F1F5F9;font-family:Arial,Helvetica,sans-serif;color:#1E293B;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F5F9;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;">
          <tr>
            <td style="background:#332E9E;padding:18px 28px;">
              <span style="font-size:18px;font-weight:bold;color:#ffffff;">Travel Orbit — Flight Refund Request</span>
            </td>
          </tr>
          <tr>
            <td style="padding:24px 28px 8px 28px;">
              <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
                <tr>
                  <td style="border:1px solid #E2E8F0;background:#F8FAFF;font-weight:bold;width:220px;">Internal Reference</td>
                  <td style="border:1px solid #E2E8F0;">#{{ $booking->booking_number ?? $booking->id }}</td>
                </tr>
                <tr>
                  <td style="border:1px solid #E2E8F0;background:#F8FAFF;font-weight:bold;">Total Refund Requested</td>
                  <td style="border:1px solid #E2E8F0;">&pound;{{ number_format($refund->refund_amount, 2) }}</td>
                </tr>
                <tr>
                  <td style="border:1px solid #E2E8F0;background:#F8FAFF;font-weight:bold;">Request Sent By</td>
                  <td style="border:1px solid #E2E8F0;">{{ $refund->requestedBy->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td style="border:1px solid #E2E8F0;background:#F8FAFF;font-weight:bold;vertical-align:top;">Refund Reason / Penalties</td>
                  <td style="border:1px solid #E2E8F0;white-space:pre-wrap;">{{ $refund->reason }}</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 28px 24px 28px;">
              <div style="font-size:14px;font-weight:bold;margin-bottom:8px;">Passenger Details</div>
              <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:13px;">
                <tr style="background:#F8FAFF;">
                  <th style="border:1px solid #E2E8F0;text-align:left;">S.No.</th>
                  <th style="border:1px solid #E2E8F0;text-align:left;">Passenger Name</th>
                  <th style="border:1px solid #E2E8F0;text-align:left;">GDS Locator</th>
                  <th style="border:1px solid #E2E8F0;text-align:left;">Airline Locator</th>
                  <th style="border:1px solid #E2E8F0;text-align:left;">Ticket Number</th>
                </tr>
                @foreach($refund->passengers as $i => $rp)
                  <tr>
                    <td style="border:1px solid #E2E8F0;">{{ $i + 1 }}</td>
                    <td style="border:1px solid #E2E8F0;">{{ $rp->passenger?->display_name ?? 'N/A' }}</td>
                    <td style="border:1px solid #E2E8F0;">{{ $rp->gds_locator ?: '-' }}</td>
                    <td style="border:1px solid #E2E8F0;">{{ $rp->airline_locator ?: '-' }}</td>
                    <td style="border:1px solid #E2E8F0;">{{ $rp->e_ticket_number ?: '-' }}</td>
                  </tr>
                @endforeach
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 24px 28px;font-size:11px;color:#64748B;">
              This is an automated refund request from Travel Orbit MIS. It does not require a signature to be considered valid.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
