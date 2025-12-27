@extends('layouts.app')

@section('title', 'APER Forms')
@section('page-title', 'APER Forms')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">APER Forms</span>
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
                    <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="kt-card bg-info/10 border border-info/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-info text-xl"></i>
                    <p class="text-sm font-medium text-info">{{ session('info') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">My APER Forms</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('officer.aper-forms.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create New Form
                    </a>
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
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Year</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Reporting Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Submitted</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forms as $form)
                                    @php
                                        $statusConfig = match($form->status) {
                                            'DRAFT' => ['class' => 'secondary', 'label' => 'Draft'],
                                            'SUBMITTED' => ['class' => 'info', 'label' => 'Submitted'],
                                            'REPORTING_OFFICER' => ['class' => 'warning', 'label' => 'With Reporting Officer'],
                                            'COUNTERSIGNING_OFFICER' => ['class' => 'warning', 'label' => 'With Countersigning Officer'],
                                            'OFFICER_REVIEW' => ['class' => 'primary', 'label' => 'Pending Review'],
                                            'ACCEPTED' => ['class' => 'success', 'label' => 'Accepted'],
                                            'REJECTED' => ['class' => 'danger', 'label' => 'Rejected'],
                                            default => ['class' => 'secondary', 'label' => $form->status]
                                        };
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $form->year }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $statusConfig['class'] }} kt-badge-sm">
                                                {{ $statusConfig['label'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $form->submitted_at ? $form->submitted_at->format('d/m/Y') : '-' }}
                                        </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($form->status === 'DRAFT')
                                            <a href="{{ route('officer.aper-forms.edit', $form->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-primary">
                                                <i class="ki-filled ki-notepad-edit"></i> Edit
                                            </a>
                                        @endif
                                        <a href="{{ route('officer.aper-forms.show', $form->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No APER forms found</p>
                        <a href="{{ route('officer.aper-forms.create') }}" class="kt-btn kt-btn-primary">
                            Create First Form
                        </a>
                    </div>
                @endif

                <!-- Pagination -->
                @if($forms->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $forms->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

