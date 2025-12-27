<!-- Grade Field Component -->
<div class="flex flex-col gap-2">
    <label class="kt-form-label mb-1">{{ $label }}</label>
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-4">
                <span class="text-sm text-secondary-foreground min-w-[80px]">Grade:</span>
                <div class="flex gap-2">
                    @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="radio" 
                                   name="{{ $fieldName }}_grade" 
                                   value="{{ $grade }}"
                                   class="rounded border-input text-primary focus:ring-primary"
                                   {{ old($fieldName.'_grade', $form->{$fieldName.'_grade'}) == $grade ? 'checked' : '' }}>
                            <span class="text-sm">{{ $grade }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="mt-2 flex flex-col gap-2">
        <label class="kt-form-label text-sm mb-1">Comment/Justification:</label>
        <textarea name="{{ $fieldName }}_comment" 
                  class="kt-input" 
                  rows="2" 
                  placeholder="Please justify the grading">{{ old($fieldName.'_comment', $form->{$fieldName.'_comment'}) }}</textarea>
    </div>
</div>

