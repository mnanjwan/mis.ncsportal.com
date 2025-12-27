<?php

namespace App\Mail;

use App\Models\APERForm;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERReportingOfficerAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public APERForm $form,
        public User $reportingOfficer
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Assigned for Review - ' . $this->form->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aper-reporting-officer-assigned',
            with: [
                'form' => $this->form,
                'reportingOfficer' => $this->reportingOfficer,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

