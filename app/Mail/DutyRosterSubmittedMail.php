<?php

namespace App\Mail;

use App\Models\DutyRoster;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DutyRosterSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DutyRoster $roster,
        public User $user,
        public string $commandName,
        public string $periodStart,
        public string $periodEnd,
        public string $preparedByName,
        public int $assignmentsCount,
        public ?string $oicName = null,
        public ?string $secondInCommandName = null,
        public string $approvalRoute = 'area-controller/roster'
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Duty Roster Submitted - Requires Approval - ' . $this->commandName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.duty-roster-submitted',
            with: [
                'roster' => $this->roster,
                'rosterId' => $this->roster->id,
                'user' => $this->user,
                'commandName' => $this->commandName,
                'periodStart' => $this->periodStart,
                'periodEnd' => $this->periodEnd,
                'preparedByName' => $this->preparedByName,
                'assignmentsCount' => $this->assignmentsCount,
                'oicName' => $this->oicName,
                'secondInCommandName' => $this->secondInCommandName,
                'approvalRoute' => $this->approvalRoute,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

