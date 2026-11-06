<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Member Inquiry</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 28px;">
        <h2 style="color: #0f172a; margin-top: 0;">New Member Inquiry Received</h2>
        <p>A wholesale member has initiated a new conversation in the PurNatureK Members Portal.</p>

        <div style="background: #f1f5f9; border-radius: 6px; padding: 16px; margin: 20px 0;">
            <p style="margin: 0 0 8px;"><strong>Conversation ID:</strong> #{{ $conversation->id }}</p>
            <p style="margin: 0 0 8px;"><strong>Subject:</strong> {{ $conversation->subject }}</p>
            <p style="margin: 0 0 8px;"><strong>Category:</strong> {{ $conversation->category_label }}</p>
            <p style="margin: 0 0 8px;"><strong>Member:</strong> {{ $conversation->customer?->first_name }} {{ $conversation->customer?->last_name }} ({{ $conversation->customer?->email }})</p>
            <p style="margin: 0;"><strong>Submitted:</strong> {{ $conversation->created_at->format('M d, Y H:i T') }}</p>
        </div>

        <h3 style="color: #334155; font-size: 15px; margin-bottom: 8px;">Message:</h3>
        <div style="background: #ffffff; border-left: 3px solid #10b981; padding: 12px 16px; margin-bottom: 24px; font-size: 14px; white-space: pre-wrap;">{{ $messageModel->message }}</div>

        <p style="margin-top: 24px;">
            <a href="{{ route('admin.b2b.conversations.view', $conversation->id) }}" style="display: inline-block; background: #047857; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px;">View in Admin Inbox</a>
        </p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
        <p style="font-size: 12px; color: #64748b; margin: 0;">PurNatureK Wholesale Members Portal — Internal Messaging Notification</p>
    </div>
</body>
</html>

