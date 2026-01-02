<?php

namespace App\Mail;

use App\Models\StaffOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffOrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StaffOrder $staffOrder,
        public User $user,
        public string $fromCommandName,
        public string $toCommandName
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Staff Order Created - Order Number: ' . $this->staffOrder->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-order-created',
            with: [
                'staffOrder' => $this->staffOrder,
                'orderId' => $this->staffOrder->id,
                'orderNumber' => $this->staffOrder->order_number,
                'user' => $this->user,
                'fromCommandName' => $this->fromCommandName,
                'toCommandName' => $this->toCommandName,
                'effectiveDate' => $this->staffOrder->effective_date,
                'status' => $this->staffOrder->status,
                'description' => $this->staffOrder->description,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

