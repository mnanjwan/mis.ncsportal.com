<!-- Section 11: Overall Assessment -->
<div class="kt-card shadow-sm">
    <div class="kt-card-header bg-muted/50 border-b border-border">
        <h3 class="kt-card-title text-lg font-semibold">Section 11: Overall Assessment</h3>
        <p class="text-sm text-secondary-foreground mt-2">From the above assessment, indicate the overall performance of his/her duties by selecting the appropriate rating below</p>
    </div>
    <div class="kt-card-content p-6">
        <div class="flex flex-col gap-4">
            <label class="kt-form-label mb-2 font-medium text-base">Select Overall Assessment:</label>
            <div class="grid grid-cols-1 gap-3">
                @php
                    $overallOptions = [
                        'A' => ['label' => 'Outstanding', 'desc' => 'An exceptionally valuable member of the staff: performance is well above the required standard for the job.', 'color' => 'success'],
                        'B' => ['label' => 'Very Good', 'desc' => 'Displays good all-round level of effectiveness: performance meets requirements all important tasks', 'color' => 'primary'],
                        'C' => ['label' => 'Good', 'desc' => 'A competent member of the staff: generally achieves the standards required.', 'color' => 'info'],
                        'D' => ['label' => 'Satisfactory', 'desc' => 'Completes all assignment satisfactorily within agreed date', 'color' => 'warning'],
                        'E' => ['label' => 'Fair', 'desc' => 'Performance does not always reach the required standard: room for improvement', 'color' => 'warning'],
                        'F' => ['label' => 'Poor', 'desc' => 'Performance does not meet the required standard', 'color' => 'danger'],
                    ];
                @endphp
                @foreach($overallOptions as $grade => $option)
                    @php
                        $isChecked = old('overall_assessment', $form->overall_assessment) == $grade;
                        $bgClass = $isChecked ? "bg-{$option['color']}/10 border-{$option['color']}/30" : 'bg-background border-border/50 hover:bg-muted/50';
                    @endphp
                    <label class="flex items-start gap-3 cursor-pointer p-4 rounded-lg border-2 transition-all {{ $bgClass }}">
                        <input type="radio" 
                               name="overall_assessment" 
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
    </div>
</div>

