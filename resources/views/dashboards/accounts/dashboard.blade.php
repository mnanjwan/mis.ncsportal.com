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
                        <span class="text-2xl font-semibold text-mono">{{ $auditedCount ?? 0 }}</span>
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
    
    <!-- Pending Emoluments for Action -->
    @if(isset($pendingEmoluments) && $pendingEmoluments->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pending Emoluments for Action</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('accounts.validated-officers') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Year</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Audited</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Auditor</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingEmoluments as $emolument)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ ($emolument->officer->initials ?? '') . ' ' . ($emolument->officer->surname ?? '') }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-mono text-foreground">
                                            {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-foreground">
                                        {{ $emolument->year }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $emolument->audited_at ? $emolument->audited_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $emolument->audit->auditor->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('accounts.validated-officers') }}?emolument={{ $emolument->id }}" 
                                           class="kt-btn kt-btn-sm kt-btn-primary"
                                           title="Process">
                                            <i class="ki-filled ki-check"></i> Process
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="text-center py-12">
                    <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No pending emoluments for processing</p>
                </div>
            </div>
        </div>
    @endif

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


