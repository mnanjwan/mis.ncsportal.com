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
                        <span class="text-2xl font-semibold text-mono">{{ $totalOfficers ?? 0 }}</span>
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
                        <span class="text-2xl font-semibold text-mono">{{ $lastServiceNumber ?? 'N/A' }}</span>
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
                        <span class="text-2xl font-semibold text-mono">{{ $pendingRecruits ?? 0 }}</span>
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
                <a href="{{ route('establishment.training-results') }}" class="kt-btn kt-btn-info">
                    <i class="ki-filled ki-chart-simple"></i> Training Results
                </a>
            </div>
        </div>
    </div>
</div>

@endsection


