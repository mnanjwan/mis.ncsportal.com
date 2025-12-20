<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'dismissed',
        'quartered',
        'is_deceased',
        'deceased_date',
        'is_active',
        'profile_picture_url',
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
            'dismissed' => 'boolean',
            'quartered' => 'boolean',
            'is_deceased' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->initials} {$this->surname}";
    }

    // Mutators - Ensure service number always starts with NCS
    public function setServiceNumberAttribute($value)
    {
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

    /**
     * Check if officer has completed onboarding
     * Onboarding is complete when all required fields are filled (not placeholder values)
     */
    public function hasCompletedOnboarding(): bool
    {
        // Check if officer has all required fields filled (not placeholder values)
        $requiredFields = [
            'initials' => ['TBD'],
            'surname' => ['TBD'],
            'substantive_rank' => ['TBD'],
            'salary_grade_level' => ['TBD'],
            'state_of_origin' => ['TBD'],
            'lga' => ['TBD'],
            'geopolitical_zone' => ['TBD'],
            'entry_qualification' => ['TBD'],
            'date_of_birth' => ['1900-01-01'],
            'date_of_first_appointment' => null, // Just check if not null
            'date_of_present_appointment' => null, // Just check if not null
            'phone_number' => ['00000000000'],
            'permanent_home_address' => ['To be provided during onboarding'],
            'bank_name' => null, // Just check if not null
            'bank_account_number' => null, // Just check if not null
            'pfa_name' => null, // Just check if not null
            'rsa_number' => null, // Just check if not null
        ];

        foreach ($requiredFields as $field => $invalidValues) {
            $value = $this->$field;
            
            // If field is null, onboarding is not complete
            if ($value === null) {
                return false;
            }
            
            // If field has invalid placeholder values, onboarding is not complete
            if ($invalidValues !== null && in_array($value, $invalidValues)) {
                return false;
            }
        }

        // Check if next of kin exists (required for onboarding completion)
        if (!$this->nextOfKin()->where('is_primary', true)->exists()) {
            return false;
        }

        return true;
    }
}

