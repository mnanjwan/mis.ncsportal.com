<?php

namespace App\Mail;

use App\Models\Query;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QueryResponseSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Query $query
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Query Response Submitted - ' . ($this->query->officer->service_number ?? 'N/A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.query-response-submitted',
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
