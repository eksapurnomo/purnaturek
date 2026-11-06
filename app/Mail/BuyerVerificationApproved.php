<?php

namespace App\Mail;

use App\Models\BuyerVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuyerVerificationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BuyerVerification $verification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! Your Wholesale Buyer Verification Has Been Approved — PurNatureK',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.buyer_verification.approved',
        );
    }
}

