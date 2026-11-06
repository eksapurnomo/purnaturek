<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Buyer Verification Request</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; padding: 20px;">
    <h2 style="color: #2c5282;">New Wholesale Buyer Verification Request</h2>
    <p>A registered member has submitted business verification documents for review on the PurNatureK Members Portal.</p>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 180px;">Applicant Name:</td>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $verification->customer?->first_name }} {{ $verification->customer?->last_name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Applicant Email:</td>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $verification->customer?->email }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Company Name:</td>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $verification->company_name ?: 'Not Provided' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Tax ID / Business Reg:</td>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $verification->tax_id ?: 'Not Provided' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Business Type:</td>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $verification->business_type_label }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Submitted Documents:</td>
            <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $verification->documents->count() }} file(s) uploaded</td>
        </tr>
    </table>

    <p style="margin-top: 25px;">
        <a href="{{ route('admin.b2b.buyer_verifications.view', $verification->id) }}" style="background-color: #2b6cb0; color: #fff; padding: 10px 18px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Review Application in Admin
        </a>
    </p>

    <p style="font-size: 12px; color: #718096; margin-top: 30px;">PurNatureK Wholesale Members Portal — Automated Notification</p>
</body>
</html>

