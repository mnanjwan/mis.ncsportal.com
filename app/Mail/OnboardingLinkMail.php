<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnboardingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $onboardingLink;
    public $tempPassword;
    public $officerName;

    /**
     * Create a new message instance.
     */
    public function __construct($onboardingLink, $tempPassword, $officerName = null)
    {
        $this->onboardingLink = $onboardingLink;
        $this->tempPassword = $tempPassword;
        $this->officerName = $officerName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('NCS Employee Portal - Onboarding Invitation')
                    ->view('emails.onboarding-link')
                    ->with([
                        'onboardingLink' => $this->onboardingLink,
                        'tempPassword' => $this->tempPassword,
                        'officerName' => $this->officerName,
                    ]);
    }
}

