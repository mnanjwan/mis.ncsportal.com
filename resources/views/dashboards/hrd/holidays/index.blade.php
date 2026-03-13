@extends('layouts.app')

@section('title', 'Manage Holidays')
@section('page-title', 'Holiday Settings')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Holiday Settings</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Public & Floating Holidays</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.holidays.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Add Holiday
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-4">
                Manage public holidays that affect leave and pass duration calculations. Skip dates configured here are automatically excluded from working days.
            </p>

            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Type</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Year</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">{{ $holiday->name }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                        {{ $holiday->date->format('d M, Y') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($holiday->is_floating)
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">Floating</span>
                                        @else
                                            <span class="kt-badge kt-badge-info kt-badge-sm">Fixed Override</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                        {{ $holiday->year }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hrd.holidays.edit', $holiday->id) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                <i class="ki-filled ki-notepad-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    data-kt-modal-toggle="#delete-modal-{{ $holiday->id }}"
                                                    class="kt-btn kt-btn-sm kt-btn-icon kt-btn-danger">
                                                <i class="ki-filled ki-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground">No holidays added yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden">
                <div class="flex flex-col gap-4">
                    @forelse($holidays as $holiday)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-calendar-8 text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $holiday->name }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground font-mono">
                                        {{ $holiday->date->format('d M, Y') }}
                                    </span>
                                    <span class="text-xs">
                                        @if($holiday->is_floating)
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">Floating</span>
                                        @else
                                            <span class="kt-badge kt-badge-info kt-badge-sm">Fixed Override</span>
                                        @endif
                                        <span class="text-secondary-foreground ml-2">{{ $holiday->year }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('hrd.holidays.edit', $holiday->id) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                    <i class="ki-filled ki-notepad-edit"></i>
                                </a>
                                <button type="button" 
                                        data-kt-modal-toggle="#delete-modal-{{ $holiday->id }}"
                                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-danger">
                                    <i class="ki-filled ki-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">No holidays added yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            @if($holidays->hasPages())
                <div class="mt-4">
                    {{ $holidays->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($holidays as $holiday)
    <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $holiday->id }}">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="kt-modal-title">Confirm Deletion</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to delete the holiday <strong>{{ $holiday->name }}</strong>? 
                    This action cannot be undone and will affect working day calculations.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form action="{{ route('hrd.holidays.destroy', $holiday->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
