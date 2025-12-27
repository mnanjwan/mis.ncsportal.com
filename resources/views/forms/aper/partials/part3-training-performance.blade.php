<!-- PART 3: Training Courses and Job Performance -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">PART 3: Training Courses & Job Performance</h3>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-5">
            <!-- 7. Training Courses/Seminars Attended since Appointment -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">7. Training Courses/Seminars Attended since Appointment</h4>
                <p class="text-sm text-secondary-foreground italic mb-2">Training courses are automatically fetched from your records since appointment. You can review and edit if needed.</p>
                <div class="overflow-x-auto">
                    <table class="kt-table w-full" id="training-courses-table">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-2 px-3 text-sm">Type of Training/Seminar</th>
                                <th class="text-left py-2 px-3 text-sm">Where the Training was Held</th>
                                <th class="text-left py-2 px-3 text-sm">Period of Training From</th>
                                <th class="text-left py-2 px-3 text-sm">Period of Training To</th>
                                <th class="text-left py-2 px-3 text-sm w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="training-courses-tbody">
                            @php
                                $trainingCourses = old('training_courses', $formData['training_courses'] ?? []);
                                // Ensure at least one row is shown, even if empty
                                if (empty($trainingCourses)) {
                                    $trainingCourses = [['type' => '', 'where' => '', 'from' => '', 'to' => '']];
                                }
                            @endphp
                            @foreach($trainingCourses as $index => $training)
                                @php
                                    $training = array_merge(['type' => '', 'where' => '', 'from' => '', 'to' => ''], $training ?? []);
                                @endphp
                                <tr class="training-row">
                                    <td class="py-2 px-3">
                                        <input type="text" name="training_courses[{{ $index }}][type]" class="kt-input text-sm" 
                                               value="{{ $training['type'] ?? '' }}" placeholder="Training type">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="text" name="training_courses[{{ $index }}][where]" class="kt-input text-sm" 
                                               value="{{ $training['where'] ?? '' }}" placeholder="Location">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="date" name="training_courses[{{ $index }}][from]" class="kt-input text-sm" 
                                               value="{{ $training['from'] ?? '' }}">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="date" name="training_courses[{{ $index }}][to]" class="kt-input text-sm" 
                                               value="{{ $training['to'] ?? '' }}">
                                    </td>
                                    <td class="py-2 px-3">
                                        @if($index > 0)
                                            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-training-row" onclick="removeTrainingRow(this)">
                                                <i class="ki-filled ki-cross"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addTrainingRow()">
                        <i class="ki-filled ki-plus"></i> Add Training Course
                    </button>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label mb-1">Has the past training received by you enhanced your performance and productivity?</label>
                    <textarea name="training_enhanced_performance" class="kt-input" rows="3">{{ old('training_enhanced_performance', $formData['training_enhanced_performance'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- 8. Job Performance -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">8. Job Performance</h4>
                <p class="text-sm text-secondary-foreground italic">Comment on duties performed during this report</p>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(a) Looking back on the past year which jobs assigned to you do you think you have undertaken satisfactory in relation to the tasks/main duties performed during the period of report</label>
                        <textarea name="satisfactory_jobs" class="kt-input" rows="3">{{ old('satisfactory_jobs', $formData['satisfactory_jobs'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(b) What were the causes, personal or otherwise, to which your ascribe you success or failure?</label>
                        <textarea name="success_failure_causes" class="kt-input" rows="3">{{ old('success_failure_causes', $formData['success_failure_causes'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(c) Do you think that you need more training or experience to enable, you do your job better? if so of what kind?</label>
                        <textarea name="training_needs" class="kt-input" rows="3">{{ old('training_needs', $formData['training_needs'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(d) Is the most effective use being made of your capabilities in your present job?</label>
                        <select name="effective_use_capabilities" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('effective_use_capabilities', $formData['effective_use_capabilities'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('effective_use_capabilities', $formData['effective_use_capabilities'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(e) Do you think that your abilities could be better used in your present job or in another kind of job?</label>
                        <textarea name="better_use_abilities" class="kt-input" rows="2">{{ old('better_use_abilities', $formData['better_use_abilities'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(f) During the period of this report did you have job satisfaction, If no, what were the causes?</label>
                        <div class="flex flex-col gap-2">
                            <select name="job_satisfaction" class="kt-input">
                                <option value="">Select...</option>
                                <option value="YES" {{ old('job_satisfaction', $formData['job_satisfaction'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                                <option value="NO" {{ old('job_satisfaction', $formData['job_satisfaction'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                            </select>
                            <textarea name="job_satisfaction_causes" class="kt-input" rows="2" placeholder="If NO, state causes">{{ old('job_satisfaction_causes', $formData['job_satisfaction_causes'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

