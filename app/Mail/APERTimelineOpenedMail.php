<?php

namespace App\Mail;

use App\Models\Officer;
use App\Models\APERTimeline;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class APERTimelineOpenedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Officer $officer,
        public APERTimeline $timeline
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APER Form Submission Period Now Open - ' . $this->timeline->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aper-timeline-opened',
            with: [
                'officer' => $this->officer,
                'timeline' => $this->timeline,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

