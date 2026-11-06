<?php

namespace App\Mail;

use App\Models\MemberContract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberContractAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MemberContract $contract,
        public ?string $notes = null
    ) {}

    public function envelope(): Envelope
    {
        $customerName = trim(($this->contract->customer?->first_name ?? '') . ' ' . ($this->contract->customer?->last_name ?? ''));

        return new Envelope(
            subject: '[PurNatureK Contracts] Agreement Acknowledged & Accepted — ' . $this->contract->reference_number . ' (' . ($customerName ?: 'Member') . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contracts.accepted',
        );
    }
}

