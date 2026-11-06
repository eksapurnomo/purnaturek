<?php

namespace App\Mail;

use App\Models\BuyerVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyerVerificationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BuyerVerification $verification,
        public string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Wholesale Buyer Verification Update — PurNatureK',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.buyer_verification.rejected',
            with: [
                'verification' => $this->verification,
                'reason'       => $this->reason,
            ],
        );
    }
}

