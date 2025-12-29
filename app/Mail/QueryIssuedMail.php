<?php

namespace App\Mail;

use App\Models\Query;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QueryIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Query $query
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Query Issued - ' . ($this->query->officer->service_number ?? 'N/A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.query-issued',
            with: [
                'query' => $this->query,
                'officer' => $this->query->officer,
                'issuedBy' => $this->query->issuedBy,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
