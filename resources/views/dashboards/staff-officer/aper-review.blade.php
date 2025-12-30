@extends('layouts.app')

@section('title', 'APER Forms - Review')
@section('page-title', 'APER Forms - Review')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">APER Forms Review</span>
@endsection

@section('content')
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
                    <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Rejected APER Forms - Pending Review</h3>
                <div class="kt-card-toolbar">
                    <p class="text-xs text-secondary-foreground">
                        <i class="ki-filled ki-information"></i> 
                        Officers have rejected these forms. You can reassign or finalize them.
                    </p>
                </div>
            </div>
        </div>

        <!-- Forms Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($forms->count() > 0)
                    <div class="table-scroll-wrapper overflow-x-auto">
                        <table class="kt-table" style="min-width: 800px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Year</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rejection Reason</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rejected At</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forms as $form)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-foreground">
                                                    {{ $form->officer->initials }} {{ $form->officer->surname }}
                                                </span>
                                                <span class="text-xs text-secondary-foreground">
                                                    {{ $form->officer->service_number }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">{{ $form->year }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground line-clamp-2 max-w-md">
                                                {{ Str::limit($form->rejection_reason, 100) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $form->rejected_at ? $form->rejected_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('staff-officer.aper-forms.review.show', $form->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-primary">
                                                <i class="ki-filled ki-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($forms->hasPages())
                        <div class="mt-6 pt-4 border-t border-border">
                            {{ $forms->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                        <p class="text-secondary-foreground mb-2">No rejected APER forms pending review</p>
                        <p class="text-xs text-secondary-foreground">All forms have been processed.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

