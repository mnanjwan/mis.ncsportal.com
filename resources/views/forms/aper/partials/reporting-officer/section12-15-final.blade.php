<!-- Sections 12-15: Training Needs, General Remarks, Suggestions, Promotability -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">Sections 12-15: Final Assessment</h3>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-5">
            <!-- Section 12: Training Needs -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">Section 12: Training Needs</label>
                <p class="text-sm text-secondary-foreground mb-2">Indicate training needs necessary to improve the performance or potential for the officer:</p>
                <textarea name="training_needs_assessment" class="kt-input" rows="4">{{ old('training_needs_assessment', $form->training_needs_assessment) }}</textarea>
            </div>

            <!-- Section 13: General Remarks -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">Section 13: General Remarks</label>
                <p class="text-sm text-secondary-foreground mb-2">
                    Please provide any additional relevant information here drawing attention to any particular strengths or weaknesses 
                    and indicate special aptitudes (if any) demonstrated by the officer
                </p>
                <textarea name="general_remarks" class="kt-input" rows="5">{{ old('general_remarks', $form->general_remarks) }}</textarea>
            </div>

            <!-- Section 14: Suggestions -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">Section 14: Do you suggest the officer for;</label>
                <div class="flex flex-col gap-4 mt-3">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(a) A different job in the same grade: Yes/No</label>
                        <div class="flex flex-col gap-2">
                            <select name="suggest_different_job" class="kt-input">
                                <option value="">Select...</option>
                                <option value="YES" {{ old('suggest_different_job', $form->suggest_different_job) == 'YES' ? 'selected' : '' }}>YES</option>
                                <option value="NO" {{ old('suggest_different_job', $form->suggest_different_job) == 'NO' ? 'selected' : '' }}>NO</option>
                            </select>
                            <textarea name="different_job_details" class="kt-input" rows="2" placeholder="If YES, say which kind of job and give reasons">{{ old('different_job_details', $form->different_job_details) }}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(B) Transfer to a job at similar level in another occupational group or cadre? Yes/No.</label>
                        <div class="flex flex-col gap-2">
                            <select name="suggest_transfer" class="kt-input">
                                <option value="">Select...</option>
                                <option value="YES" {{ old('suggest_transfer', $form->suggest_transfer) == 'YES' ? 'selected' : '' }}>YES</option>
                                <option value="NO" {{ old('suggest_transfer', $form->suggest_transfer) == 'NO' ? 'selected' : '' }}>NO</option>
                            </select>
                            <textarea name="transfer_details" class="kt-input" rows="2" placeholder="If YES, say which kind of job and give reasons">{{ old('transfer_details', $form->transfer_details) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 15: Promotability -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">Section 15: Promotability</label>
                <p class="text-sm text-secondary-foreground mb-3">Select the appropriate statement:</p>
                <div class="flex flex-col gap-2">
                    @php
                        $promotabilityOptions = [
                            'A' => 'Exceptionally well qualified, the officer already seems likely to stand out in the next higher post',
                            'B' => 'Ready for promotion',
                            'C' => 'Has promotion potentials, has completed the required number of years',
                            'D' => 'Not yet ripe for promotion (for non-fulfilment of condition prescribed for Promotion in line with the Guidelines on Civil Service Reforms',
                            'E' => 'No evidence of promotion potential at present',
                            'F' => 'Unlikely to qualify as he/she seems to have reached the limit of his/her capacity',
                        ];
                    @endphp
                    @foreach($promotabilityOptions as $grade => $description)
                        <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-muted/50">
                            <input type="radio" 
                                   name="promotability" 
                                   value="{{ $grade }}"
                                   class="mt-1 rounded border-input text-primary focus:ring-primary"
                                   {{ old('promotability', $form->promotability) == $grade ? 'checked' : '' }}>
                            <div class="flex-1">
                                <span class="text-sm font-medium">{{ $grade }}.</span>
                                <span class="text-sm text-foreground">{{ $description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Declaration -->
            <div class="border-t border-border pt-4 flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">Reporting Officer Declaration</label>
                <textarea name="reporting_officer_declaration" class="kt-input" rows="3" placeholder="Enter declaration statement">{{ old('reporting_officer_declaration', $form->reporting_officer_declaration) }}</textarea>
            </div>
        </div>
    </div>
</div>

