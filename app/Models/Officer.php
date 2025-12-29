<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Officer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_number',
        'appointment_number',
        'initials',
        'surname',
        'sex',
        'date_of_birth',
        'date_of_first_appointment',
        'date_of_present_appointment',
        'substantive_rank',
        'salary_grade_level',
        'state_of_origin',
        'lga',
        'geopolitical_zone',
        'marital_status',
        'entry_qualification',
        'discipline',
        'additional_qualification',
        'present_station',
        'date_posted_to_station',
        'residential_address',
        'permanent_home_address',
        'phone_number',
        'email',
        'personal_email',
        'customs_email',
        'email_status',
        'bank_name',
        'bank_account_number',
        'sort_code',
        'pfa_name',
        'rsa_number',
        'unit',
        'interdicted',
        'suspended',
        'ongoing_investigation',
        'dismissed',
        'quartered',
        'is_deceased',
        'deceased_date',
        'is_active',
        'preretirement_leave_status',
        'preretirement_leave_started_at',
        'profile_picture_url',
        'onboarding_status',
        'verification_status',
        'onboarding_token',
        'onboarding_link_sent_at',
        'onboarding_completed_at',
        'verified_at',
        'verification_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_first_appointment' => 'date',
            'date_of_present_appointment' => 'date',
            'date_posted_to_station' => 'date',
            'deceased_date' => 'date',
            'interdicted' => 'boolean',
            'suspended' => 'boolean',
            'ongoing_investigation' => 'boolean',
            'dismissed' => 'boolean',
            'quartered' => 'boolean',
            'is_deceased' => 'boolean',
            'is_active' => 'boolean',
            'preretirement_leave_started_at' => 'datetime',
        ];
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->initials} {$this->surname}";
    }

    /**
     * Get the full URL for the profile picture
     * Returns the full URL to the profile picture or null if not set
     */
    public function getProfilePictureUrlFull(): ?string
    {
        $path = $this->attributes['profile_picture_url'] ?? null;
        
        if (empty($path)) {
            return null;
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        // Get base URL from config (uses APP_URL/storage) or fallback to asset()
        $baseUrl = config('filesystems.disks.public.url');
        if (empty($baseUrl)) {
            // Fallback: use asset() helper which works with symlink
            return asset('storage/' . $path);
        }
        
        // Ensure proper URL formatting
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    // Mutators - Ensure service number always starts with NCS
    public function setServiceNumberAttribute($value)
    {
        // Handle null or empty values
        if (empty($value) || is_null($value)) {
            $this->attributes['service_number'] = null;
            return;
        }
        
        // Remove any existing NCS prefix to avoid duplication
        $value = preg_replace('/^NCS/i', '', $value);
        // Add NCS prefix
        $this->attributes['service_number'] = 'NCS' . strtoupper(trim($value));
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function presentStation()
    {
        return $this->belongsTo(Command::class, 'present_station');
    }

    public function nextOfKin()
    {
        return $this->hasMany(NextOfKin::class);
    }

    public function documents()
    {
        return $this->hasMany(OfficerDocument::class);
    }

    public function emoluments()
    {
        return $this->hasMany(Emolument::class);
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function passApplications()
    {
        return $this->hasMany(PassApplication::class);
    }

    public function postings()
    {
        return $this->hasMany(OfficerPosting::class);
    }

    public function currentPosting()
    {
        return $this->hasOne(OfficerPosting::class)->where('is_current', true);
    }

    public function courses()
    {
        return $this->hasMany(OfficerCourse::class);
    }

    public function quarters()
    {
        return $this->hasMany(OfficerQuarter::class);
    }

    public function currentQuarter()
    {
        return $this->hasOne(OfficerQuarter::class)->where('is_current', true);
    }

    public function quarterRequests()
    {
        return $this->hasMany(QuarterRequest::class);
    }

    public function chatRoomMembers()
    {
        return $this->hasMany(ChatRoomMember::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function deceasedRecord()
    {
        return $this->hasOne(DeceasedOfficer::class);
    }

    public function retirementAlert()
    {
        return $this->hasOne(RetirementAlert::class);
    }

    /**
     * Calculate retirement date based on age (60 years) or service (35 years), whichever comes first
     */
    public function calculateRetirementDate(): ?\Carbon\Carbon
    {
        if (!$this->date_of_birth || !$this->date_of_first_appointment) {
            return null;
        }

        // Age-based retirement: Date of Birth + 60 years
        $ageBasedRetirement = $this->date_of_birth->copy()->addYears(60);

        // Service-based retirement: Date of First Appointment + 35 years
        $serviceBasedRetirement = $this->date_of_first_appointment->copy()->addYears(35);

        // Return whichever comes earlier
        return $ageBasedRetirement->lt($serviceBasedRetirement) ? $ageBasedRetirement : $serviceBasedRetirement;
    }

    /**
     * Get retirement type (AGE or SVC)
     */
    public function getRetirementType(): ?string
    {
        if (!$this->date_of_birth || !$this->date_of_first_appointment) {
            return null;
        }

        $ageBasedRetirement = $this->date_of_birth->copy()->addYears(60);
        $serviceBasedRetirement = $this->date_of_first_appointment->copy()->addYears(35);

        return $ageBasedRetirement->lt($serviceBasedRetirement) ? 'AGE' : 'SVC';
    }

    /**
     * Get date when alert should be sent (3 months before retirement)
     */
    public function getAlertDate(): ?\Carbon\Carbon
    {
        $retirementDate = $this->calculateRetirementDate();
        return $retirementDate ? $retirementDate->copy()->subMonths(3) : null;
    }

    /**
     * Check if officer is within 3 months of retirement
     */
    public function isApproachingRetirement(): bool
    {
        $alertDate = $this->getAlertDate();
        if (!$alertDate) {
            return false;
        }

        $today = now()->startOfDay();
        $alertDateStart = $alertDate->startOfDay();

        // Check if today is on or after the alert date, but before retirement date
        return $today->gte($alertDateStart) && $today->lt($this->calculateRetirementDate());
    }

    /**
     * Get days until retirement
     */
    public function getDaysUntilRetirement(): ?int
    {
        $retirementDate = $this->calculateRetirementDate();
        if (!$retirementDate) {
            return null;
        }

        return max(0, now()->diffInDays($retirementDate, false));
    }

    /**
     * Get time in service (from date of first appointment to now)
     * Returns an array with years, months, and days
     */
    public function getTimeInService(): ?array
    {
        if (!$this->date_of_first_appointment) {
            return null;
        }

        $startDate = $this->date_of_first_appointment;
        $endDate = now();

        $diff = $startDate->diff($endDate);

        return [
            'years' => (int) $diff->y,
            'months' => (int) $diff->m,
            'days' => (int) $diff->d,
            'total_days' => (int) $startDate->diffInDays($endDate),
        ];
    }

    /**
     * Get time left in service (from now to retirement date)
     * Returns an array with years, months, and days
     */
    public function getTimeLeftInService(): ?array
    {
        $retirementDate = $this->calculateRetirementDate();
        if (!$retirementDate) {
            return null;
        }

        $startDate = now();
        $endDate = $retirementDate;

        // If retirement date has passed, return zeros
        if ($endDate->lt($startDate)) {
            return [
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'total_days' => 0,
            ];
        }

        $diff = $startDate->diff($endDate);

        return [
            'years' => (int) $diff->y,
            'months' => (int) $diff->m,
            'days' => (int) $diff->d,
            'total_days' => (int) $startDate->diffInDays($endDate),
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountChangeRequests()
    {
        return $this->hasMany(AccountChangeRequest::class);
    }

    public function trainingResult()
    {
        return $this->hasOne(TrainingResult::class);
    }

    public function queries()
    {
        return $this->hasMany(Query::class);
    }

    public function acceptedQueries()
    {
        return $this->hasMany(Query::class)->where('status', 'ACCEPTED');
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function currentInvestigation()
    {
        return $this->hasOne(Investigation::class)
            ->whereIn('status', ['INVITED', 'ONGOING_INVESTIGATION', 'INTERDICTED', 'SUSPENDED'])
            ->latest();
    }

    /**
     * Check if officer has completed onboarding
     * Onboarding is complete when profile picture is uploaded (indicates completion of onboarding form)
     */
    public function hasCompletedOnboarding(): bool
    {
        // Primary indicator: Profile photo is uploaded during onboarding step 4
        // This is the most reliable indicator that onboarding was completed
        if (!empty($this->profile_picture_url)) {
            return true;
        }

        return false;
    }
}

