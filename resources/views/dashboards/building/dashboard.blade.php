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
                    <a href="{{ route('building.requests') }}" class="kt-btn kt-btn-warning">
                        <i class="ki-filled ki-file-up"></i> Quarter Requests
                    </a>
                    <a href="{{ route('building.rejected-allocations') }}" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-cross-circle"></i> Rejected Allocations
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
                    console.error('API token not found');
                    showError('Authentication required. Please refresh the page.');
                    return;
                }

                try {
                    // Load statistics
                    const statsRes = await fetch('/api/v1/quarters/statistics', {
                        headers: { 
                            'Authorization': 'Bearer ' + token, 
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const statsData = await statsRes.json();

                    if (statsRes.ok && statsData.success) {
                        if (statsData.data) {
                            document.getElementById('total-quarters').textContent = statsData.data.total_quarters || 0;
                            document.getElementById('occupied-quarters').textContent = statsData.data.occupied || 0;
                            document.getElementById('available-quarters').textContent = statsData.data.available || 0;
                        } else {
                            // No data available
                            document.getElementById('total-quarters').textContent = '0';
                            document.getElementById('occupied-quarters').textContent = '0';
                            document.getElementById('available-quarters').textContent = '0';
                        }
                    } else {
                        // Handle error response
                        const errorMsg = statsData.message || 'Failed to load statistics';
                        console.error('API Error:', errorMsg);
                        
                        if (statsData.meta?.code === 'NO_COMMAND_ASSIGNED') {
                            document.getElementById('total-quarters').textContent = 'N/A';
                            document.getElementById('occupied-quarters').textContent = 'N/A';
                            document.getElementById('available-quarters').textContent = 'N/A';
                            showError('You must be assigned to a command to view statistics. Please contact HRD.');
                        } else {
                            document.getElementById('total-quarters').textContent = '0';
                            document.getElementById('occupied-quarters').textContent = '0';
                            document.getElementById('available-quarters').textContent = '0';
                            showError(errorMsg);
                        }
                    }
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                    document.getElementById('total-quarters').textContent = 'Error';
                    document.getElementById('occupied-quarters').textContent = 'Error';
                    document.getElementById('available-quarters').textContent = 'Error';
                    showError('Failed to load dashboard data. Please refresh the page.');
                }
            });

            function showError(message) {
                // Create error notification card
                const notification = document.createElement('div');
                notification.className = 'kt-card bg-danger/10 border border-danger/20 mb-4';
                notification.innerHTML = `
                    <div class="kt-card-content p-4">
                        <div class="flex items-center gap-3">
                            <i class="ki-filled ki-information text-danger text-xl"></i>
                            <p class="text-sm text-danger font-medium">${message}</p>
                        </div>
                    </div>
                `;
                
                // Insert at top of content
                const content = document.querySelector('.grid.gap-5');
                if (content) {
                    content.insertBefore(notification, content.firstChild);
                    
                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 5000);
                } else {
                    alert(message);
                }
            }
        </script>
    @endpush
@endsection
