<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Wholesale Buyer Verification Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; padding: 20px;">
    <h2 style="color: #276749;">Your Wholesale Buyer Account Has Been Approved!</h2>
    <p>Dear {{ $verification->customer?->first_name }},</p>
    
    <p>We are pleased to inform you that your business credentials for <strong>{{ $verification->company_name ?: 'your account' }}</strong> have been reviewed and approved by the PurNatureK commercial team.</p>

    <div style="background-color: #f0fff4; border-left: 4px solid #38a169; padding: 15px; margin: 20px 0;">
        <h4 style="margin: 0 0 8px 0; color: #22543d;">Unlocked Commercial Capabilities:</h4>
        <ul style="margin: 0; padding-left: 20px; color: #2d3748;">
            <li>Submit formal Requests for Quote (RFQ) and negotiate pricing.</li>
            <li>Initiate wholesale sample requests and trials.</li>
            <li>Proceed with approved quotation checkouts and purchase orders.</li>
            <li>Access dedicated commercial terms and Master Supply Agreements.</li>
        </ul>
    </div>

    <p style="margin-top: 25px;">
        <a href="{{ route('shop.customers.account.index') }}" style="background-color: #276749; color: #fff; padding: 10px 18px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Access Wholesale Member Portal
        </a>
    </p>

    <p>If you have any questions or require custom product specifications, please contact your account representative or reply directly to this email.</p>

    <p style="font-size: 12px; color: #718096; margin-top: 30px;">PurNatureK Wholesale Members Portal</p>
</body>
</html>

