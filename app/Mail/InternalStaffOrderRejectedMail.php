<?php

namespace App\Mail;

use App\Models\InternalStaffOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InternalStaffOrderRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InternalStaffOrder $order,
        public User $user,
        public string $rejectedByName,
        public string $rejectionReason,
        public string $commandName
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Internal Staff Order Rejected - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.internal-staff-order-rejected',
            with: [
                'order' => $this->order,
                'orderId' => $this->order->id,
                'orderNumber' => $this->order->order_number,
                'user' => $this->user,
                'rejectedByName' => $this->rejectedByName,
                'rejectionReason' => $this->rejectionReason,
                'commandName' => $this->commandName,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
