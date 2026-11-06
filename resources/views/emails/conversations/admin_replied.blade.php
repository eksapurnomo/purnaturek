<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PurNatureK Admin Response</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 28px;">
        <h2 style="color: #0f172a; margin-top: 0;">PurNatureK Administration Replied</h2>
        <p>A new message has been posted to your conversation <strong>#{{ $conversation->id }} — {{ $conversation->subject }}</strong>.</p>

        <h3 style="color: #334155; font-size: 15px; margin-bottom: 8px;">Admin Message:</h3>
        <div style="background: #f8fafc; border-left: 3px solid #047857; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; white-space: pre-wrap;">{{ $messageModel->message }}</div>

        <p style="margin-top: 24px;">
            <a href="{{ route('shop.customers.account.conversations.view', $conversation->id) }}" style="display: inline-block; background: #047857; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px;">View Conversation &amp; Reply</a>
        </p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
        <p style="font-size: 12px; color: #64748b; margin: 0;">PurNatureK Wholesale Members Portal</p>
    </div>
</body>
</html>

