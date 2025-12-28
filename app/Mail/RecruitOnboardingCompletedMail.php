<?php

namespace App\Mail;

use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruitOnboardingCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recruit;
    public $recruitName;

    /**
     * Create a new message instance.
     */
    public function __construct(Officer $recruit, $recruitName = null)
    {
        $this->recruit = $recruit;
        $this->recruitName = $recruitName ?? trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Recruit Onboarding Completed - ' . $this->recruitName)
                    ->view('emails.recruit-onboarding-completed')
                    ->with([
                        'recruit' => $this->recruit,
                        'recruitName' => $this->recruitName,
                    ]);
    }
}

