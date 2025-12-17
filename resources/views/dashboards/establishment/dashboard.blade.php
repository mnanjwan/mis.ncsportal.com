@extends('layouts.app')

@section('title', 'Establishment Dashboard')
@section('page-title', 'Establishment Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Total Officers</span>
                        <span class="text-2xl font-semibold text-mono" id="total-officers">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-people text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Last Service Number</span>
                        <span class="text-2xl font-semibold text-mono" id="last-service-number">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-abstract-26 text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending New Recruits</span>
                        <span class="text-2xl font-semibold text-mono" id="pending-recruits">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-user-plus text-2xl text-warning"></i>
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
                <a href="{{ route('establishment.service-numbers') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-abstract-26"></i> Manage Service Numbers
                </a>
                <a href="{{ route('establishment.new-recruits') }}" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-user-plus"></i> New Recruits
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
        const res = await fetch('/api/v1/officers?per_page=1&sort=service_number,desc', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            if (data.data && data.data.length > 0) {
                document.getElementById('last-service-number').textContent = data.data[0].service_number || 'N/A';
            }
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
});
</script>
@endpush
@endsection


