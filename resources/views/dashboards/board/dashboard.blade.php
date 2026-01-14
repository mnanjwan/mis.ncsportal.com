@extends('layouts.app')

@section('title', 'Board Dashboard')
@section('page-title', 'Board Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Promotions</span>
                        <span class="text-2xl font-semibold text-mono" id="pending-promotions">Loading...</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-arrow-up text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Approved This Year</span>
                        <span class="text-2xl font-semibold text-mono" id="approved-year">Loading...</span>
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
                <a href="{{ route('board.promotions') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-arrow-up"></i> View Promotion Eligibility Lists
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = window.API_CONFIG?.token;
    
    if (!token) {
        console.error('API token not available');
        document.getElementById('pending-promotions').textContent = '0';
        document.getElementById('approved-year').textContent = '0';
        return;
    }

    try {
        // Fetch dashboard statistics
        const statsRes = await fetch('/api/v1/promotions/dashboard-stats', {
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Accept': 'application/json' 
            }
        });
        
        if (statsRes.ok) {
            const statsData = await statsRes.json();
            if (statsData.data) {
                document.getElementById('pending-promotions').textContent = statsData.data.pending_promotions || 0;
                document.getElementById('approved-year').textContent = statsData.data.approved_this_year || 0;
            }
        } else {
            // Fallback: try fetching eligibility lists directly
            const listsRes = await fetch('/api/v1/promotion-eligibility-lists?status=SUBMITTED_TO_BOARD&per_page=1', {
                headers: { 
                    'Authorization': 'Bearer ' + token, 
                    'Accept': 'application/json' 
                }
            });
            
            if (listsRes.ok) {
                const listsData = await listsRes.json();
                document.getElementById('pending-promotions').textContent = listsData.meta?.total || 0;
            } else {
                document.getElementById('pending-promotions').textContent = '0';
            }
            document.getElementById('approved-year').textContent = '0';
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        document.getElementById('pending-promotions').textContent = '0';
        document.getElementById('approved-year').textContent = '0';
    }
});
</script>
@endpush
@endsection


