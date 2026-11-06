<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Wholesale Buyer Verification Update</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; padding: 20px;">
    <h2 style="color: #9b2c2c;">Action Required: Wholesale Buyer Verification Update</h2>
    <p>Dear {{ $verification->customer?->first_name }},</p>
    
    <p>Thank you for submitting your business documentation to the PurNatureK Members Portal. Our compliance team reviewed your application for <strong>{{ $verification->company_name ?: 'your account' }}</strong> and was unable to verify your account based on the submitted materials.</p>

    <div style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0;">
        <h4 style="margin: 0 0 8px 0; color: #742a2a;">Reason for Verification Decision:</h4>
        <p style="margin: 0; color: #4a5568;">{{ $reason }}</p>
    </div>

    <p><strong>Next Steps:</strong> You may correct the highlighted information or upload updated documentation directly from your account portal.</p>

    <p style="margin-top: 25px;">
        <a href="{{ route('shop.customers.account.buyer_verification.index') }}" style="background-color: #c53030; color: #fff; padding: 10px 18px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Update &amp; Resubmit Application
        </a>
    </p>

    <p>Your member login remains fully active, and you may continue to browse the private catalog and review technical specifications while your application is updated.</p>

    <p style="font-size: 12px; color: #718096; margin-top: 30px;">PurNatureK Wholesale Members Portal</p>
</body>
</html>

