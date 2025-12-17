@extends('layouts.app')

@section('title', 'Accounts Dashboard')
@section('page-title', 'Accounts Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Validated Officers</span>
                        <span class="text-2xl font-semibold text-mono">{{ $validatedCount ?? 0 }}</span>
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
                        <span class="text-sm font-normal text-secondary-foreground">Pending Processing</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingProcessing ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-wallet text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Processed This Month</span>
                        <span class="text-2xl font-semibold text-mono">{{ $processedThisMonth ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-file-check text-2xl text-info"></i>
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
                <a href="{{ route('accounts.validated-officers') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check"></i> View Validated Officers
                </a>
                <a href="{{ route('accounts.processed-history') }}" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-file-check"></i> Processed History
                </a>
                <a href="{{ route('accounts.deceased-officers') }}" class="kt-btn kt-btn-secondary">
                    <i class="ki-filled ki-heart"></i> Deceased Officers
                </a>
            </div>
        </div>
    </div>
</div>

@endsection


