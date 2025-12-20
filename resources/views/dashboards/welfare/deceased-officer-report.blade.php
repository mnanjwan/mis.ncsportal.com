@extends('layouts.app')

@section('title', 'Deceased Officer Report')
@section('page-title', 'Deceased Officer Report')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('welfare.dashboard') }}">Welfare</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('welfare.deceased-officers') }}">Deceased Officers</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('welfare.deceased-officers.show', $deceasedOfficer->id) }}">Details</a>
    <span>/</span>
    <span class="text-primary">Report</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Report Header -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Deceased Officer Comprehensive Data Report</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('welfare.deceased-officers.export', $deceasedOfficer->id) }}" class="kt-btn kt-btn-sm kt-btn-success">
                        <i class="ki-filled ki-file-down"></i> Export CSV
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">SVC no</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $deceasedOfficer->officer->service_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Rank</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->officer->substantive_rank ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">DOB</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->officer->date_of_birth ? $deceasedOfficer->officer->date_of_birth->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Date of Death</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->date_of_death->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next of Kin(s) -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Next of Kin(s)</h3>
            </div>
            <div class="kt-card-content">
                @if($deceasedOfficer->next_of_kin_data && count($deceasedOfficer->next_of_kin_data) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($deceasedOfficer->next_of_kin_data as $index => $kin)
                            <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                <h4 class="text-sm font-semibold text-foreground mb-3">Next of Kin {{ $index + 1 }}</h4>
                                <div class="flex flex-col gap-2">
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Name</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['name'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Relationship</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['relationship'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Phone Number</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['phone_number'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Email</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['email'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Address</span>
                                        <p class="text-sm font-medium text-foreground">{{ $kin['address'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-secondary-foreground text-center py-4">No Next of Kin information available</p>
                @endif
            </div>
        </div>

        <!-- Banking Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Banking Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Bank Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->bank_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Account Number</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $deceasedOfficer->bank_account_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Retirement Savings Account Administrator (RSA)</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $deceasedOfficer->rsa_administrator ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('welfare.deceased-officers.show', $deceasedOfficer->id) }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-left"></i> Back to Details
                    </a>
                    <a href="{{ route('welfare.deceased-officers.export', $deceasedOfficer->id) }}" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-file-down"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
