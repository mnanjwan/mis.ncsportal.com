@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Profile</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Profile Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-5">
                    <div class="kt-avatar size-24">
                        <div class="kt-avatar-image">
                            <img alt="avatar"
                                src="{{ asset('ncs-employee-portal/dist/assets/media/avatars/300-1.png') }}" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 grow">
                        <h2 class="text-2xl font-semibold text-mono" id="officer-name">Loading...</h2>
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="text-secondary-foreground">Service Number: <span class="font-semibold text-mono"
                                    id="service-number">-</span></span>
                            <span class="text-secondary-foreground">Rank: <span class="font-semibold text-mono"
                                    id="rank">-</span></span>
                            <span class="text-secondary-foreground">Command: <span class="font-semibold text-mono"
                                    id="command">-</span></span>
                        </div>
                    </div>
                    <button class="kt-btn kt-btn-primary">Edit Profile</button>
                </div>
            </div>
        </div>
        <!-- End of Profile Header -->
        <!-- Profile Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Personal Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Personal Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="personal-info">
                        <p class="text-secondary-foreground text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>
            <!-- End of Personal Information -->
            <!-- Employment Details -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Employment Details</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="employment-info">
                        <p class="text-secondary-foreground text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>
            <!-- End of Employment Details -->
            <!-- Banking Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Banking Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="banking-info">
                        <p class="text-secondary-foreground text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>
            <!-- End of Banking Information -->
            <!-- Next of Kin -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Next of Kin</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="next-of-kin-info">
                        <p class="text-secondary-foreground text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>
            <!-- End of Next of Kin -->
        </div>
        <!-- End of Profile Details -->
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                try {
                    const token = window.API_CONFIG.token;
                    const res = await fetch('/api/v1/auth/me', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        const officer = data.data?.user?.officer;

                        if (officer) {
                            // Update header
                            document.getElementById('officer-name').textContent = `${officer.initials} ${officer.surname}`;
                            document.getElementById('service-number').textContent = officer.service_number || 'N/A';
                            document.getElementById('rank').textContent = officer.substantive_rank || 'N/A';
                            document.getElementById('command').textContent = officer.command?.name || 'N/A';

                            // Personal Information
                            document.getElementById('personal-info').innerHTML = `
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Date of Birth</span>
                                <span class="text-sm font-semibold text-mono">${officer.date_of_birth ? new Date(officer.date_of_birth).toLocaleDateString() : 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Gender</span>
                                <span class="text-sm font-semibold text-mono">${officer.gender || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Phone</span>
                                <span class="text-sm font-semibold text-mono">${officer.phone || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Email</span>
                                <span class="text-sm font-semibold text-mono">${data.data.user.email || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Address</span>
                                <span class="text-sm font-semibold text-mono">${officer.address || 'N/A'}</span>
                            </div>
                        `;

                            // Employment Details
                            document.getElementById('employment-info').innerHTML = `
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Date of First Appointment</span>
                                <span class="text-sm font-semibold text-mono">${officer.date_of_first_appointment ? new Date(officer.date_of_first_appointment).toLocaleDateString() : 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Present Station</span>
                                <span class="text-sm font-semibold text-mono">${officer.command?.name || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">RSA PIN</span>
                                <span class="text-sm font-semibold text-mono">${officer.rsa_pin || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Quartered</span>
                                <span class="text-sm font-semibold text-mono">${officer.is_quartered ? 'Yes' : 'No'}</span>
                            </div>
                        `;

                            // Banking Information
                            document.getElementById('banking-info').innerHTML = `
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Bank Name</span>
                                <span class="text-sm font-semibold text-mono">${officer.bank_name || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Account Number</span>
                                <span class="text-sm font-semibold text-mono">${officer.account_number || 'N/A'}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Account Name</span>
                                <span class="text-sm font-semibold text-mono">${officer.account_name || 'N/A'}</span>
                            </div>
                        `;

                            // Next of Kin
                            if (officer.next_of_kin) {
                                document.getElementById('next-of-kin-info').innerHTML = `
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-secondary-foreground">Name</span>
                                    <span class="text-sm font-semibold text-mono">${officer.next_of_kin.name || 'N/A'}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-secondary-foreground">Relationship</span>
                                    <span class="text-sm font-semibold text-mono">${officer.next_of_kin.relationship || 'N/A'}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-secondary-foreground">Phone</span>
                                    <span class="text-sm font-semibold text-mono">${officer.next_of_kin.phone || 'N/A'}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-secondary-foreground">Address</span>
                                    <span class="text-sm font-semibold text-mono">${officer.next_of_kin.address || 'N/A'}</span>
                                </div>
                            `;
                            } else {
                                document.getElementById('next-of-kin-info').innerHTML = '<p class="text-secondary-foreground text-center py-4">No next of kin information</p>';
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error loading profile:', error);
                }
            });
        </script>
    @endpush
@endsection