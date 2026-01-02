<?php

namespace App\Mail;

use App\Models\InternalStaffOrder;
use App\Models\User;
use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InternalStaffOrderApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InternalStaffOrder $order,
        public User $user,
        public string $commandName,
        public string $officerName,
        public string $serviceNumber,
        public string $targetUnit,
        public string $targetRole,
        public ?Officer $outgoingOfficer = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Internal Staff Order Approved - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.internal-staff-order-approved',
            with: [
                'order' => $this->order,
                'orderId' => $this->order->id,
                'orderNumber' => $this->order->order_number,
                'user' => $this->user,
                'commandName' => $this->commandName,
                'officerName' => $this->officerName,
                'serviceNumber' => $this->serviceNumber,
                'targetUnit' => $this->targetUnit,
                'targetRole' => $this->targetRole,
                'currentUnit' => $this->order->current_unit,
                'currentRole' => $this->order->current_role,
                'outgoingOfficer' => $this->outgoingOfficer,
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
