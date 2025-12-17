@extends('layouts.app')

@section('title', 'DC Admin Dashboard')
@section('page-title', 'DC Admin Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Leave</span>
                        <span class="text-2xl font-semibold text-mono" id="pending-leave">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-calendar text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Pass</span>
                        <span class="text-2xl font-semibold text-mono" id="pending-pass">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Approved This Month</span>
                        <span class="text-2xl font-semibold text-mono" id="approved-month">Loading...</span>
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
                <a href="{{ route('dc-admin.leave-pass') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-calendar"></i> Manage Leave & Pass
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
        const [leaveRes, passRes] = await Promise.all([
            fetch('/api/v1/leave-applications?status=PENDING&per_page=1', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } }),
            fetch('/api/v1/pass-applications?status=PENDING&per_page=1', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } })
        ]);
        
        if (leaveRes.ok) {
            const data = await leaveRes.json();
            document.getElementById('pending-leave').textContent = data.meta?.total || 0;
        }
        
        if (passRes.ok) {
            const data = await passRes.json();
            document.getElementById('pending-pass').textContent = data.meta?.total || 0;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
});
</script>
@endpush
@endsection


