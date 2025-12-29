@extends('layouts.app')

@section('title', 'Search Officers')
@section('page-title', 'Search Officers')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <span class="text-primary">Search Officers</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Search Officers System-Wide</h3>
        </div>
        <div class="kt-card-content">
            <form method="GET" action="{{ route('investigation.search') }}" class="mb-5">
                <div class="flex gap-3">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by service number, name, or email..."
                           class="kt-input flex-1">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-magnifier"></i> Search
                    </button>
                </div>
            </form>

            @if($officers->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 800px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Command</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($officers as $officer)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <span class="font-medium">{{ $officer->service_number }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $officer->initials }} {{ $officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $officer->email }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->substantive_rank }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->presentStation->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if($officer->ongoing_investigation)
                                                <span class="kt-badge kt-badge-warning">Ongoing Investigation</span>
                                            @endif
                                            @if($officer->interdicted)
                                                <span class="kt-badge kt-badge-danger">Interdicted</span>
                                            @endif
                                            @if($officer->suspended)
                                                <span class="kt-badge kt-badge-danger">Suspended</span>
                                            @endif
                                            @if(!$officer->ongoing_investigation && !$officer->interdicted && !$officer->suspended)
                                                <span class="kt-badge kt-badge-success">Clear</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('investigation.create', $officer->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-plus"></i> Send Invitation
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-border">
                    {{ $officers->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-magnifier text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">
                        @if(request('search'))
                            No officers found matching "{{ request('search') }}"
                        @else
                            Enter a search term to find officers
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


