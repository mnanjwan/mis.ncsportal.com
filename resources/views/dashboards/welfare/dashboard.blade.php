@extends('layouts.app')

@section('title', 'Welfare Dashboard')
@section('page-title', 'Welfare Dashboard')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Deceased Officers</span>
                            <span class="text-2xl font-semibold text-mono" id="deceased-officers">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-secondary/10">
                            <i class="ki-filled ki-heart text-2xl text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Settlements</span>
                            <span class="text-2xl font-semibold text-mono" id="pending-settlements">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-file-up text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Settled This Year</span>
                            <span class="text-2xl font-semibold text-mono" id="settled-year">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-check text-2xl text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('welfare.deceased-officers') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-heart"></i> View Deceased Officers
                    </a>
                    <a href="#"
                        class="inline-flex items-center justify-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all shadow-sm">
                        <i class="ki-filled ki-plus mr-2"></i> Record Deceased Officer
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const token = window.API_CONFIG.token;

                try {
                    const res = await fetch('/api/v1/deceased-officers?per_page=1', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        document.getElementById('deceased-officers').textContent = data.meta?.total || 0;
                    }
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                }
            });
        </script>
    @endpush
@endsection
