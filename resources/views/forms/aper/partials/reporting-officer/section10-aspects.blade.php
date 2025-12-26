<!-- Section 10: Aspects of Performance -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">Section 10: Aspects of Performance</h3>
        <p class="text-sm text-secondary-foreground">
            In assessing Performance you are to consider some or all of the following aspects and comment on as well as assess them separately. 
            Each aspect is described in terms of Outstanding (A) and Very poor (F). The four intermediate ratings (B,C,D,E) represent behaviour 
            between these extremes. Rating 'A' OR 'F' should be given if you believe it is a generally true statement. Either of the two ratings 
            however, must be supported in paragraph 14 and General Remarks.
        </p>
        <p class="text-sm text-secondary-foreground mt-2">
            <strong>Grading Weights:</strong> A: 6, B: 5, C: 4, D: 3, E: 2, F: 1
        </p>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-6">
            <!-- Sub-section (1) Job Assessment/General Ability -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">(1) Job Assessment/General Ability</h4>
                <p class="text-sm text-secondary-foreground italic">(Assess objectively how the officer has performed his set tasks)</p>
                
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
                <div class="mt-4 p-4 bg-muted/50 rounded-lg">
                    <h5 class="font-semibold mb-3">Effectiveness of communications:</h5>
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
                <div class="mt-4 p-4 bg-muted/50 rounded-lg">
                    <h5 class="font-semibold mb-3">Human Relations:</h5>
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
            <div class="flex flex-col gap-4 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">Quality of Work</h4>
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
            <div class="flex flex-col gap-4 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">Section (H) Character Traits</h4>
                <p class="text-sm text-secondary-foreground italic">In assessing character traits, consideration should be given to:</p>
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
            <div class="flex flex-col gap-4 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">Section (III) Work Habits</h4>
                <h5 class="font-semibold">(A) Criteria:</h5>
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
            <div class="flex flex-col gap-4 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">Section (b) Sanctions</h4>
                <div>
                    <label class="kt-form-label">Has any disciplinary action been taken against the officer during the period covered by this report?</label>
                    <div class="flex flex-col gap-2">
                        <select name="disciplinary_action" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('disciplinary_action', $form->disciplinary_action) == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('disciplinary_action', $form->disciplinary_action) == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                        <textarea name="disciplinary_action_details" class="kt-input" rows="3" placeholder="If Yes give details of sanctions">{{ old('disciplinary_action_details', $form->disciplinary_action_details) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Rewards -->
            <div class="flex flex-col gap-4 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">Section (C) REWARD</h4>
                <div>
                    <label class="kt-form-label">Has the officer received any special commendation (WRITTEN) during the year for outstanding performance?</label>
                    <div class="flex flex-col gap-2">
                        <select name="special_commendation" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('special_commendation', $form->special_commendation) == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('special_commendation', $form->special_commendation) == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                        <textarea name="special_commendation_details" class="kt-input" rows="3" placeholder="If yes give details">{{ old('special_commendation_details', $form->special_commendation_details) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Leadership Attainment -->
            <div class="flex flex-col gap-4 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">Section (IV) Leadership Attainment</h4>
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

