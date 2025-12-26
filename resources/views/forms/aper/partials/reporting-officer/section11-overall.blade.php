<!-- Section 11: Overall Assessment -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">Section 11: Overall Assessment</h3>
        <p class="text-sm text-secondary-foreground">From the above assessment, indicate the overall performance of his/her duties by ticking the appropriate box below</p>
    </div>
    <div class="kt-card-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="kt-form-label mb-3">Select Overall Assessment:</label>
                <div class="flex flex-col gap-2">
                    @php
                        $overallOptions = [
                            'A' => 'Outstanding - An exceptionally valuable member of the staff: performance is well above the required standard for the job.',
                            'B' => 'Very Good - Displays good all-round level of effectiveness: performance meets requirements all important tasks',
                            'C' => 'Good - A competent member of the staff: generally achieves the standards required.',
                            'D' => 'Satisfactory - Completes all assignment satisfactorily within agreed date',
                            'E' => 'Fair - Performance does not always reach the required standard: room for improvement',
                            'F' => 'Poor - Performance does not meet the required standard',
                        ];
                    @endphp
                    @foreach($overallOptions as $grade => $description)
                        <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-muted/50">
                            <input type="radio" 
                                   name="overall_assessment" 
                                   value="{{ $grade }}"
                                   class="mt-1 rounded border-input text-primary focus:ring-primary"
                                   {{ old('overall_assessment', $form->overall_assessment) == $grade ? 'checked' : '' }}>
                            <div class="flex-1">
                                <span class="text-sm font-medium">{{ $grade }}: {{ explode(' - ', $description)[0] }}</span>
                                <p class="text-xs text-secondary-foreground mt-1">{{ explode(' - ', $description)[1] ?? '' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

