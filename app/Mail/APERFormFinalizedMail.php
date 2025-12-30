<?php

namespace App\Mail;

use App\Models\APERForm;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERFormFinalizedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public APERForm $form
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Finalized - ' . $this->form->year,
        );
    }

    public function content(): Content
    {
        // Ensure relationships are loaded
        if (!$this->form->relationLoaded('officer')) {
            $this->form->load('officer');
        }
        if (!$this->form->relationLoaded('staffOfficer')) {
            $this->form->load('staffOfficer');
        }

        return new Content(
            view: 'emails.aper-form-finalized',
            with: [
                'form' => $this->form,
                'officer' => $this->form->officer,
                'staffOfficer' => $this->form->staffOfficer,
                'rejectionReason' => $this->form->staff_officer_rejection_reason,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
