<?php

namespace App\Mail;

use App\Models\APERForm;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERFormAcceptedToHRDMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public APERForm $form,
        public User $hrdUser
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Accepted - Ready for Grading - ' . $this->form->year,
        );
    }

    public function content(): Content
    {
        // Ensure relationships are loaded
        if (!$this->form->relationLoaded('officer')) {
            $this->form->load('officer');
        }

        return new Content(
            view: 'emails.aper-form-accepted-to-hrd',
            with: [
                'form' => $this->form,
                'officer' => $this->form->officer,
                'hrdUser' => $this->hrdUser,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

