<?php

namespace App\Mail;

use App\Models\Officer;
use App\Models\APERTimeline;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERTimelineClosingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Officer $officer,
        public APERTimeline $timeline,
        public int $daysRemaining,
        public bool $hasDraft = false,
        public ?int $formId = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Submission Deadline Approaching - ' . $this->timeline->year,
        );
    }

    public function content(): Content
    {
        $endDate = $this->timeline->is_extended && $this->timeline->extension_end_date
            ? $this->timeline->extension_end_date
            : $this->timeline->end_date;

        return new Content(
            view: 'emails.aper-timeline-closing',
            with: [
                'officer' => $this->officer,
                'timeline' => $this->timeline,
                'endDate' => $endDate,
                'daysRemaining' => $this->daysRemaining,
                'hasDraft' => $this->hasDraft,
                'formId' => $this->formId,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

