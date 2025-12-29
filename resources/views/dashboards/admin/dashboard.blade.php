@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Command Info Card -->
    @if($adminCommand)
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-foreground mb-1">Assigned Command</h3>
                    <p class="text-2xl font-bold text-primary">{{ $adminCommand->name }}</p>
                </div>
                <div class="flex items-center justify-center size-16 rounded-full bg-primary/10">
                    <i class="ki-filled ki-gear text-3xl text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Total Role Assignments</span>
                        <span class="text-2xl font-semibold text-mono">{{ $roleAssignmentsCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-profile-user text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Staff Officers</span>
                        <span class="text-2xl font-semibold text-mono">{{ $staffOfficersCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-profile-user text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Area Controllers</span>
                        <span class="text-2xl font-semibold text-mono">{{ $areaControllersCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-profile-user text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">DC Admins</span>
                        <span class="text-2xl font-semibold text-mono">{{ $dcAdminsCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-profile-user text-2xl text-success"></i>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('admin.role-assignments') }}" class="kt-btn kt-btn-primary w-full justify-center">
                    <i class="ki-filled ki-profile-user"></i>
                    Manage Role Assignments
                </a>
                <a href="{{ route('admin.role-assignments.create') }}" class="kt-btn kt-btn-outline w-full justify-center">
                    <i class="ki-filled ki-plus"></i>
                    Assign New Role
                </a>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex items-start gap-3">
                <i class="ki-filled ki-information-2 text-info text-xl mt-0.5"></i>
                <div class="flex-1">
                    <h4 class="font-semibold text-info mb-2">Admin Role Assignment Guidelines</h4>
                    <ul class="text-sm text-secondary-foreground space-y-1 list-disc list-inside">
                        <li>You can assign roles to officers within your assigned command: <strong>{{ $adminCommand->name ?? 'N/A' }}</strong></li>
                        <li>You can assign the following roles: <strong>Staff Officer</strong>, <strong>Area Controller</strong>, and <strong>DC Admin</strong></li>
                        <li>All role assignments are specific to your command only</li>
                        <li>Officers will receive email and app notifications when roles are assigned</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

