@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Reports</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

<div class="grid gap-5 lg:gap-7.5">
    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card cursor-pointer hover:shadow-lg transition-shadow" onclick="generateReport('officers')">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Officers Report</span>
                        <span class="text-2xl font-semibold text-mono">All Officers</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-people text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card cursor-pointer hover:shadow-lg transition-shadow" onclick="generateReport('emoluments')">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Emoluments Report</span>
                        <span class="text-2xl font-semibold text-mono">All Emoluments</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-wallet text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card cursor-pointer hover:shadow-lg transition-shadow" onclick="generateReport('leave')">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Leave Report</span>
                        <span class="text-2xl font-semibold text-mono">Leave Statistics</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Options -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Generate Custom Report</h3>
        </div>
        <div class="kt-card-content">
            <form id="custom-report-form" action="{{ route('hrd.reports.generate') }}" method="POST" class="flex flex-col gap-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Report Type</label>
                        <select name="report_type" class="kt-input" required>
                            <option value="">Select report type...</option>
                            <option value="officers">Officers</option>
                            <option value="emoluments">Emoluments</option>
                            <option value="leave">Leave Applications</option>
                            <option value="pass">Pass Applications</option>
                            <option value="promotions">Promotions</option>
                            <option value="retirements">Retirements</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Format</label>
                        <select name="format" class="kt-input" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Start Date</label>
                        <input type="date" name="start_date" class="kt-input"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">End Date</label>
                        <input type="date" name="end_date" class="kt-input"/>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generateReport(type) {
    const token = window.API_CONFIG.token;
    const url = `/api/v1/reports/${type}?format=pdf`;
    
    // Open in new window to download
    window.open(url + '&token=' + token, '_blank');
}

// Form will submit normally to backend route
</script>
@endpush
@endsection


