@extends('layouts.app')

@section('title', 'Building Unit Dashboard')
@section('page-title', 'Building Unit Dashboard')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Quarters</span>
                            <span class="text-2xl font-semibold text-mono" id="total-quarters">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-home-2 text-2xl text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Occupied</span>
                            <span class="text-2xl font-semibold text-mono" id="occupied-quarters">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-check text-2xl text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Available</span>
                            <span class="text-2xl font-semibold text-mono" id="available-quarters">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-home text-2xl text-info"></i>
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
                    <a href="{{ route('building.quarters') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-home-2"></i> Manage Quarters
                    </a>
                    <a href="{{ route('building.officers') }}" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-user"></i> Manage Officers Quartered Status
                    </a>
                    <a href="{{ route('building.quarters.allocate') }}" class="kt-btn kt-btn-info">
                        <i class="ki-filled ki-plus"></i> Allocate Quarters
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
                    // Load statistics
                    const statsRes = await fetch('/api/v1/quarters/statistics', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });

                    if (statsRes.ok) {
                        const statsData = await statsRes.json();
                        if (statsData.success && statsData.data) {
                            document.getElementById('total-quarters').textContent = statsData.data.total_quarters || 0;
                            document.getElementById('occupied-quarters').textContent = statsData.data.occupied || 0;
                            document.getElementById('available-quarters').textContent = statsData.data.available || 0;
                        }
                    }
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                    document.getElementById('total-quarters').textContent = '0';
                    document.getElementById('occupied-quarters').textContent = '0';
                    document.getElementById('available-quarters').textContent = '0';
                }
            });
        </script>
    @endpush
@endsection
