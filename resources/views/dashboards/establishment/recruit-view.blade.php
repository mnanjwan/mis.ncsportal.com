@extends('layouts.app')

@section('title', 'View Recruit Details')
@section('page-title', 'View Recruit Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.new-recruits') }}">New Recruits</a>
    <span>/</span>
    <span class="text-primary">View Recruit</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('establishment.new-recruits') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to New Recruits
        </a>
        @if($recruit->onboarding_status === 'completed' && $recruit->verification_status === 'pending')
        <button type="button" 
                onclick="showVerifyModal({{ $recruit->id }}, '{{ $recruit->initials }} {{ $recruit->surname }}');"
                class="kt-btn kt-btn-sm kt-btn-success">
            <i class="ki-filled ki-check-circle"></i> Verify Documents
        </button>
        @endif
    </div>

    <!-- Profile Header -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex flex-col lg:flex-row items-start lg:items-center gap-5">
                <div class="kt-avatar size-24">
                    <div class="kt-avatar-image">
                        @if($recruit->getProfilePictureUrlFull())
                            <img alt="avatar" src="{{ $recruit->getProfilePictureUrlFull() }}" />
                        @else
                            <div class="flex items-center justify-center size-24 rounded-full bg-primary/10 text-primary font-bold text-xl">
                                {{ strtoupper(($recruit->initials[0] ?? '') . ($recruit->surname[0] ?? '')) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col gap-2 grow">
                    <h2 class="text-2xl font-semibold text-mono">
                        {{ $recruit->initials ?? '' }} {{ $recruit->surname ?? '' }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Appointment Number: <span class="font-semibold text-mono">{{ $recruit->appointment_number ?? 'N/A' }}</span>
                        </span>
                        <span class="text-secondary-foreground">
                            Rank: <span class="font-semibold text-mono">{{ $recruit->substantive_rank ?? 'N/A' }}</span>
                        </span>
                        <span class="text-secondary-foreground">
                            Email: <span class="font-semibold text-mono">{{ $recruit->email ?? 'N/A' }}</span>
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="kt-badge {{ $recruit->onboarding_status === 'completed' ? 'kt-badge-success' : ($recruit->onboarding_status === 'verified' ? 'kt-badge-info' : 'kt-badge-warning') }}">
                            Onboarding: {{ ucfirst(str_replace('_', ' ', $recruit->onboarding_status ?? 'pending')) }}
                        </span>
                        @if($recruit->verification_status)
                        <span class="kt-badge {{ $recruit->verification_status === 'verified' ? 'kt-badge-success' : ($recruit->verification_status === 'rejected' ? 'kt-badge-danger' : 'kt-badge-warning') }}">
                            Verification: {{ ucfirst($recruit->verification_status) }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recruit Information -->
    <div class="grid lg:grid-cols-2 gap-5 lg:gap-7.5">
        <!-- Left Column -->
        <div class="flex flex-col gap-5">
            <!-- Step 1: Personal Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Personal Information</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->initials ?? '' }} {{ $recruit->surname ?? '' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Gender</span>
                            <span class="text-sm font-semibold text-mono">{{ ($recruit->sex ?? '') == 'M' ? 'Male' : (($recruit->sex ?? '') == 'F' ? 'Female' : 'N/A') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of Birth</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->date_of_birth ? $recruit->date_of_birth->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Phone</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->phone_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Email</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">State of Origin</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->state_of_origin ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">LGA</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->lga ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Geopolitical Zone</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->geopolitical_zone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Marital Status</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->marital_status ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-start justify-between">
                            <span class="text-sm text-secondary-foreground">Residential Address</span>
                            <span class="text-sm font-semibold text-mono text-right max-w-[60%]">{{ $recruit->residential_address ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-start justify-between">
                            <span class="text-sm text-secondary-foreground">Permanent Home Address</span>
                            <span class="text-sm font-semibold text-mono text-right max-w-[60%]">{{ $recruit->permanent_home_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Employment Details -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Employment Details</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of First Appointment</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->date_of_first_appointment ? $recruit->date_of_first_appointment->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of Present Appointment</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->date_of_present_appointment ? $recruit->date_of_present_appointment->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Substantive Rank</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->substantive_rank ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Salary Grade Level</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->salary_grade_level ?? 'N/A' }}</span>
                        </div>
                        @if($zone)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Zone</span>
                            <span class="text-sm font-semibold text-mono">{{ $zone->name ?? 'N/A' }}</span>
                        </div>
                        @endif
                        @if($command)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Command/Present Station</span>
                            <span class="text-sm font-semibold text-mono">{{ $command->name ?? 'N/A' }}</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date Posted to Station</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->date_posted_to_station ? $recruit->date_posted_to_station->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        @if($recruit->unit)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Unit</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->unit }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Education Details -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Education Details</h4>
                </div>
                <div class="kt-card-content">
                    @php
                        $education = [];
                        if ($recruit->additional_qualification) {
                            $education = json_decode($recruit->additional_qualification, true);
                            if (!is_array($education)) {
                                $education = [];
                            }
                        }
                    @endphp
                    @if(count($education) > 0)
                        <div class="space-y-4">
                            @foreach($education as $index => $edu)
                                <div class="border border-border rounded-lg p-4 {{ $index === 0 ? 'bg-primary/5 border-primary/20' : '' }}">
                                    @if($index === 0)
                                        <div class="text-xs text-primary font-semibold mb-3">Entry Qualification</div>
                                    @else
                                        <div class="text-xs text-secondary-foreground font-semibold mb-3">Additional Qualification #{{ $index + 1 }}</div>
                                    @endif
                                    <div class="grid gap-3">
                                        @if(isset($edu['qualification']) && $edu['qualification'])
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Qualification</span>
                                            <span class="text-sm font-semibold text-mono">{{ $edu['qualification'] }}</span>
                                        </div>
                                        @endif
                                        @if(isset($edu['university']) && $edu['university'])
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Institution/University</span>
                                            <span class="text-sm font-semibold text-mono">{{ $edu['university'] }}</span>
                                        </div>
                                        @elseif(isset($edu['institution']) && $edu['institution'])
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Institution/University</span>
                                            <span class="text-sm font-semibold text-mono">{{ $edu['institution'] }}</span>
                                        </div>
                                        @endif
                                        @if(isset($edu['discipline']) && $edu['discipline'])
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Discipline</span>
                                            <span class="text-sm font-semibold text-mono">{{ $edu['discipline'] }}</span>
                                        </div>
                                        @endif
                                        @if(isset($edu['year_obtained']) && $edu['year_obtained'])
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Year Obtained</span>
                                            <span class="text-sm font-semibold text-mono">{{ $edu['year_obtained'] }}</span>
                                        </div>
                                        @elseif(isset($edu['year']) && $edu['year'])
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Year Obtained</span>
                                            <span class="text-sm font-semibold text-mono">{{ $edu['year'] }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @if($recruit->entry_qualification)
                        <div class="grid gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Entry Qualification</span>
                                <span class="text-sm font-semibold text-mono">{{ $recruit->entry_qualification }}</span>
                            </div>
                            @if($recruit->discipline)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Discipline</span>
                                <span class="text-sm font-semibold text-mono">{{ $recruit->discipline }}</span>
                            </div>
                            @endif
                        </div>
                        @else
                        <p class="text-sm text-secondary-foreground">No education information available.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="flex flex-col gap-5">
            <!-- Step 3: Banking Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Banking Information</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Bank Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->bank_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Account Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->bank_account_number ?? 'N/A' }}</span>
                        </div>
                        @if($recruit->sort_code)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Sort Code</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->sort_code }}</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">PFA Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->pfa_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">RSA Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $recruit->rsa_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Next of Kin -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Next of Kin</h4>
                </div>
                <div class="kt-card-content">
                    @php
                        $nextOfKin = $recruit->nextOfKin ?? collect();
                    @endphp
                    @if($nextOfKin->count() > 0)
                        <div class="space-y-4">
                            @foreach($nextOfKin as $index => $nok)
                                <div class="border border-border rounded-lg p-4 {{ $nok->is_primary ? 'bg-primary/5 border-primary/20' : '' }}">
                                    @if($nok->is_primary)
                                        <div class="text-xs text-primary font-semibold mb-2">Primary Next of Kin</div>
                                    @endif
                                    <div class="grid gap-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Name</span>
                                            <span class="text-sm font-semibold text-mono">{{ $nok->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Relationship</span>
                                            <span class="text-sm font-semibold text-mono">{{ $nok->relationship ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Phone</span>
                                            <span class="text-sm font-semibold text-mono">{{ $nok->phone_number ?? 'N/A' }}</span>
                                        </div>
                                        @if($nok->email)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-secondary-foreground">Email</span>
                                            <span class="text-sm font-semibold text-mono">{{ $nok->email }}</span>
                                        </div>
                                        @endif
                                        <div class="flex items-start justify-between">
                                            <span class="text-sm text-secondary-foreground">Address</span>
                                            <span class="text-sm font-semibold text-mono text-right max-w-[60%]">{{ $nok->address ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-secondary-foreground">No next of kin information available.</p>
                    @endif
                </div>
            </div>

            <!-- Step 4: Uploaded Documents -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Uploaded Documents</h4>
                </div>
                <div class="kt-card-content">
                    @php
                        $documents = $recruit->documents ?? collect();
                        $documentsCount = $documents->count();
                    @endphp
                    @if($documentsCount > 0)
                        <div class="mb-3">
                            <p class="text-sm text-secondary-foreground">
                                Found <strong>{{ $documentsCount }}</strong> document(s) for this recruit.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($documents as $doc)
                                @php
                                    $isImage = str_starts_with($doc->mime_type ?? '', 'image/');
                                    $fileUrl = $doc->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($doc->file_path) : null;
                                    $fileExists = $doc->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path) : false;
                                @endphp
                                <div class="border border-border rounded-lg p-3 hover:border-primary/50 transition-colors cursor-pointer document-item {{ !$fileExists ? 'border-danger/50 bg-danger/5' : '' }}"
                                     data-file-url="{{ $fileUrl }}"
                                     data-file-name="{{ $doc->file_name }}"
                                     data-is-image="{{ $isImage ? '1' : '0' }}">
                                    @if($isImage && $fileUrl && $fileExists)
                                        <img src="{{ $fileUrl }}" 
                                             alt="{{ $doc->file_name ?? 'Document' }}"
                                             class="w-full h-32 object-cover rounded mb-2">
                                    @else
                                        <div class="w-full h-32 flex items-center justify-center bg-muted rounded mb-2 {{ !$fileExists ? 'bg-danger/10' : '' }}">
                                            <i class="ki-filled ki-file text-primary text-3xl"></i>
                                        </div>
                                    @endif
                                    <div class="text-xs font-medium text-foreground truncate" title="{{ $doc->file_name }}">
                                        {{ $doc->file_name ?? 'Document' }}
                                    </div>
                                    @if($doc->file_size)
                                    <div class="text-xs text-secondary-foreground">
                                        {{ number_format($doc->file_size / 1024, 2) }} KB
                                    </div>
                                    @endif
                                    @if(!$fileExists)
                                    <div class="text-xs text-danger mt-1">
                                        <i class="ki-filled ki-information"></i> File not found on disk
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-3">
                            <p class="text-sm text-secondary-foreground">
                                <i class="ki-filled ki-information text-info"></i> No documents found in the database for this recruit.
                            </p>
                            @if($recruit->onboarding_status === 'completed')
                            <div class="kt-card bg-warning/10 border border-warning/20 p-3">
                                <p class="text-xs text-warning">
                                    <strong>Note:</strong> This recruit completed onboarding, but no documents were saved. 
                                    This could mean:
                                </p>
                                <ul class="text-xs text-warning mt-2 list-disc list-inside space-y-1">
                                    <li>Documents were not uploaded during onboarding</li>
                                    <li>Documents were uploaded but failed to save (check logs)</li>
                                    <li>Onboarding was completed before document saving was implemented</li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verification Notes (if verified/rejected) -->
            @if($recruit->verification_status && $recruit->verification_notes)
            <div class="kt-card {{ $recruit->verification_status === 'verified' ? 'bg-success/10 border-success/20' : 'bg-danger/10 border-danger/20' }}">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Verification Notes</h4>
                </div>
                <div class="kt-card-content">
                    <p class="text-sm text-secondary-foreground">{{ $recruit->verification_notes }}</p>
                    @if($recruit->verified_at)
                    <p class="text-xs text-secondary-foreground mt-2">
                        Verified on: {{ $recruit->verified_at->format('d/m/Y H:i') }}
                    </p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Document Modal -->
<div id="document-modal" class="kt-modal hidden" data-kt-modal="true">
    <div class="kt-modal-content max-w-4xl">
        <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-foreground" id="modal-document-name">Document</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" onclick="closeDocumentModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-4 px-5">
            <div id="modal-document-content" class="flex items-center justify-center min-h-[400px]">
                <!-- Document content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div class="kt-modal" data-kt-modal="true" id="verify-recruit-modal">
    <div class="kt-modal-content max-w-[500px] top-[20%]">
        <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Verify Recruit Documents</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form action="" method="POST" id="verifyRecruitForm">
            @csrf
            <div class="kt-modal-body py-4 px-5">
                <p class="text-sm text-secondary-foreground mb-4">
                    Verify documents for <strong id="verify-recruit-name"></strong>?
                </p>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            Verification Status <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="verification_status" id="verification_status_id" value="{{ old('verification_status') ?? '' }}" required>
                            <button type="button" 
                                    id="verification_status_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="verification_status_select_text">{{ old('verification_status') ? (old('verification_status') === 'verified' ? 'Verified' : (old('verification_status') === 'rejected' ? 'Rejected' : 'Select status...')) : 'Select status...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="verification_status_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="verification_status_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search status..."
                                           autocomplete="off">
                                </div>
                                <div id="verification_status_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            Verification Notes
                        </label>
                        <textarea name="verification_notes" 
                                 class="kt-input" 
                                 rows="3"
                                 placeholder="Optional notes about the verification..."></textarea>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer py-3 px-5 flex items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <button type="submit" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i>
                    <span>Verify</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle document clicks
        document.querySelectorAll('.document-item').forEach(function(item) {
            item.addEventListener('click', function() {
                const fileUrl = this.getAttribute('data-file-url');
                const fileName = this.getAttribute('data-file-name');
                const isImage = this.getAttribute('data-is-image') === '1';
                
                if (isImage && fileUrl) {
                    showDocumentModal(fileUrl, fileName);
                } else {
                    // For non-image files, open in new tab
                    if (fileUrl) {
                        window.open(fileUrl, '_blank');
                    }
                }
            });
        });
    });

    function showDocumentModal(fileUrl, fileName) {
        const modal = document.getElementById('document-modal');
        const content = document.getElementById('modal-document-content');
        const nameEl = document.getElementById('modal-document-name');
        
        nameEl.textContent = fileName || 'Document';
        content.innerHTML = `<img src="${fileUrl}" alt="${fileName}" class="max-w-full max-h-[70vh] object-contain">`;
        
        modal.classList.remove('hidden');
        if (typeof KTModal !== 'undefined') {
            const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
            modalInstance.show();
        } else {
            modal.style.display = 'flex';
        }
    }

    function closeDocumentModal() {
        const modal = document.getElementById('document-modal');
        if (typeof KTModal !== 'undefined') {
            const modalInstance = KTModal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        } else {
            modal.classList.add('hidden');
        }
    }

    function showVerifyModal(recruitId, recruitName) {
        document.getElementById('verify-recruit-name').textContent = recruitName;
        document.getElementById('verifyRecruitForm').action = '{{ route("establishment.onboarding.verify", ":id") }}'.replace(':id', recruitId);
        const modal = document.getElementById('verify-recruit-modal');
        if (typeof KTModal !== 'undefined') {
            const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
            modalInstance.show();
        } else {
            modal.style.display = 'flex';
        }
    }

    // Reusable function to create searchable select
    function createSearchableSelect(config) {
        const {
            triggerId,
            hiddenInputId,
            dropdownId,
            searchInputId,
            optionsContainerId,
            displayTextId,
            options,
            displayFn,
            onSelect,
            placeholder = 'Select...',
            searchPlaceholder = 'Search...'
        } = config;

        const trigger = document.getElementById(triggerId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const dropdown = document.getElementById(dropdownId);
        const searchInput = document.getElementById(searchInputId);
        const optionsContainer = document.getElementById(optionsContainerId);
        const displayText = document.getElementById(displayTextId);

        if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) {
            return;
        }

        let selectedOption = null;
        let filteredOptions = [...options];

        // Render options
        function renderOptions(opts) {
            if (opts.length === 0) {
                optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                return;
            }

            optionsContainer.innerHTML = opts.map(opt => {
                const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
                const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
                return `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                         data-id="${value}" 
                         data-name="${display}">
                        <div class="text-sm text-foreground">${display}</div>
                    </div>
                `;
            }).join('');

            // Add click handlers
            optionsContainer.querySelectorAll('.select-option').forEach(option => {
                option.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    selectedOption = options.find(o => {
                        const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                        return String(optValue) === String(id);
                    });
                    
                    if (selectedOption || id === '') {
                        hiddenInput.value = id;
                        displayText.textContent = name;
                        dropdown.classList.add('hidden');
                        searchInput.value = '';
                        filteredOptions = [...options];
                        renderOptions(filteredOptions);
                        
                        if (onSelect) onSelect(selectedOption || {id: id, name: name});
                    }
                });
            });
        }

        // Initial render
        renderOptions(filteredOptions);

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filteredOptions = options.filter(opt => {
                const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
                return String(display).toLowerCase().includes(searchTerm);
            });
            renderOptions(filteredOptions);
        });

        // Toggle dropdown
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    // Initialize verification status select
    document.addEventListener('DOMContentLoaded', function() {
        const verificationStatusOptions = [
            {id: '', name: 'Select status...'},
            {id: 'verified', name: 'Verified'},
            {id: 'rejected', name: 'Rejected'}
        ];

        createSearchableSelect({
            triggerId: 'verification_status_select_trigger',
            hiddenInputId: 'verification_status_id',
            dropdownId: 'verification_status_dropdown',
            searchInputId: 'verification_status_search_input',
            optionsContainerId: 'verification_status_options',
            displayTextId: 'verification_status_select_text',
            options: verificationStatusOptions,
            placeholder: 'Select status...',
            searchPlaceholder: 'Search status...'
        });
    });
</script>
@endpush

@endsection

