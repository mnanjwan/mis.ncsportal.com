@extends('layouts.app')

@section('title', 'Create APER Form')
@section('page-title', 'Create APER Form')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.aper-forms') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Create</span>
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

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <ul class="list-disc list-inside text-sm text-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Timeline Info -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-info text-xl"></i>
                <div>
                    <p class="text-sm font-medium text-info">APER Timeline: {{ $activeTimeline->year }}</p>
                    <p class="text-xs text-secondary-foreground">
                        Period: {{ $activeTimeline->start_date->format('d/m/Y') }} - {{ $activeTimeline->end_date->format('d/m/Y') }}
                        @if($activeTimeline->days_remaining > 0)
                            | {{ $activeTimeline->days_remaining }} days remaining
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <form action="{{ route('officer.aper-forms.store') }}" method="POST" id="aper-form">
        @csrf
        
        <!-- Part 1: Personal Records -->
        @include('forms.aper.partials.part1-personal-records', ['formData' => $formData ?? [], 'officer' => $officer])
        
        <!-- Part 2: Leave Records, Target Setting, Job Description -->
        @include('forms.aper.partials.part2-leave-targets-job', ['formData' => $formData ?? [], 'activeTimeline' => $activeTimeline])
        
        <!-- Part 3: Training and Job Performance -->
        @include('forms.aper.partials.part3-training-performance', ['formData' => $formData ?? []])

        <!-- Form Actions -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <a href="{{ route('officer.aper-forms') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-arrow-left"></i> Cancel
                    </a>
                    <div class="flex gap-3">
                        <button type="submit" name="action" value="save" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Save Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-send"></i> Submit Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('aper-form').addEventListener('submit', function(e) {
    const submitButton = e.submitter;
    if (submitButton && submitButton.value === 'submit') {
        if (!confirm('Are you sure you want to submit this APER form? You will not be able to edit it after submission.')) {
            e.preventDefault();
            return false;
        }
    }
});
</script>
@endsection

