<!-- Grade Field Component -->
<div class="flex flex-col gap-3 p-4 bg-muted/30 rounded-lg border border-border/50 hover:border-primary/20 transition-colors">
    <label class="kt-form-label mb-0 text-sm font-medium text-foreground">{{ $label }}</label>
    
    <!-- Grade Selection -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2 min-w-[100px]">
            <span class="text-sm font-medium text-secondary-foreground">Grade:</span>
        </div>
        <div class="flex flex-wrap gap-2 flex-1 grade-group" data-field="{{ $fieldName }}">
            @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                @php
                    $isChecked = old($fieldName.'_grade', $form->{$fieldName.'_grade'}) == $grade;
                @endphp
                <label class="grade-option flex items-center justify-center cursor-pointer px-4 py-2 rounded-md border-2 transition-all min-w-[50px] 
                    @if($isChecked) border-primary bg-primary/10 text-primary font-semibold @else border-border/50 bg-background text-foreground hover:border-primary/50 hover:bg-muted/50 @endif">
                    <input type="radio" 
                           name="{{ $fieldName }}_grade" 
                           value="{{ $grade }}"
                           class="sr-only grade-radio"
                           {{ $isChecked ? 'checked' : '' }}>
                    <span class="text-sm font-medium">{{ $grade }}</span>
                </label>
            @endforeach
        </div>
    </div>
    
    <!-- Comment Field -->
    <div class="flex flex-col gap-2 mt-1">
        <label class="kt-form-label text-xs mb-0 text-secondary-foreground font-medium">Comment/Justification:</label>
        <textarea name="{{ $fieldName }}_comment" 
                  class="kt-input text-sm" 
                  rows="3" 
                  placeholder="Please provide justification for the grade assigned...">{{ old($fieldName.'_comment', $form->{$fieldName.'_comment'}) }}</textarea>
    </div>
</div>

