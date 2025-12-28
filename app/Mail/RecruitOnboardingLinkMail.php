<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruitOnboardingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $onboardingLink;
    public $recruitName;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct($onboardingLink, $recruitName = null, $email = null)
    {
        $this->onboardingLink = $onboardingLink;
        $this->recruitName = $recruitName;
        $this->email = $email;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('NCS Employee Portal - Complete Your Onboarding')
                    ->view('emails.recruit-onboarding-link')
                    ->with([
                        'onboardingLink' => $this->onboardingLink,
                        'recruitName' => $this->recruitName,
                        'email' => $this->email,
                    ]);
    }
}

