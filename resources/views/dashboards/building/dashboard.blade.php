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
                    <a href="#"
                        class="inline-flex items-center justify-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all shadow-sm">
                        <i class="ki-filled ki-plus mr-2"></i> Allocate Quarters
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
                    const res = await fetch('/api/v1/quarters?per_page=1', {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        // Calculate statistics from data
                        document.getElementById('total-quarters').textContent = data.meta?.total || 0;
                    }
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                }
            });
        </script>
    @endpush
@endsection
