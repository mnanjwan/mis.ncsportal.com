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
                <div class="relative max-w-xs">
                    <input type="hidden" name="targets_agreed" id="targets_agreed_id" value="{{ old('targets_agreed', $form->targets_agreed) ?? '' }}">
                    <button type="button" 
                            id="targets_agreed_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="targets_agreed_select_text">{{ old('targets_agreed', $form->targets_agreed) ? old('targets_agreed', $form->targets_agreed) : '-- Select YES or NO --' }}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="targets_agreed_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="targets_agreed_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search..."
                                   autocomplete="off">
                        </div>
                        <div id="targets_agreed_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
                <textarea name="targets_agreement_details" class="kt-input" rows="3" placeholder="If NO, please provide details...">{{ old('targets_agreement_details', $form->targets_agreement_details) }}</textarea>
            </div>
            
            <!-- Duties Agreement -->
            <div class="flex flex-col gap-3 p-4 bg-muted/30 rounded-lg border border-border/50">
                <label class="kt-form-label mb-0 font-medium">(b) Did you and the person reported upon agree on the main duties performed and the order of importance under the target set?</label>
                <div class="relative max-w-xs">
                    <input type="hidden" name="duties_agreed" id="duties_agreed_id" value="{{ old('duties_agreed', $form->duties_agreed) ?? '' }}">
                    <button type="button" 
                            id="duties_agreed_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="duties_agreed_select_text">{{ old('duties_agreed', $form->duties_agreed) ? old('duties_agreed', $form->duties_agreed) : '-- Select YES or NO --' }}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="duties_agreed_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="duties_agreed_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search..."
                                   autocomplete="off">
                        </div>
                        <div id="duties_agreed_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
                <p class="text-xs text-secondary-foreground italic">(If NO, please discuss the changes with the officer and record any unsolved differences here)</p>
                <textarea name="duties_agreement_details" class="kt-input" rows="3" placeholder="Record any differences or disagreements...">{{ old('duties_agreement_details', $form->duties_agreement_details) }}</textarea>
            </div>
        </div>
    </div>
</div>

