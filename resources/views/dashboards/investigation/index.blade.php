@extends('layouts.app')

@section('title', 'Investigations')
@section('page-title', 'Investigations')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <span class="text-primary">Investigations</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-foreground">Investigation Records</h2>
        <a href="{{ route('investigation.search') }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-magnifier"></i> Search Officers
        </a>
    </div>

    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">All Investigations</h3>
            <div class="kt-card-toolbar flex items-center gap-3">
                <form method="GET" action="{{ route('investigation.index') }}" class="flex items-center gap-3">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by officer name or service number..."
                           class="kt-input">
                    <select name="status" class="kt-input" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="INVITED" {{ request('status') === 'INVITED' ? 'selected' : '' }}>Invited</option>
                        <option value="ONGOING_INVESTIGATION" {{ request('status') === 'ONGOING_INVESTIGATION' ? 'selected' : '' }}>Ongoing Investigation</option>
                        <option value="INTERDICTED" {{ request('status') === 'INTERDICTED' ? 'selected' : '' }}>Interdicted</option>
                        <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                        <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Resolved</option>
                    </select>
                    <button type="submit" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-magnifier"></i> Search
                    </button>
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($investigations->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Invited Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status Changed</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Investigation Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($investigations as $investigation)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $investigation->officer->initials }} {{ $investigation->officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $investigation->officer->service_number }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($investigation->status === 'INVITED')
                                            <span class="kt-badge kt-badge-info">Invited</span>
                                        @elseif($investigation->status === 'ONGOING_INVESTIGATION')
                                            <span class="kt-badge kt-badge-warning">Ongoing Investigation</span>
                                        @elseif($investigation->status === 'INTERDICTED')
                                            <span class="kt-badge kt-badge-danger">Interdicted</span>
                                        @elseif($investigation->status === 'SUSPENDED')
                                            <span class="kt-badge kt-badge-danger">Suspended</span>
                                        @else
                                            <span class="kt-badge kt-badge-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $investigation->invited_at ? $investigation->invited_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $investigation->status_changed_at ? $investigation->status_changed_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $investigation->investigationOfficer->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('investigation.show', $investigation->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-border">
                    {{ $investigations->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No investigations found</p>
                    <a href="{{ route('investigation.search') }}" class="kt-btn kt-btn-primary mt-4">
                        <i class="ki-filled ki-magnifier"></i> Search Officers
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


