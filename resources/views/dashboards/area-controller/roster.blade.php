@extends('layouts.app')

@section('title', 'Duty Rosters')
@section('page-title', 'Duty Rosters - Pending Approval')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
    <span>/</span>
    <span class="text-primary">Duty Rosters</span>
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

<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Submitted Duty Rosters</h3>
        </div>
        <div class="kt-card-content">
            @if($rosters->count() > 0)
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Unit</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Command</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Period</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Prepared By</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Assignments</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rosters as $roster)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-semibold">{{ $roster->unit ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $roster->command->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $roster->roster_period_start->format('M d') }} - {{ $roster->roster_period_end->format('M d, Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $roster->preparedBy->email ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $roster->assignments->count() }} assignments</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('area-controller.roster.show', $roster->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $rosters->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No submitted rosters found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

