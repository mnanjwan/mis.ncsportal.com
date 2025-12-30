<?php

namespace App\Mail;

use App\Models\DutyRoster;
use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DutyRosterAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DutyRoster $roster,
        public Officer $officer,
        public string $role,
        public string $commandName,
        public string $periodStart,
        public string $periodEnd,
        public ?string $oicName = null,
        public ?string $secondInCommandName = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Duty Roster Assignment - ' . $this->commandName,
        );
    }

    public function content(): Content
    {
        // Ensure roster has assignments loaded
        if (!$this->roster->relationLoaded('assignments')) {
            $this->roster->load('assignments');
        }
        
        // Get assignments for this officer
        $assignments = $this->roster->assignments->where('officer_id', $this->officer->id);

        return new Content(
            view: 'emails.duty-roster-assignment',
            with: [
                'roster' => $this->roster,
                'rosterId' => $this->roster->id,
                'officer' => $this->officer,
                'role' => $this->role,
                'commandName' => $this->commandName,
                'periodStart' => $this->periodStart,
                'periodEnd' => $this->periodEnd,
                'oicName' => $this->oicName,
                'secondInCommandName' => $this->secondInCommandName,
                'assignments' => $assignments,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
