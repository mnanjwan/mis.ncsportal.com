<!-- Read-only display of Reporting Officer's Assessment -->
@php
    $gradeLabels = ['A' => 'Outstanding', 'B' => 'Very Good', 'C' => 'Good', 'D' => 'Satisfactory', 'E' => 'Fair', 'F' => 'Poor'];
    $overallLabels = ['A' => 'Outstanding', 'B' => 'Very Good', 'C' => 'Good', 'D' => 'Satisfactory', 'E' => 'Fair', 'F' => 'Poor'];
    $promotabilityLabels = [
        'A' => 'Exceptionally well qualified, the officer already seems likely to stand out in the next higher post',
        'B' => 'Ready for promotion',
        'C' => 'Has promotion potentials, has completed the required number of years',
        'D' => 'Not yet ripe for promotion (for non-fulfilment of condition prescribed for Promotion in line with the Guidelines on Civil Service Reforms',
        'E' => 'No evidence of promotion potential at present',
        'F' => 'Unlikely to qualify as he/she seems to have reached the limit of his/her capacity',
    ];
@endphp

<!-- Section 9: Assessment of Performance -->
@if($form->targets_agreed || $form->duties_agreed || $form->other_comments)
<div class="kt-card shadow-sm bg-muted/20">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Section 9: Assessment of Performance</h3>
        <p class="text-sm text-secondary-foreground italic">(Completed by Reporting Officer)</p>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-4">
            @if($form->other_comments)
            <div class="p-4 bg-background rounded-lg border border-border/50">
                <label class="kt-form-label text-sm font-medium mb-2">Other Comments</label>
                <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->other_comments }}</p>
            </div>
            @endif
            
            @if($form->targets_agreed)
            <div class="p-4 bg-background rounded-lg border border-border/50">
                <label class="kt-form-label text-sm font-medium mb-2">Targets Agreement</label>
                <p class="text-sm text-foreground"><strong>{{ $form->targets_agreed }}</strong></p>
                @if($form->targets_agreement_details)
                <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->targets_agreement_details }}</p>
                @endif
            </div>
            @endif
            
            @if($form->duties_agreed)
            <div class="p-4 bg-background rounded-lg border border-border/50">
                <label class="kt-form-label text-sm font-medium mb-2">Duties Agreement</label>
                <p class="text-sm text-foreground"><strong>{{ $form->duties_agreed }}</strong></p>
                @if($form->duties_agreement_details)
                <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->duties_agreement_details }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Section 10: Aspects of Performance -->
<div class="kt-card shadow-sm bg-muted/20">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Section 10: Aspects of Performance</h3>
        <p class="text-sm text-secondary-foreground italic">(Completed by Reporting Officer)</p>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-6">
            <!-- Job Assessment Fields -->
            <div class="flex flex-col gap-4 border-b border-border/50 pb-6">
                <h4 class="text-base font-semibold text-foreground">(1) Job Assessment/General Ability</h4>
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $jobFields = [
                            'job_understanding' => 'How well he/she understands, organises and does his/her job',
                            'knowledge_application' => 'How well he/she applied his/her professional/technical/administrative or any other acquired knowledge',
                            'accomplishment' => 'How much he/she was able to accomplish within a set time frame',
                            'judgement' => 'Judgement (quality of his/her decisions and contributions) where Relevant',
                            'work_speed_accuracy' => 'Work-speed and accuracy',
                            'written_expression' => 'Expression on paper',
                            'oral_expression' => 'Oral Expression',
                            'staff_relations' => 'Relation with staff',
                            'public_relations' => 'Relation with public',
                            'staff_management' => 'Management of staff',
                        ];
                    @endphp
                    @foreach($jobFields as $field => $label)
                        @if($form->{$field.'_grade'})
                        <div class="p-4 bg-background rounded-lg border border-border/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <label class="kt-form-label text-sm font-medium mb-1">{{ $label }}</label>
                                    @if($form->{$field.'_comment'})
                                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->{$field.'_comment'} }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col items-center min-w-[80px]">
                                    <span class="text-2xl font-bold text-primary">{{ $form->{$field.'_grade'} }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $gradeLabels[$form->{$field.'_grade'}] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Quality of Work -->
            <div class="flex flex-col gap-4 border-b border-border/50 pb-6">
                <h4 class="text-base font-semibold text-foreground">(2) Quality of Work</h4>
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $qualityFields = [
                            'quality_of_work' => 'Quality of work',
                            'productivity' => 'Productivity',
                            'effective_use_of_data' => 'Effective use of figures / other Data',
                            'initiative' => 'Initiative',
                        ];
                    @endphp
                    @foreach($qualityFields as $field => $label)
                        @if($form->{$field.'_grade'})
                        <div class="p-4 bg-background rounded-lg border border-border/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <label class="kt-form-label text-sm font-medium mb-1">{{ $label }}</label>
                                    @if($form->{$field.'_comment'})
                                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->{$field.'_comment'} }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col items-center min-w-[80px]">
                                    <span class="text-2xl font-bold text-primary">{{ $form->{$field.'_grade'} }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $gradeLabels[$form->{$field.'_grade'}] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Character Traits -->
            <div class="flex flex-col gap-4 border-b border-border/50 pb-6">
                <h4 class="text-base font-semibold text-foreground">(3) Character Traits</h4>
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $characterFields = [
                            'dependability' => 'Dependability (whether he/she is able to work consistently without Close supervision, inspection or compulsion)',
                            'loyalty' => 'Loyalty to the organisation',
                            'honesty' => 'Honesty',
                            'reliability_under_pressure' => 'Reliability under pressure',
                            'sense_of_responsibility' => 'Sense of responsibility',
                            'appearance' => 'Appearance',
                        ];
                    @endphp
                    @foreach($characterFields as $field => $label)
                        @if($form->{$field.'_grade'})
                        <div class="p-4 bg-background rounded-lg border border-border/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <label class="kt-form-label text-sm font-medium mb-1">{{ $label }}</label>
                                    @if($form->{$field.'_comment'})
                                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->{$field.'_comment'} }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col items-center min-w-[80px]">
                                    <span class="text-2xl font-bold text-primary">{{ $form->{$field.'_grade'} }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $gradeLabels[$form->{$field.'_grade'}] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Work Habits -->
            <div class="flex flex-col gap-4 border-b border-border/50 pb-6">
                <h4 class="text-base font-semibold text-foreground">(4) Work Habits</h4>
                <div class="grid grid-cols-1 gap-4">
                    @php
                        $workHabitFields = [
                            'punctuality' => 'Punctuality to work',
                            'attendance' => 'Attendance at work',
                            'drive_determination' => 'Drive and Determination',
                            'resource_utilization' => 'Resource utilization',
                        ];
                    @endphp
                    @foreach($workHabitFields as $field => $label)
                        @if($form->{$field.'_grade'})
                        <div class="p-4 bg-background rounded-lg border border-border/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <label class="kt-form-label text-sm font-medium mb-1">{{ $label }}</label>
                                    @if($form->{$field.'_comment'})
                                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->{$field.'_comment'} }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col items-center min-w-[80px]">
                                    <span class="text-2xl font-bold text-primary">{{ $form->{$field.'_grade'} }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $gradeLabels[$form->{$field.'_grade'}] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Leadership -->
            @php
                $leadershipFields = [
                    'encourage_standards' => 'Does he/she encourage his/her subordinate to define agreed Standards and measures for effectiveness before hand',
                    'train_subordinates' => 'Does he/she encourage and train his/her subordinates to avoid late assessments of goals?',
                    'good_example' => 'Does he/she show good example in terms of punctuality efficiency and high degree of responsibility in whatever he/she does?',
                    'suggestions_improvements' => 'Did he make suggestion for changes/adjust methods/procedures that signaticantly contribute to his own work or that of any associate / Subordinates?',
                ];
                $hasLeadershipGrades = false;
                foreach($leadershipFields as $field => $label) {
                    if($form->{$field.'_grade'}) {
                        $hasLeadershipGrades = true;
                        break;
                    }
                }
            @endphp
            @if($hasLeadershipGrades)
            <div class="flex flex-col gap-4 border-b border-border/50 pb-6">
                <h4 class="text-base font-semibold text-foreground">(5) Leadership Attainment</h4>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($leadershipFields as $field => $label)
                        @if($form->{$field.'_grade'})
                        <div class="p-4 bg-background rounded-lg border border-border/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <label class="kt-form-label text-sm font-medium mb-1">{{ $label }}</label>
                                    @if($form->{$field.'_comment'})
                                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->{$field.'_comment'} }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col items-center min-w-[80px]">
                                    <span class="text-2xl font-bold text-primary">{{ $form->{$field.'_grade'} }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $gradeLabels[$form->{$field.'_grade'}] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sanctions & Rewards -->
            @if($form->disciplinary_action || $form->special_commendation)
            <div class="flex flex-col gap-4">
                @if($form->disciplinary_action)
                <div class="p-4 bg-warning/10 rounded-lg border border-warning/20">
                    <label class="kt-form-label text-sm font-medium mb-2">Disciplinary Action</label>
                    <p class="text-sm text-foreground"><strong>{{ $form->disciplinary_action }}</strong></p>
                    @if($form->disciplinary_action_details)
                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->disciplinary_action_details }}</p>
                    @endif
                </div>
                @endif
                @if($form->special_commendation)
                <div class="p-4 bg-success/10 rounded-lg border border-success/20">
                    <label class="kt-form-label text-sm font-medium mb-2">Special Commendation</label>
                    <p class="text-sm text-foreground"><strong>{{ $form->special_commendation }}</strong></p>
                    @if($form->special_commendation_details)
                    <p class="text-sm text-foreground mt-2 whitespace-pre-wrap">{{ $form->special_commendation_details }}</p>
                    @endif
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Section 11: Overall Assessment -->
@if($form->overall_assessment)
<div class="kt-card shadow-sm bg-muted/20">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Section 11: Overall Assessment</h3>
        <p class="text-sm text-secondary-foreground italic">(Completed by Reporting Officer)</p>
    </div>
    <div class="kt-card-content p-6">
        <div class="p-4 bg-primary/10 rounded-lg border border-primary/20">
            <div class="flex items-center gap-4">
                <div class="flex flex-col items-center justify-center min-w-[100px]">
                    <span class="text-4xl font-bold text-primary">{{ $form->overall_assessment }}</span>
                    <span class="text-sm text-secondary-foreground">{{ $overallLabels[$form->overall_assessment] ?? '' }}</span>
                </div>
                <div class="flex-1">
                    <label class="kt-form-label text-sm font-medium mb-1">Overall Performance Assessment</label>
                    <p class="text-sm text-secondary-foreground">
                        @php
                            $overallDescriptions = [
                                'A' => 'An exceptionally valuable member of the staff: performance is well above the required standard for the job.',
                                'B' => 'Displays good all-round level of effectiveness: performance meets requirements all important tasks',
                                'C' => 'A competent member of the staff: generally achieves the standards required.',
                                'D' => 'Completes all assignment satisfactorily within agreed date',
                                'E' => 'Performance does not always reach the required standard: room for improvement',
                                'F' => 'Performance does not meet the required standard',
                            ];
                        @endphp
                        {{ $overallDescriptions[$form->overall_assessment] ?? '' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Sections 12-15: Final Assessment -->
<div class="kt-card shadow-sm bg-muted/20">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Sections 12-15: Final Assessment</h3>
        <p class="text-sm text-secondary-foreground italic">(Completed by Reporting Officer)</p>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-6">
            @if($form->training_needs_assessment)
            <div class="p-4 bg-background rounded-lg border border-border/50">
                <label class="kt-form-label text-sm font-medium mb-2">Section 12: Training Needs</label>
                <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->training_needs_assessment }}</p>
            </div>
            @endif

            @if($form->general_remarks)
            <div class="p-4 bg-background rounded-lg border border-border/50">
                <label class="kt-form-label text-sm font-medium mb-2">Section 13: General Remarks</label>
                <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->general_remarks }}</p>
            </div>
            @endif

            @if($form->suggest_different_job || $form->suggest_transfer)
            <div class="p-4 bg-background rounded-lg border border-border/50">
                <label class="kt-form-label text-sm font-medium mb-2">Section 14: Suggestions</label>
                <div class="flex flex-col gap-3 mt-2">
                    @if($form->suggest_different_job)
                    <div>
                        <p class="text-sm font-medium text-foreground">Different Job: <strong>{{ $form->suggest_different_job }}</strong></p>
                        @if($form->different_job_details)
                        <p class="text-sm text-foreground mt-1 whitespace-pre-wrap">{{ $form->different_job_details }}</p>
                        @endif
                    </div>
                    @endif
                    @if($form->suggest_transfer)
                    <div>
                        <p class="text-sm font-medium text-foreground">Transfer: <strong>{{ $form->suggest_transfer }}</strong></p>
                        @if($form->transfer_details)
                        <p class="text-sm text-foreground mt-1 whitespace-pre-wrap">{{ $form->transfer_details }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($form->promotability)
            <div class="p-4 bg-success/10 rounded-lg border border-success/20">
                <label class="kt-form-label text-sm font-medium mb-2">Section 15: Promotability</label>
                <div class="flex items-start gap-4 mt-2">
                    <div class="flex flex-col items-center justify-center min-w-[80px]">
                        <span class="text-3xl font-bold text-success">{{ $form->promotability }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-foreground">{{ $promotabilityLabels[$form->promotability] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($form->reporting_officer_declaration)
            <div class="p-4 bg-primary/10 rounded-lg border border-primary/20">
                <label class="kt-form-label text-sm font-medium mb-2">Reporting Officer Declaration</label>
                <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->reporting_officer_declaration }}</p>
                @if($form->reporting_officer_completed_at)
                <p class="text-xs text-secondary-foreground mt-2">Completed: {{ $form->reporting_officer_completed_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

