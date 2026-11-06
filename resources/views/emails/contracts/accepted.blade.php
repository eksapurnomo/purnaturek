<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agreement Acknowledged</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 24px; background-color: #f9fafb; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 28px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 16px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #065f46; font-size: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .info-table th { text-align: left; padding: 8px; font-size: 13px; color: #6b7280; width: 35%; border-bottom: 1px solid #f3f4f6; }
        .info-table td { padding: 8px; font-size: 13px; color: #111827; border-bottom: 1px solid #f3f4f6; }
        .badge-success { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 4px; background: #d1fae5; color: #065f46; }
        .notes-box { background: #f3f4f6; padding: 12px; border-radius: 4px; font-size: 13px; color: #374151; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2>Agreement Acknowledged & Accepted</h2>
        </div>

        <p>A wholesale member has accepted an issued commercial agreement:</p>

        <table class="info-table">
            <tr>
                <th>Reference:</th>
                <td><strong>{{ $contract->reference_number }}</strong></td>
            </tr>
            <tr>
                <th>Agreement:</th>
                <td>{{ $contract->title }}</td>
            </tr>
            <tr>
                <th>Member:</th>
                <td>{{ $contract->customer?->first_name }} {{ $contract->customer?->last_name }} ({{ $contract->customer?->email }})</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td><span class="badge-success">ACCEPTED</span></td>
            </tr>
            <tr>
                <th>Accepted At:</th>
                <td>{{ $contract->responded_at ? $contract->responded_at->format('M d, Y H:i:s T') : now()->format('M d, Y H:i:s T') }}</td>
            </tr>
            <tr>
                <th>Document Hash (SHA-256):</th>
                <td><code style="font-size: 11px; word-break: break-all;">{{ $contract->file_hash ?? 'N/A' }}</code></td>
            </tr>
        </table>

        @if(!empty($notes))
            <div class="notes-box">
                <strong>Member Notes / Comments:</strong><br>
                {{ $notes }}
            </div>
        @endif

        <p style="margin-top: 24px;">
            <a href="{{ route('admin.b2b.member-contracts.view', $contract->id) }}" style="color: #059669; font-weight: 600; text-decoration: underline;">
                View Agreement in Admin Panel &rarr;
            </a>
        </p>
    </div>
</body>
</html>

