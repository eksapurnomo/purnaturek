<?php

namespace App\Mail;

use App\Models\MemberConversation;
use App\Models\MemberConversationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberConversationReplied extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MemberConversation $conversation,
        public MemberConversationMessage $messageModel
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PurNatureK Portal] Member Reply: #' . $this->conversation->id . ' — ' . $this->conversation->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conversations.member_replied',
        );
    }
}

