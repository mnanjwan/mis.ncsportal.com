<?php

namespace App\Mail;

use App\Models\Query;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QueryExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Query $query
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Query Expired - Added to Disciplinary Record',
        );
    }

    public function content(): Content
    {
        $issuedBy = $this->query->issuedBy;
        
        return new Content(
            view: 'emails.query-expired',
            with: [
                'query' => $this->query,
                'officer' => $this->query->officer,
                'issuedBy' => $issuedBy,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
