<!-- Sections 12-15: Training Needs, General Remarks, Suggestions, Promotability -->
<div class="kt-card shadow-sm">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Sections 12-15: Final Assessment</h3>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-6">
            <!-- Section 12: Training Needs -->
            <div class="flex flex-col gap-3 p-4 bg-info/5 rounded-lg border border-info/10">
                <label class="kt-form-label font-semibold mb-0 text-base">Section 12: Training Needs</label>
                <p class="text-sm text-secondary-foreground mb-2">Indicate training needs necessary to improve the performance or potential for the officer:</p>
                <textarea name="training_needs_assessment" class="kt-input" rows="5" placeholder="Describe the training needs identified...">{{ old('training_needs_assessment', $form->training_needs_assessment) }}</textarea>
            </div>

            <!-- Section 13: General Remarks -->
            <div class="flex flex-col gap-3 p-4 bg-primary/5 rounded-lg border border-primary/10">
                <label class="kt-form-label font-semibold mb-0 text-base">Section 13: General Remarks</label>
                <p class="text-sm text-secondary-foreground mb-2">
                    Please provide any additional relevant information here drawing attention to any particular strengths or weaknesses 
                    and indicate special aptitudes (if any) demonstrated by the officer
                </p>
                <textarea name="general_remarks" class="kt-input" rows="6" placeholder="Provide general remarks and observations...">{{ old('general_remarks', $form->general_remarks) }}</textarea>
            </div>

            <!-- Section 14: Suggestions -->
            <div class="flex flex-col gap-4 p-4 bg-warning/5 rounded-lg border border-warning/10">
                <label class="kt-form-label font-semibold mb-0 text-base">Section 14: Do you suggest the officer for;</label>
                <div class="flex flex-col gap-4 mt-2">
                    <div class="flex flex-col gap-3">
                        <label class="kt-form-label mb-0 font-medium">(a) A different job in the same grade: Yes/No</label>
                        <select name="suggest_different_job" class="kt-input max-w-xs">
                            <option value="">-- Select YES or NO --</option>
                            <option value="YES" {{ old('suggest_different_job', $form->suggest_different_job) == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('suggest_different_job', $form->suggest_different_job) == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                        <textarea name="different_job_details" class="kt-input" rows="3" placeholder="If YES, specify the type of job and provide reasons...">{{ old('different_job_details', $form->different_job_details) }}</textarea>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-border/50 pt-4">
                        <label class="kt-form-label mb-0 font-medium">(b) Transfer to a job at similar level in another occupational group or cadre? Yes/No.</label>
                        <select name="suggest_transfer" class="kt-input max-w-xs">
                            <option value="">-- Select YES or NO --</option>
                            <option value="YES" {{ old('suggest_transfer', $form->suggest_transfer) == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('suggest_transfer', $form->suggest_transfer) == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                        <textarea name="transfer_details" class="kt-input" rows="3" placeholder="If YES, specify the type of job and provide reasons...">{{ old('transfer_details', $form->transfer_details) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 15: Promotability -->
            <div class="flex flex-col gap-4 p-4 bg-success/5 rounded-lg border border-success/10">
                <label class="kt-form-label font-semibold mb-0 text-base">Section 15: Promotability</label>
                <p class="text-sm text-secondary-foreground mb-3">Select the appropriate statement:</p>
                <div class="flex flex-col gap-3">
                    @php
                        $promotabilityOptions = [
                            'A' => ['label' => 'Exceptionally well qualified', 'desc' => 'The officer already seems likely to stand out in the next higher post', 'color' => 'success'],
                            'B' => ['label' => 'Ready for promotion', 'desc' => 'The officer is ready to take on higher responsibilities', 'color' => 'primary'],
                            'C' => ['label' => 'Has promotion potentials', 'desc' => 'Has completed the required number of years', 'color' => 'info'],
                            'D' => ['label' => 'Not yet ripe for promotion', 'desc' => 'For non-fulfilment of condition prescribed for Promotion in line with the Guidelines on Civil Service Reforms', 'color' => 'warning'],
                            'E' => ['label' => 'No evidence of promotion potential at present', 'desc' => 'Further development needed', 'color' => 'warning'],
                            'F' => ['label' => 'Unlikely to qualify', 'desc' => 'Seems to have reached the limit of his/her capacity', 'color' => 'danger'],
                        ];
                    @endphp
                    @foreach($promotabilityOptions as $grade => $option)
                        @php
                            $isChecked = old('promotability', $form->promotability) == $grade;
                            $bgClass = $isChecked ? "bg-{$option['color']}/10 border-{$option['color']}/30" : 'bg-background border-border/50 hover:bg-muted/50';
                        @endphp
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border-2 transition-all {{ $bgClass }}">
                            <input type="radio" 
                                   name="promotability" 
                                   value="{{ $grade }}"
                                   class="mt-1 w-5 h-5 rounded-full border-input text-primary focus:ring-primary"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-base font-bold text-foreground">{{ $grade }}.</span>
                                    <span class="text-base font-semibold text-foreground">{{ $option['label'] }}</span>
                                </div>
                                <p class="text-sm text-secondary-foreground leading-relaxed">{{ $option['desc'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Declaration -->
            <div class="border-t-2 border-border pt-5 flex flex-col gap-3 p-4 bg-muted/30 rounded-lg">
                <label class="kt-form-label font-semibold mb-0 text-base">Reporting Officer Declaration</label>
                <textarea name="reporting_officer_declaration" class="kt-input" rows="4" placeholder="Enter your declaration statement...">{{ old('reporting_officer_declaration', $form->reporting_officer_declaration) }}</textarea>
                <p class="text-xs text-secondary-foreground italic">By completing this declaration, you certify that the information provided is accurate and truthful.</p>
            </div>
        </div>
    </div>
</div>

