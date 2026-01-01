@extends('layouts.app')

@section('title', 'Grade APER Form')
@section('page-title', 'Grade APER Form - ' . $form->year)

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.aper-forms') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Grade</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Status Card -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">APER Form {{ $form->year }}</h3>
                    <p class="text-sm text-secondary-foreground">
                        Officer: <strong>{{ $form->officer->initials }} {{ $form->officer->surname }}</strong> ({{ $form->officer->service_number }})
                    </p>
                    <p class="text-sm text-secondary-foreground mt-1">
                        Status: <span class="kt-badge kt-badge-info kt-badge-sm">Finalized</span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('hrd.aper-forms.show', $form->id) }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-eye"></i> View Details
                    </a>
                    <a href="{{ route('hrd.aper-forms.export', $form->id) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-file-down"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Grading Form -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Grade APER Form</h3>
            <p class="text-sm text-secondary-foreground italic">Enter the score for this APER form (0-100)</p>
        </div>
        <div class="kt-card-content">
            <form action="{{ route('hrd.aper-forms.grade.submit', $form->id) }}" method="POST">
                @csrf
                
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="kt-form-label font-semibold">Score <span class="text-danger">*</span></label>
                        <p class="text-sm text-secondary-foreground mb-2">Enter a score between 0 and 100</p>
                        <input type="number" 
                               name="hrd_score" 
                               class="kt-input" 
                               step="0.01"
                               min="0" 
                               max="100" 
                               value="{{ old('hrd_score', $form->hrd_score) }}" 
                               required>
                        @error('hrd_score')
                            <span class="text-sm text-danger">{{ $message }}</span>
                        @enderror
                        @if($form->hrd_score !== null)
                            <p class="text-xs text-success mt-1">
                                <i class="ki-filled ki-check-circle"></i> Previously graded: {{ number_format($form->hrd_score, 2) }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="kt-form-label font-semibold">Score Notes</label>
                        <p class="text-sm text-secondary-foreground mb-2">Optional notes or comments about the score</p>
                        <textarea name="hrd_score_notes" 
                                  class="kt-input" 
                                  rows="4" 
                                  placeholder="Enter any additional notes about the score...">{{ old('hrd_score_notes', $form->hrd_score_notes) }}</textarea>
                        @error('hrd_score_notes')
                            <span class="text-sm text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($form->hrd_graded_at)
                        <div class="p-4 bg-muted/50 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="kt-form-label text-sm">Last Graded At</label>
                                    <p class="text-sm text-foreground">{{ $form->hrd_graded_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($form->hrdGradedBy)
                                    <div>
                                        <label class="kt-form-label text-sm">Graded By</label>
                                        <p class="text-sm text-foreground">{{ $form->hrdGradedBy->email }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                        <a href="{{ route('hrd.aper-forms') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> {{ $form->hrd_score !== null ? 'Update Grade' : 'Submit Grade' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Reference: Assessment Summary -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Assessment Summary</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label text-sm">Overall Assessment</label>
                    <p class="text-2xl font-bold text-primary">{{ $form->overall_assessment ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label text-sm">Promotability</label>
                    <p class="text-2xl font-bold text-primary">{{ $form->promotability ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label text-sm">Status</label>
                    <p class="text-sm font-semibold text-foreground">{{ $form->status }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

