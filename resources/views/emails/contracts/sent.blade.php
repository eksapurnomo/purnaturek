<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Commercial Agreement Issued</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 28px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 16px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #065f46; font-size: 20px; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 4px; background: #fef3c7; color: #92400e; }
        .info-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .info-table th { text-align: left; padding: 8px; font-size: 13px; color: #6b7280; width: 35%; border-bottom: 1px solid #f3f4f6; }
        .info-table td { padding: 8px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6; }
        .notice { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; font-size: 13px; color: #1e40af; margin: 16px 0; }
        .disclaimer { background: #f9fafb; border: 1px solid #e5e7eb; padding: 12px; font-size: 12px; color: #6b7280; margin-top: 20px; border-radius: 4px; }
        .button { display: inline-block; background-color: #059669; color: #ffffff !important; text-decoration: none; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 6px; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2>PurNatureK Commercial Agreement</h2>
        </div>

        <p>Dear {{ $contract->customer?->first_name ?? 'Wholesale Member' }},</p>

        <p>PurNatureK administration has issued a formal commercial agreement for your review and acknowledgment:</p>

        <table class="info-table">
            <tr>
                <th>Reference Number:</th>
                <td><strong>{{ $contract->reference_number }}</strong></td>
            </tr>
            <tr>
                <th>Agreement Title:</th>
                <td>{{ $contract->title }}</td>
            </tr>
            @if($contract->template)
            <tr>
                <th>Template Reference:</th>
                <td>{{ $contract->template->title }} (v{{ $contract->template_version ?? '1.0' }})</td>
            </tr>
            @endif
            <tr>
                <th>Issued Date:</th>
                <td>{{ $contract->issued_date ? $contract->issued_date->format('M d, Y') : '—' }}</td>
            </tr>
            <tr>
                <th>Effective Date:</th>
                <td>{{ $contract->effective_date ? $contract->effective_date->format('M d, Y') : '—' }}</td>
            </tr>
            <tr>
                <th>Response Deadline:</th>
                <td>
                    @if($contract->expires_at)
                        <strong style="color: #b45309;">{{ $contract->expires_at->format('M d, Y H:i T') }}</strong>
                    @else
                        No hard expiration
                    @endif
                </td>
            </tr>
        </table>

        <div class="notice">
            <strong>Action Required:</strong> Please log in to your PurNatureK Members Portal account to download and inspect the attached document, and record your formal acknowledgment.
        </div>

        <p style="text-align: center;">
            <a href="{{ route('shop.customers.account.contracts.view', $contract->id) }}" class="button">Review & Acknowledge Agreement</a>
        </p>

        <div class="disclaimer">
            <em>Notice:</em> Agreement acceptance through the portal constitutes an electronic acknowledgment of agreement terms on record between PurNatureK and your commercial purchasing account.
        </div>
    </div>
</body>
</html>

