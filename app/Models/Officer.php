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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

