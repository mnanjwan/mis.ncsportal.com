<!-- Section 9: Assessment of Performance -->
<div class="kt-card shadow-sm">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">PART III: Section 9 - Assessment of Performance</h3>
        <p class="text-sm text-secondary-foreground italic mt-1">(To be completed by the Reporting Officer)</p>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-6">
            <!-- Other Comments -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label mb-1 font-medium">(g) Any other comments on issues not mentioned above</label>
                <textarea name="other_comments" class="kt-input" rows="4" placeholder="Enter any other comments or observations...">{{ old('other_comments', $form->other_comments) }}</textarea>
            </div>
            
            <!-- Targets Agreement -->
            <div class="flex flex-col gap-3 p-4 bg-muted/30 rounded-lg border border-border/50">
                <label class="kt-form-label mb-0 font-medium">(a) Did you and the person reported upon agree on the targets set?</label>
                <select name="targets_agreed" class="kt-input max-w-xs">
                    <option value="">-- Select YES or NO --</option>
                    <option value="YES" {{ old('targets_agreed', $form->targets_agreed) == 'YES' ? 'selected' : '' }}>YES</option>
                    <option value="NO" {{ old('targets_agreed', $form->targets_agreed) == 'NO' ? 'selected' : '' }}>NO</option>
                </select>
                <textarea name="targets_agreement_details" class="kt-input" rows="3" placeholder="If NO, please provide details...">{{ old('targets_agreement_details', $form->targets_agreement_details) }}</textarea>
            </div>
            
            <!-- Duties Agreement -->
            <div class="flex flex-col gap-3 p-4 bg-muted/30 rounded-lg border border-border/50">
                <label class="kt-form-label mb-0 font-medium">(b) Did you and the person reported upon agree on the main duties performed and the order of importance under the target set?</label>
                <select name="duties_agreed" class="kt-input max-w-xs">
                    <option value="">-- Select YES or NO --</option>
                    <option value="YES" {{ old('duties_agreed', $form->duties_agreed) == 'YES' ? 'selected' : '' }}>YES</option>
                    <option value="NO" {{ old('duties_agreed', $form->duties_agreed) == 'NO' ? 'selected' : '' }}>NO</option>
                </select>
                <p class="text-xs text-secondary-foreground italic">(If NO, please discuss the changes with the officer and record any unsolved differences here)</p>
                <textarea name="duties_agreement_details" class="kt-input" rows="3" placeholder="Record any differences or disagreements...">{{ old('duties_agreement_details', $form->duties_agreement_details) }}</textarea>
            </div>
        </div>
    </div>
</div>

