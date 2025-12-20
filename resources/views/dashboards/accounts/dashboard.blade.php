@extends('layouts.app')

@section('title', 'Accounts Dashboard')
@section('page-title', 'Accounts Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
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
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Change Requests</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingChangeRequests ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-credit-card text-2xl text-warning"></i>
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
                <a href="{{ route('accounts.account-change.pending') }}" class="kt-btn kt-btn-warning">
                    <i class="ki-filled ki-credit-card"></i> Account Change Requests
                    @if($pendingChangeRequests > 0)
                        <span class="kt-badge kt-badge-danger kt-badge-sm ms-2">{{ $pendingChangeRequests }}</span>
                    @endif
                </a>
                <a href="{{ route('accounts.deceased-officers') }}" class="kt-btn kt-btn-secondary">
                    <i class="ki-filled ki-heart"></i> Deceased Officers
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Account Change Requests -->
    @if(isset($recentChangeRequests) && $recentChangeRequests->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Account Change Requests</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('accounts.account-change.pending') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Request Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Change Type</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentChangeRequests as $request)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-foreground">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ ($request->officer->initials ?? '') . ' ' . ($request->officer->surname ?? '') }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $request->officer->presentStation->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-mono text-foreground">
                                            {{ $request->officer->service_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col gap-1">
                                            @if($request->new_account_number)
                                                <span class="text-xs text-secondary-foreground">Account Number</span>
                                            @endif
                                            @if($request->new_rsa_pin)
                                                <span class="text-xs text-secondary-foreground">RSA PIN</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('accounts.account-change.show', $request->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           title="View Details">
                                            <i class="ki-filled ki-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection


