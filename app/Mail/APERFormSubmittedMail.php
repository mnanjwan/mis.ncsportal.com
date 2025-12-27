<?php

namespace App\Mail;

use App\Models\APERForm;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERFormSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public APERForm $form
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Submitted Successfully - ' . $this->form->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aper-form-submitted',
            with: [
                'officer' => $this->form->officer,
                'form' => $this->form,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

