@extends('layouts.app')

@section('title', 'Published Deployments')
@section('page-title', 'Published Deployments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route(($routePrefix ?? 'hrd') . '.dashboard') }}">{{ ($routePrefix ?? 'hrd') === 'zone-coordinator' ? 'Zone Coordinator' : 'HRD' }}</a>
    <span>/</span>
    <span class="text-primary">Published Deployments</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-mono">Published Deployments</h2>
            <p class="text-sm text-secondary-foreground mt-1">View all published manning deployments</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route(($routePrefix ?? 'hrd') . '.manning-deployments.draft') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-file-add"></i> Draft Deployment
            </a>
            @if(($routePrefix ?? 'hrd') === 'hrd')
                <a href="{{ route('hrd.manning-requests') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                    <i class="ki-filled ki-arrow-left"></i> Back to Requests
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="kt-alert kt-alert-success">
            <div class="kt-alert-content">
                <i class="ki-filled ki-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Deployments List -->
    <div class="kt-card">
        <div class="kt-card-content">
            @if($deployments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Deployment Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Published Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Published By</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officers</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deployments as $deployment)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground font-mono">
                                            {{ $deployment->deployment_number }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $deployment->published_at ? $deployment->published_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $deployment->publishedBy->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $deployment->assignments->count() }} officer(s)
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route(($routePrefix ?? 'hrd') . '.manning-deployments.print', $deployment->id) }}" 
                                           target="_blank" 
                                           class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-printer"></i> Print
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$deployments" item-name="deployments" />
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No published deployments yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

