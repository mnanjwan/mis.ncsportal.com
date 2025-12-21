@extends('layouts.app')

@section('title', 'Officer Details')
@section('page-title', 'Officer Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.officers') }}">Officers</a>
    <span>/</span>
    <span class="text-primary">View Officer</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.officers') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Officers List
            </a>
            <div class="flex gap-2">
                <a href="{{ route('hrd.officers.edit', $officer->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-pencil"></i> Edit Officer
                </a>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-5">
                    <div class="kt-avatar size-24">
                        <div class="kt-avatar-image">
                            @if($officer->getProfilePictureUrlFull())
                                <img alt="avatar" src="{{ $officer->getProfilePictureUrlFull() }}" />
                            @else
                                <div class="flex items-center justify-center size-24 rounded-full bg-primary/10 text-primary font-bold text-xl">
                                    {{ strtoupper(($officer->initials[0] ?? '') . ($officer->surname[0] ?? '')) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 grow">
                        <h2 class="text-2xl font-semibold text-mono">
                            {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="text-secondary-foreground">
                                Service Number: <span class="font-semibold text-mono">{{ $officer->service_number ?? 'N/A' }}</span>
                            </span>
                            <span class="text-secondary-foreground">
                                Rank: <span class="font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                            </span>
                            <span class="text-secondary-foreground">
                                Command: <span class="font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Personal Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Personal Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of Birth</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_of_birth ? $officer->date_of_birth->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Sex</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->sex ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Marital Status</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->marital_status ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">State of Origin</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->state_of_origin ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">LGA</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->lga ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Geopolitical Zone</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->geopolitical_zone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Service Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of First Appointment</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of Present Appointment</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_of_present_appointment ? $officer->date_of_present_appointment->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Substantive Rank</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Salary Grade Level</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->salary_grade_level ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Zone</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->zone->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Command/Present Station</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date Posted to Station</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_posted_to_station ? $officer->date_posted_to_station->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        @if($officer->unit)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Unit</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->unit }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Contact Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Email</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Phone Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->phone_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Residential Address</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->residential_address ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Permanent Home Address</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->permanent_home_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Educational & Professional Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Educational & Professional</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @php
                            // Build education array from entry_qualification and additional_qualification
                            $educationEntries = [];
                            
                            // First, try to get all education entries from additional_qualification JSON
                            if ($officer->additional_qualification) {
                                $allEducation = json_decode($officer->additional_qualification, true);
                                if (is_array($allEducation) && count($allEducation) > 0) {
                                    // Check if first entry in JSON matches entry_qualification (new format with all entries)
                                    $firstEntryMatches = isset($allEducation[0]) && 
                                        isset($allEducation[0]['qualification']) && 
                                        $allEducation[0]['qualification'] === $officer->entry_qualification;
                                    
                                    if ($firstEntryMatches) {
                                        // New format: All entries (including first) are in JSON with universities
                                        $educationEntries = $allEducation;
                                    } else {
                                        // Old format: JSON only has entries from index 1, need to prepend first entry
                                        $firstEntry = [
                                            'university' => '', // Not stored in old format
                                            'qualification' => $officer->entry_qualification,
                                            'discipline' => $officer->discipline ?? ''
                                        ];
                                        $educationEntries = array_merge([$firstEntry], $allEducation);
                                    }
                                }
                            }
                            
                            // Fallback: If no JSON data, reconstruct from legacy fields only
                            if (empty($educationEntries) && $officer->entry_qualification) {
                                // First education entry from entry_qualification and discipline (no university available)
                                $educationEntries[] = [
                                    'university' => '', // Not stored in legacy format
                                    'qualification' => $officer->entry_qualification,
                                    'discipline' => $officer->discipline ?? ''
                                ];
                            }
                        @endphp
                        
                        @if(count($educationEntries) > 0)
                            @foreach($educationEntries as $index => $edu)
                                <div class="border-b border-input pb-4 {{ $index > 0 ? 'mt-4' : '' }}">
                                    <div class="text-xs text-secondary-foreground mb-2 font-semibold">Education Entry #{{ $index + 1 }}</div>
                                    @if(!empty($edu['university']))
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-secondary-foreground">University</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['university'] }}</span>
                                    </div>
                                    @endif
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-secondary-foreground">Qualification</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['qualification'] ?? 'N/A' }}</span>
                        </div>
                                    @if(!empty($edu['discipline']))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Discipline</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['discipline'] }}</span>
                        </div>
                                    @endif
                        </div>
                            @endforeach
                        @else
                            <div class="text-sm text-secondary-foreground">No education information available</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Financial Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Bank Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->bank_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Bank Account Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->bank_account_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Sort Code</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->sort_code ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">PFA Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->pfa_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">RSA Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->rsa_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Status Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Status</span>
                            <span class="kt-badge kt-badge-{{ $officer->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                {{ $officer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Interdicted</span>
                            <span class="kt-badge kt-badge-{{ $officer->interdicted ? 'danger' : 'success' }} kt-badge-sm">
                                {{ $officer->interdicted ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Suspended</span>
                            <span class="kt-badge kt-badge-{{ $officer->suspended ? 'danger' : 'success' }} kt-badge-sm">
                                {{ $officer->suspended ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Dismissed</span>
                            <span class="kt-badge kt-badge-{{ $officer->dismissed ? 'danger' : 'success' }} kt-badge-sm">
                                {{ $officer->dismissed ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Quartered</span>
                            <span class="kt-badge kt-badge-{{ $officer->quartered ? 'success' : 'secondary' }} kt-badge-sm">
                                {{ $officer->quartered ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if($officer->is_deceased)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Deceased Date</span>
                                <span class="text-sm font-semibold text-mono">
                                    {{ $officer->deceased_date ? $officer->deceased_date->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Next of Kin Information -->
            @php
                $nextOfKin = $officer->nextOfKin ?? collect();
            @endphp
            @if($nextOfKin->count() > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Next of Kin Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @foreach($nextOfKin as $index => $nok)
                            <div class="border-b border-input pb-4 {{ $index > 0 ? 'mt-4' : '' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-xs text-secondary-foreground font-semibold">Next of Kin #{{ $index + 1 }}</div>
                                    @if($nok->is_primary)
                                        <span class="kt-badge kt-badge-success kt-badge-sm">Primary</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Name</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->name }}</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Relationship</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->relationship }}</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Phone Number</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->phone_number }}</span>
                                </div>
                                @if($nok->email)
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Email</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->email }}</span>
                                </div>
                                @endif
                                <div class="flex items-start justify-between">
                                    <span class="text-sm text-secondary-foreground">Address</span>
                                    <span class="text-sm font-semibold text-mono text-right max-w-[60%]">{{ $nok->address }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

