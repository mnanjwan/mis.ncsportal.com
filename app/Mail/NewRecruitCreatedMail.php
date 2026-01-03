<?php

namespace App\Mail;

use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewRecruitCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recruit;
    public $appUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Officer $recruit)
    {
        $this->recruit = $recruit;
        $this->appUrl = config('app.url');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        Log::info('NewRecruitCreatedMail: build() method called', [
            'recruit_id' => $this->recruit->id ?? null,
            'recruit_email' => $this->recruit->email ?? null,
            'appointment_number' => $this->recruit->appointment_number ?? null,
        ]);

        return $this->subject('Welcome to NCS - Your Recruit Profile Has Been Created')
                    ->view('emails.new-recruit-created')
                    ->with([
                        'recruit' => $this->recruit,
                        'appUrl' => $this->appUrl,
                    ]);
    }
}

