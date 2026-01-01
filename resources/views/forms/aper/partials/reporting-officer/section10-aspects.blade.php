<!-- Section 10: Aspects of Performance -->
<div class="kt-card shadow-sm">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Section 10: Aspects of Performance</h3>
        <div class="mt-3 space-y-2">
            <p class="text-sm text-secondary-foreground leading-relaxed">
                In assessing Performance you are to consider some or all of the following aspects and comment on as well as assess them separately. 
                Each aspect is described in terms of Outstanding (A) and Very poor (F). The four intermediate ratings (B,C,D,E) represent behaviour 
                between these extremes. Rating 'A' OR 'F' should be given if you believe it is a generally true statement. Either of the two ratings 
                however, must be supported in paragraph 14 and General Remarks.
            </p>
            <div class="flex items-center gap-2 p-3 bg-primary/10 rounded-md border border-primary/20">
                <span class="text-sm font-semibold text-primary">Grading Weights:</span>
                <div class="flex gap-4 text-sm">
                    <span>A: <strong>6</strong></span>
                    <span>B: <strong>5</strong></span>
                    <span>C: <strong>4</strong></span>
                    <span>D: <strong>3</strong></span>
                    <span>E: <strong>2</strong></span>
                    <span>F: <strong>1</strong></span>
                </div>
            </div>
        </div>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-6">
            <!-- Sub-section (1) Job Assessment/General Ability -->
            <div class="flex flex-col gap-5 border-b border-border/50 pb-6">
                <div class="flex flex-col gap-2">
                    <h4 class="text-lg font-semibold text-foreground">(1) Job Assessment/General Ability</h4>
                    <p class="text-sm text-secondary-foreground italic">(Assess objectively how the officer has performed his set tasks)</p>
                </div>
                
                @php
                    $jobAssessmentFields = [
                        ['name' => 'job_understanding', 'label' => '(a) How well he/she understands, organises and does his/her job'],
                        ['name' => 'knowledge_application', 'label' => '(b) How well he/she applied his/her professional/technical/administrative or any other acquired knowledge'],
                        ['name' => 'accomplishment', 'label' => '(c) How much he/she was able to accomplish within a set time frame'],
                        ['name' => 'judgement', 'label' => '(d) Judgement (quality of his/her decisions and contributions) where Relevant'],
                        ['name' => 'work_speed_accuracy', 'label' => '(e) Work-speed and accuracy'],
                    ];
                @endphp
                
                @foreach($jobAssessmentFields as $field)
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => $field['name'],
                        'label' => $field['label'],
                        'form' => $form
                    ])
                @endforeach

                <!-- Effectiveness of Communications -->
                <div class="mt-4 p-5 bg-primary/5 rounded-lg border border-primary/10">
                    <h5 class="font-semibold mb-4 text-foreground">Effectiveness of communications:</h5>
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => 'written_expression',
                        'label' => '(f) Expression on paper',
                        'form' => $form
                    ])
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => 'oral_expression',
                        'label' => '(g) Oral Expression',
                        'form' => $form
                    ])
                </div>

                <!-- Human Relations -->
                <div class="mt-4 p-5 bg-success/5 rounded-lg border border-success/10">
                    <h5 class="font-semibold mb-4 text-foreground">Human Relations:</h5>
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => 'staff_relations',
                        'label' => '(h) Relation with staff',
                        'form' => $form
                    ])
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => 'public_relations',
                        'label' => '(I) Relation with public',
                        'form' => $form
                    ])
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => 'staff_management',
                        'label' => '(j) Management of staff',
                        'form' => $form
                    ])
                </div>
            </div>

            <!-- Quality of Work -->
            <div class="flex flex-col gap-5 border-t border-border/50 pt-6">
                <div class="flex flex-col gap-2">
                    <h4 class="text-lg font-semibold text-foreground">(2) Quality of Work</h4>
                </div>
                @php
                    $qualityFields = [
                        ['name' => 'quality_of_work', 'label' => '(k) Quality of work'],
                        ['name' => 'productivity', 'label' => '(i) Productivity'],
                        ['name' => 'effective_use_of_data', 'label' => '(M) Effective use of figures / other Data'],
                        ['name' => 'initiative', 'label' => '(n) Initiative'],
                    ];
                @endphp
                @foreach($qualityFields as $field)
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => $field['name'],
                        'label' => $field['label'],
                        'form' => $form
                    ])
                @endforeach
            </div>

            <!-- Character Traits -->
            <div class="flex flex-col gap-5 border-t border-border/50 pt-6">
                <div class="flex flex-col gap-2">
                    <h4 class="text-lg font-semibold text-foreground">(3) Character Traits</h4>
                    <p class="text-sm text-secondary-foreground italic">In assessing character traits, consideration should be given to:</p>
                </div>
                @php
                    $characterFields = [
                        ['name' => 'dependability', 'label' => '(a) Dependability (whether he/she is able to work consistently without Close supervision, inspection or compulsion)'],
                        ['name' => 'loyalty', 'label' => '(b) Loyalty to the organisation'],
                        ['name' => 'honesty', 'label' => '(c) Honesty'],
                        ['name' => 'reliability_under_pressure', 'label' => '(d) Reliability under pressure'],
                        ['name' => 'sense_of_responsibility', 'label' => '(e) Sense of responsibility'],
                        ['name' => 'appearance', 'label' => '(f) Appearance'],
                    ];
                @endphp
                @foreach($characterFields as $field)
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => $field['name'],
                        'label' => $field['label'],
                        'form' => $form
                    ])
                @endforeach
            </div>

            <!-- Work Habits -->
            <div class="flex flex-col gap-5 border-t border-border/50 pt-6">
                <div class="flex flex-col gap-2">
                    <h4 class="text-lg font-semibold text-foreground">(4) Work Habits</h4>
                    <h5 class="font-medium text-foreground">Criteria:</h5>
                </div>
                @php
                    $workHabitFields = [
                        ['name' => 'punctuality', 'label' => '(i) Punctuality to work'],
                        ['name' => 'attendance', 'label' => '(ii) Attendance at work'],
                        ['name' => 'drive_determination', 'label' => '(iii) Drive and Determination'],
                        ['name' => 'resource_utilization', 'label' => '(iv) Resource utilization'],
                    ];
                @endphp
                @foreach($workHabitFields as $field)
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => $field['name'],
                        'label' => $field['label'],
                        'form' => $form
                    ])
                @endforeach
            </div>

            <!-- Sanctions -->
            <div class="flex flex-col gap-4 border-t border-border/50 pt-6">
                <div class="flex flex-col gap-2 mb-4">
                    <h4 class="text-lg font-semibold text-foreground">(5) Sanctions</h4>
                </div>
                <div class="flex flex-col gap-3 p-4 bg-warning/5 rounded-lg border border-warning/10">
                    <label class="kt-form-label mb-0 font-medium">Has any disciplinary action been taken against the officer during the period covered by this report?</label>
                    <select name="disciplinary_action" class="kt-input max-w-xs">
                        <option value="">-- Select YES or NO --</option>
                        <option value="YES" {{ old('disciplinary_action', $form->disciplinary_action) == 'YES' ? 'selected' : '' }}>YES</option>
                        <option value="NO" {{ old('disciplinary_action', $form->disciplinary_action) == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                    <textarea name="disciplinary_action_details" class="kt-input" rows="3" placeholder="If YES, provide details of sanctions...">{{ old('disciplinary_action_details', $form->disciplinary_action_details) }}</textarea>
                </div>
            </div>

            <!-- Rewards -->
            <div class="flex flex-col gap-4 border-t border-border/50 pt-6">
                <div class="flex flex-col gap-2 mb-4">
                    <h4 class="text-lg font-semibold text-foreground">(6) Rewards</h4>
                </div>
                <div class="flex flex-col gap-3 p-4 bg-success/5 rounded-lg border border-success/10">
                    <label class="kt-form-label mb-0 font-medium">Has the officer received any special commendation (WRITTEN) during the year for outstanding performance?</label>
                    <select name="special_commendation" class="kt-input max-w-xs">
                        <option value="">-- Select YES or NO --</option>
                        <option value="YES" {{ old('special_commendation', $form->special_commendation) == 'YES' ? 'selected' : '' }}>YES</option>
                        <option value="NO" {{ old('special_commendation', $form->special_commendation) == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                    <textarea name="special_commendation_details" class="kt-input" rows="3" placeholder="If YES, provide details...">{{ old('special_commendation_details', $form->special_commendation_details) }}</textarea>
                </div>
            </div>

            <!-- Leadership Attainment -->
            <div class="flex flex-col gap-5 border-t border-border/50 pt-6">
                <div class="flex flex-col gap-2">
                    <h4 class="text-lg font-semibold text-foreground">(7) Leadership Attainment</h4>
                </div>
                @php
                    $leadershipFields = [
                        ['name' => 'encourage_standards', 'label' => '(i) Does he/she encourage his/her subordinate to define agreed Standards and measures for effectiveness before hand'],
                        ['name' => 'train_subordinates', 'label' => '(ii) Does he/she encourage and train his/her subordinates to avoid late assessments of goals?'],
                        ['name' => 'good_example', 'label' => '(iii) Does he/she show good example in terms of punctuality efficiency and high degree of responsibility in whatever he/she does?'],
                        ['name' => 'suggestions_improvements', 'label' => '(iv) Did he make suggestion for changes/adjust methods/procedures that signaticantly contribute to his own work or that of any associate / Subordinates?'],
                    ];
                @endphp
                @foreach($leadershipFields as $field)
                    @include('forms.aper.partials.reporting-officer.grade-field', [
                        'fieldName' => $field['name'],
                        'label' => $field['label'],
                        'form' => $form
                    ])
                @endforeach
            </div>
        </div>
    </div>
</div>

