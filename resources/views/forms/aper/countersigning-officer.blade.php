@extends('layouts.app')

@section('title', 'APER Form - Countersigning Officer')
@section('page-title', 'APER Form Countersigning - {{ $form->year }}')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.aper-forms.reporting-officer.search') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Countersigning</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
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

    <!-- Officer Info Card -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-secondary-foreground">Officer</p>
                    <p class="text-sm font-medium text-foreground">{{ $form->officer->initials }} {{ $form->officer->surname }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-foreground">Service Number</p>
                    <p class="text-sm font-medium text-foreground">{{ $form->officer->service_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-foreground">Year</p>
                    <p class="text-sm font-medium text-foreground">{{ $form->year }}</p>
                </div>
            </div>
            @if($form->reportingOfficer)
                <div class="mt-3 pt-3 border-t border-border">
                    <p class="text-xs text-secondary-foreground">Reporting Officer</p>
                    <p class="text-sm font-medium text-foreground">{{ $form->reportingOfficer->email }}</p>
                    @if($form->reporting_officer_completed_at)
                        <p class="text-xs text-secondary-foreground mt-1">Completed: {{ $form->reporting_officer_completed_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Form Preview Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Form Summary</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($form->overall_assessment)
                    <div>
                        <label class="kt-form-label text-sm">Overall Assessment</label>
                        <p class="text-sm font-medium text-foreground">
                            Grade: {{ $form->overall_assessment }}
                            @php
                                $overallLabels = ['A' => 'Outstanding', 'B' => 'Very Good', 'C' => 'Good', 'D' => 'Satisfactory', 'E' => 'Fair', 'F' => 'Poor'];
                            @endphp
                            ({{ $overallLabels[$form->overall_assessment] ?? '' }})
                        </p>
                    </div>
                @endif
                @if($form->promotability)
                    <div>
                        <label class="kt-form-label text-sm">Promotability</label>
                        <p class="text-sm font-medium text-foreground">Grade: {{ $form->promotability }}</p>
                    </div>
                @endif
            </div>
            @if($form->general_remarks)
                <div class="mt-4">
                    <label class="kt-form-label text-sm">General Remarks</label>
                    <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->general_remarks }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Countersigning Form -->
    <form action="{{ route('staff-officer.aper-forms.update-countersigning-officer', $form->id) }}" method="POST" id="countersigning-form">
        @csrf
        
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Countersigning Officer Section</h3>
                <p class="text-sm text-secondary-foreground italic">Review the assessment and provide your countersigning declaration</p>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="kt-form-label font-semibold">Countersigning Officer Declaration</label>
                        <p class="text-sm text-secondary-foreground mb-2">
                            Please review the assessment above and provide your declaration as the Countersigning Officer.
                        </p>
                        <textarea name="countersigning_officer_declaration" 
                                  class="kt-input" 
                                  rows="6" 
                                  placeholder="Enter your declaration statement..."
                                  required>{{ old('countersigning_officer_declaration', $form->countersigning_officer_declaration) }}</textarea>
                        @error('countersigning_officer_declaration')
                            <span class="text-sm text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-information text-warning text-xl mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-warning">Important Notice</p>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    By completing this form, you are confirming that you have reviewed the assessment provided by the Reporting Officer 
                                    and agree with the evaluation. This will forward the form to the officer for final review.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <a href="{{ route('staff-officer.aper-forms.reporting-officer.search') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-arrow-left"></i> Back
                    </a>
                    <div class="flex gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Save Declaration
                        </button>
                        <a href="{{ route('staff-officer.aper-forms.complete-countersigning-officer', $form->id) }}" 
                           class="kt-btn kt-btn-success"
                           onclick="return confirm('Are you sure you want to complete and forward this form to the Officer for review?')">
                            <i class="ki-filled ki-send"></i> Complete & Forward to Officer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

