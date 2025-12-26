<!-- Section 9: Assessment of Performance -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">PART III: Section 9 - Assessment of Performance</h3>
        <p class="text-sm text-secondary-foreground italic">(To be completed by the Reporting Officer)</p>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-5">
            <div>
                <label class="kt-form-label">(a) Did you and the person reported upon agree on the targets set? YES/NO</label>
                <select name="targets_agreed" class="kt-input">
                    <option value="">Select...</option>
                    <option value="YES" {{ old('targets_agreed', $form->targets_agreed) == 'YES' ? 'selected' : '' }}>YES</option>
                    <option value="NO" {{ old('targets_agreed', $form->targets_agreed) == 'NO' ? 'selected' : '' }}>NO</option>
                </select>
            </div>
            
            <div>
                <label class="kt-form-label">(b) Did you and the person reported upon agree on the main duties performed and the order of importance under the target set? YES/NO</label>
                <div class="flex flex-col gap-2">
                    <select name="duties_agreed" class="kt-input">
                        <option value="">Select...</option>
                        <option value="YES" {{ old('duties_agreed', $form->duties_agreed) == 'YES' ? 'selected' : '' }}>YES</option>
                        <option value="NO" {{ old('duties_agreed', $form->duties_agreed) == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                    <p class="text-xs text-secondary-foreground italic">(If not, please discuss the changes with him and record any unsolved difference here)</p>
                    <textarea name="duties_agreement_details" class="kt-input" rows="3" placeholder="Record any differences">{{ old('duties_agreement_details', $form->duties_agreement_details) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

