@extends('layouts.app')

@section('title', 'APER Form - Reporting Officer Assessment')
@section('page-title', 'APER Form Assessment - ' . $form->year)

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.aper-forms.reporting-officer.search') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Assessment</span>
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
                    <p class="text-sm font-medium text-foreground">{{ $officer->initials }} {{ $officer->surname }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-foreground">Service Number</p>
                    <p class="text-sm font-medium text-foreground">{{ $officer->service_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-foreground">Year</p>
                    <p class="text-sm font-medium text-foreground">{{ $form->year }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('staff-officer.aper-forms.update-reporting-officer', $form->id) }}" method="POST" id="reporting-officer-form">
        @csrf
        
        <!-- Section 9: Assessment of Performance -->
        @include('forms.aper.partials.reporting-officer.section9-assessment', ['form' => $form])
        
        <!-- Section 10: Aspects of Performance -->
        @include('forms.aper.partials.reporting-officer.section10-aspects', ['form' => $form])
        
        <!-- Section 11: Overall Assessment -->
        @include('forms.aper.partials.reporting-officer.section11-overall', ['form' => $form])
        
        <!-- Section 12-15: Training Needs, Remarks, Suggestions, Promotability -->
        @include('forms.aper.partials.reporting-officer.section12-15-final', ['form' => $form])

        <!-- Form Actions -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <a href="{{ route('staff-officer.aper-forms.reporting-officer.search') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-arrow-left"></i> Back
                    </a>
                    <div class="flex gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Save Assessment
                        </button>
                        <a href="{{ route('staff-officer.aper-forms.complete-reporting-officer', $form->id) }}" 
                           class="kt-btn kt-btn-success"
                           onclick="return confirm('Are you sure you want to complete and forward this assessment to Countersigning Officer?')">
                            <i class="ki-filled ki-send"></i> Complete & Forward
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Grade selection helper
function selectGrade(fieldName, grade) {
    document.querySelector(`input[name="${fieldName}"][value="${grade}"]`).checked = true;
}
</script>
@endsection

