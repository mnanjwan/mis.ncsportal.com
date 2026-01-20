@extends('layouts.app')

@section('title', 'Education Qualification Requests')
@section('page-title', 'Education Qualification Requests')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Education Requests</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending</span>
                            <span class="text-2xl font-semibold text-mono">{{ $pendingCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-time text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Approved</span>
                            <span class="text-2xl font-semibold text-mono">{{ $approvedCount ?? 0 }}</span>
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
                            <span class="text-sm font-normal text-secondary-foreground">Rejected</span>
                            <span class="text-2xl font-semibold text-mono">{{ $rejectedCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-danger/10">
                            <i class="ki-filled ki-cross text-2xl text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pending Education Qualification Requests</h3>
            </div>
            <div class="kt-card-content">
                @if($requests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Request Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Institution</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Qualification</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Year</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
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
                                        <td class="py-3 px-4 text-sm text-foreground">
                                            {{ $request->university }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-foreground">
                                            {{ $request->qualification }}
                                            @if($request->discipline)
                                                <div class="text-xs text-secondary-foreground">{{ $request->discipline }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $request->year_obtained }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">
                                                {{ $request->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('hrd.education-requests.show', $request->id) }}"
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

                    @if($requests->hasPages())
                        <div class="mt-6 pt-4 border-t border-border">
                            {{ $requests->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending education qualification requests found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

