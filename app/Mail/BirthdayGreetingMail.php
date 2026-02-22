<?php

namespace App\Mail;

use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BirthdayGreetingMail extends Mailable
{
    use Queueable, SerializesModels;

    public Officer $officer;

    public string $officerName;

    /**
     * Create a new message instance.
     */
    public function __construct(Officer $officer, ?string $officerName = null)
    {
        $this->officer = $officer;
        $this->officerName = $officerName ?? trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Happy Birthday! – ' . $this->officerName)
            ->view('emails.birthday-greeting')
            ->with([
                'officer' => $this->officer,
                'officerName' => $this->officerName,
            ]);
    }
}
