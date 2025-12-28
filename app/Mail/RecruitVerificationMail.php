<?php

namespace App\Mail;

use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruitVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recruit;
    public $recruitName;
    public $verificationStatus;
    public $verificationNotes;

    /**
     * Create a new message instance.
     */
    public function __construct(Officer $recruit, $verificationStatus, $verificationNotes = null)
    {
        $this->recruit = $recruit;
        $this->recruitName = trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
        $this->verificationStatus = $verificationStatus;
        $this->verificationNotes = $verificationNotes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->verificationStatus === 'verified' 
            ? 'Onboarding Verification Approved - ' . $this->recruitName
            : 'Onboarding Verification - ' . $this->recruitName;
            
        return $this->subject($subject)
                    ->view('emails.recruit-verification')
                    ->with([
                        'recruit' => $this->recruit,
                        'recruitName' => $this->recruitName,
                        'verificationStatus' => $this->verificationStatus,
                        'verificationNotes' => $this->verificationNotes,
                    ]);
    }
}

