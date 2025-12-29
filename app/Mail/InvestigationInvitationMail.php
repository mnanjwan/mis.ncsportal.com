<?php

namespace App\Mail;

use App\Models\Investigation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvestigationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Investigation $investigation
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Investigation Invitation - ' . ($this->investigation->officer->service_number ?? 'N/A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.investigation-invitation',
            with: [
                'investigation' => $this->investigation,
                'officer' => $this->investigation->officer,
                'investigationOfficer' => $this->investigation->investigationOfficer,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

