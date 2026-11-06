<?php

namespace App\Mail;

use App\Models\MemberContract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberContractSent extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MemberContract $contract
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PurNatureK Contracts] Action Required: Agreement Document Issued — ' . $this->contract->reference_number . ' (' . $this->contract->title . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contracts.sent',
        );
    }
}

