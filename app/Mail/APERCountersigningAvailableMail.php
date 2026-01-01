<?php

namespace App\Mail;

use App\Models\APERForm;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERCountersigningAvailableMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public APERForm $form,
        public User $recipient
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Awaiting Countersignature - ' . $this->form->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aper-countersigning-available',
            with: [
                'form' => $this->form,
                'recipient' => $this->recipient,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
