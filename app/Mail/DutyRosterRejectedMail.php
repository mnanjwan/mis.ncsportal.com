<?php

namespace App\Mail;

use App\Models\DutyRoster;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DutyRosterRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DutyRoster $roster,
        public string $rejectedByName,
        public string $rejectionReason,
        public string $commandName,
        public string $periodStart,
        public string $periodEnd
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Duty Roster Rejected - ' . $this->commandName,
        );
    }

    public function content(): Content
    {
        // Ensure roster has relationships loaded
        if (!$this->roster->relationLoaded('command')) {
            $this->roster->load('command');
        }
        if (!$this->roster->relationLoaded('preparedBy')) {
            $this->roster->load('preparedBy');
        }

        return new Content(
            view: 'emails.duty-roster-rejected',
            with: [
                'roster' => $this->roster,
                'rosterId' => $this->roster->id,
                'rejectedByName' => $this->rejectedByName,
                'rejectionReason' => $this->rejectionReason,
                'commandName' => $this->commandName,
                'periodStart' => $this->periodStart,
                'periodEnd' => $this->periodEnd,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
