<?php

namespace App\Mail;

use App\Models\Query;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class QueryRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Query $query
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Query Rejected - Not Added to Disciplinary Record',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.query-rejected',
            with: [
                'query' => $this->query,
                'officer' => $this->query->officer,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
