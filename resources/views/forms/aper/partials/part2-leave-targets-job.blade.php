<!-- PART 2: Leave Records, Target Setting, Job Description -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">PART 2: Leave Records, Target Setting & Job Description</h3>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-5">
            <!-- 4. Leave Records -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">4. Leave Records</h4>
                <p class="text-sm text-secondary-foreground italic mb-2">Leave records are automatically fetched for {{ $activeTimeline->year ?? date('Y') }}. You can review and edit if needed.</p>
                
                <!-- (A) Sick Leave -->
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label font-semibold mb-3">(A) Total number of days absent on sick leave during the period covered by Report</label>
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full" id="sick-leave-table">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-2 px-3 text-sm">Type</th>
                                    <th class="text-left py-2 px-3 text-sm">From</th>
                                    <th class="text-left py-2 px-3 text-sm">To</th>
                                    <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                    <th class="text-left py-2 px-3 text-sm w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sick-leave-tbody">
                                @php
                                    $sickLeaveRecords = old('sick_leave_records', $formData['sick_leave_records'] ?? []);
                                    // Ensure at least one row is shown, even if empty
                                    if (empty($sickLeaveRecords)) {
                                        $sickLeaveRecords = [['type' => '', 'from' => '', 'to' => '', 'days' => '']];
                                    }
                                @endphp
                                @foreach($sickLeaveRecords as $index => $sickLeave)
                                    @php
                                        $sickLeave = array_merge(['type' => '', 'from' => '', 'to' => '', 'days' => ''], $sickLeave ?? []);
                                    @endphp
                                    <tr class="leave-row">
                                        <td class="py-2 px-3">
                                            <select name="sick_leave_records[{{ $index }}][type]" class="kt-input text-sm">
                                                <option value="">Select...</option>
                                                <option value="Hospitalisation" {{ ($sickLeave['type'] ?? '') == 'Hospitalisation' ? 'selected' : '' }}>Hospitalisation</option>
                                                <option value="Treatment Abroad" {{ ($sickLeave['type'] ?? '') == 'Treatment Abroad' ? 'selected' : '' }}>Treatment Received Abroad</option>
                                                <option value="Sick Leave" {{ ($sickLeave['type'] ?? '') == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                            </select>
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="sick_leave_records[{{ $index }}][from]" class="kt-input text-sm" value="{{ $sickLeave['from'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="sick_leave_records[{{ $index }}][to]" class="kt-input text-sm" value="{{ $sickLeave['to'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="sick_leave_records[{{ $index }}][days]" class="kt-input text-sm" value="{{ $sickLeave['days'] ?? '' }}" min="0">
                                        </td>
                                        <td class="py-2 px-3">
                                            @if($index > 0)
                                                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-leave-row" onclick="removeLeaveRow(this, 'sick')">
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
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addLeaveRow('sick')">
                            <i class="ki-filled ki-plus"></i> Add Sick Leave Record
                        </button>
                    </div>
                </div>

                <!-- (B) Maternity Leave -->
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label font-semibold mb-3">(B) Maternity Leave</label>
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full" id="maternity-leave-table">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-2 px-3 text-sm">From</th>
                                    <th class="text-left py-2 px-3 text-sm">To</th>
                                    <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                    <th class="text-left py-2 px-3 text-sm w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="maternity-leave-tbody">
                                @php
                                    $maternityLeaveRecords = old('maternity_leave_records', $formData['maternity_leave_records'] ?? []);
                                    // Ensure at least one row is shown, even if empty
                                    if (empty($maternityLeaveRecords)) {
                                        $maternityLeaveRecords = [['from' => '', 'to' => '', 'days' => '']];
                                    }
                                @endphp
                                @foreach($maternityLeaveRecords as $index => $maternityLeave)
                                    @php
                                        $maternityLeave = array_merge(['from' => '', 'to' => '', 'days' => ''], $maternityLeave ?? []);
                                    @endphp
                                    <tr class="leave-row">
                                        <td class="py-2 px-3">
                                            <input type="date" name="maternity_leave_records[{{ $index }}][from]" class="kt-input text-sm" value="{{ $maternityLeave['from'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="maternity_leave_records[{{ $index }}][to]" class="kt-input text-sm" value="{{ $maternityLeave['to'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="maternity_leave_records[{{ $index }}][days]" class="kt-input text-sm" value="{{ $maternityLeave['days'] ?? '' }}" min="0">
                                        </td>
                                        <td class="py-2 px-3">
                                            @if($index > 0)
                                                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-leave-row" onclick="removeLeaveRow(this, 'maternity')">
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
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addLeaveRow('maternity')">
                            <i class="ki-filled ki-plus"></i> Add Maternity Leave Record
                        </button>
                    </div>
                </div>

                <!-- (B) Annual/Casual Leave -->
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label font-semibold mb-3">(B) (i) Annual Leave (ii) Casual Leave - Total number of days spent on Annual/Casual Leave</label>
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full" id="annual-leave-table">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-2 px-3 text-sm">From</th>
                                    <th class="text-left py-2 px-3 text-sm">To</th>
                                    <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                    <th class="text-left py-2 px-3 text-sm w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="annual-leave-tbody">
                                @php
                                    $annualLeaveRecords = old('annual_casual_leave_records', $formData['annual_casual_leave_records'] ?? []);
                                    // Ensure at least one row is shown, even if empty
                                    if (empty($annualLeaveRecords)) {
                                        $annualLeaveRecords = [['from' => '', 'to' => '', 'days' => '']];
                                    }
                                @endphp
                                @foreach($annualLeaveRecords as $index => $annualLeave)
                                    @php
                                        $annualLeave = array_merge(['from' => '', 'to' => '', 'days' => ''], $annualLeave ?? []);
                                    @endphp
                                    <tr class="leave-row">
                                        <td class="py-2 px-3">
                                            <input type="date" name="annual_casual_leave_records[{{ $index }}][from]" class="kt-input text-sm" value="{{ $annualLeave['from'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="annual_casual_leave_records[{{ $index }}][to]" class="kt-input text-sm" value="{{ $annualLeave['to'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="annual_casual_leave_records[{{ $index }}][days]" class="kt-input text-sm" value="{{ $annualLeave['days'] ?? '' }}" min="0">
                                        </td>
                                        <td class="py-2 px-3">
                                            @if($index > 0)
                                                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-leave-row" onclick="removeLeaveRow(this, 'annual')">
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
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addLeaveRow('annual')">
                            <i class="ki-filled ki-plus"></i> Add Annual/Casual Leave Record
                        </button>
                    </div>
                </div>
            </div>

            <!-- 5(A) Target Setting - Division -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">5(A) Target Setting</h4>
                <p class="text-sm text-secondary-foreground">The Chief Executive in consultation with the Director-General and the Directors set out the following targets for my Division/Branch/section to achieve:</p>
                <div class="flex flex-col gap-3" id="division-targets-container">
                    @php
                        $divisionTargets = old('division_targets', $formData['division_targets'] ?? []);
                        // Ensure at least one row is shown, even if empty
                        if (empty($divisionTargets)) {
                            $divisionTargets = [''];
                        }
                        $divisionTargetCount = count($divisionTargets);
                    @endphp
                    @foreach($divisionTargets as $index => $target)
                        @php
                            $target = $target ?? '';
                        @endphp
                        <div class="target-row flex items-start gap-3">
                            <label class="kt-form-label text-sm pt-2 min-w-[40px]">
                                (@php
                                    $romanNumerals = ['I', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'];
                                    echo $romanNumerals[$index] ?? ($index + 1);
                                @endphp)
                            </label>
                            <div class="flex-1">
                                <textarea name="division_targets[{{ $index }}]" class="kt-input" rows="2" placeholder="Enter target">{{ $target }}</textarea>
                            </div>
                            @if($index > 0)
                                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger mt-2 remove-target-row" onclick="removeTargetRow(this, 'division')">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addTargetRow('division')">
                        <i class="ki-filled ki-plus"></i> Add Target
                    </button>
                </div>
            </div>

            <!-- 5(B) Target Setting - Individual -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">5(B) Target Setting for the Appraise</h4>
                <p class="text-sm text-secondary-foreground">The head of the Department in consultation with the Head of my Division/Section/Unit set out the following targets for me to achieve:</p>
                <div class="flex flex-col gap-3" id="individual-targets-container">
                    @php
                        $individualTargets = old('individual_targets', $formData['individual_targets'] ?? []);
                        // Ensure at least one row is shown, even if empty
                        if (empty($individualTargets)) {
                            $individualTargets = [''];
                        }
                        $individualTargetCount = count($individualTargets);
                    @endphp
                    @foreach($individualTargets as $index => $target)
                        @php
                            $target = $target ?? '';
                        @endphp
                        <div class="target-row flex items-start gap-3">
                            <label class="kt-form-label text-sm pt-2 min-w-[40px]">
                                (@php
                                    $romanNumerals = ['I', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'];
                                    echo $romanNumerals[$index] ?? ($index + 1);
                                @endphp)
                            </label>
                            <div class="flex-1">
                                <textarea name="individual_targets[{{ $index }}]" class="kt-input" rows="2" placeholder="Enter target">{{ $target }}</textarea>
                            </div>
                            @if($index > 0)
                                <button type="button" class="kt-btn kt-btn-sm kt-btn-danger mt-2 remove-target-row" onclick="removeTargetRow(this, 'individual')">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addTargetRow('individual')">
                        <i class="ki-filled ki-plus"></i> Add Target
                    </button>
                </div>
            </div>

            <!-- 5(C) Achievement of Targets -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">5(C) Achievement of Targets</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(I) What was the estimated cost of the Project/Assignment/Responsibility set for your Division/Branch/Section?</label>
                        <input type="text" name="project_cost" class="kt-input" value="{{ old('project_cost', $formData['project_cost'] ?? '') }}">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(ii) What was the agreed time for the completion of the Project/Assignment/Responsibility?</label>
                        <input type="text" name="completion_time" class="kt-input" value="{{ old('completion_time', $formData['completion_time'] ?? '') }}">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(iii) Was the quantity of work performed during the period of the report in conformity with the set standard?</label>
                        <textarea name="quantity_conformity" class="kt-input" rows="3">{{ old('quantity_conformity', $formData['quantity_conformity'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(iv) Did the quality of the Project/Assignment/Responsibility so far completed agreed with the set standard?</label>
                        <textarea name="quality_conformity" class="kt-input" rows="3">{{ old('quality_conformity', $formData['quality_conformity'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 6. Job Description -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">(6) Job Description</h4>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(a) State below in order of importance the main duties performed in relation to the targets set during the period of report</label>
                        <textarea name="main_duties" class="kt-input" rows="6" placeholder="Enter main duties">{{ old('main_duties', $formData['main_duties'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(b) Was there any joint discussion between you and your Supervisor on how to accomplish the targets set?</label>
                        <select name="joint_discussion" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('joint_discussion', $formData['joint_discussion'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('joint_discussion', $formData['joint_discussion'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(c) Were you properly equipped professionally/Technical/ administratively to perform the jobs Allocated to You. YES/NO. If not what were your difficulties or constraints?</label>
                        <div class="flex flex-col gap-2">
                            <select name="properly_equipped" class="kt-input">
                                <option value="">Select...</option>
                                <option value="YES" {{ old('properly_equipped', $formData['properly_equipped'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                                <option value="NO" {{ old('properly_equipped', $formData['properly_equipped'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                            </select>
                            <textarea name="equipment_difficulties" class="kt-input" rows="3" placeholder="If NO, describe difficulties or constraints">{{ old('equipment_difficulties', $formData['equipment_difficulties'] ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(d) In the light of (c) above, state the various difficulties encountered in achieving the set targets and the efforts you and your supervisor put in to rectify them</label>
                        <textarea name="difficulties_encountered" class="kt-input" rows="3">{{ old('difficulties_encountered', $formData['difficulties_encountered'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(e) What were the methods adopted by your supervisor to assist you in solving the difficult problems?</label>
                        <textarea name="supervisor_assistance_methods" class="kt-input" rows="3">{{ old('supervisor_assistance_methods', $formData['supervisor_assistance_methods'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(f) Was there any periodic review of the targets set for you by your Supervisor to achieve the desired Goals? (Three months/Six months) respectively:</label>
                        <input type="text" name="periodic_review" class="kt-input" value="{{ old('periodic_review', $formData['periodic_review'] ?? '') }}" placeholder="e.g., Three months, Six months">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(g) After the review, did your performance measure up the prescribed standards set at the beginning of the year?</label>
                        <select name="performance_measure_up" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('performance_measure_up', $formData['performance_measure_up'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('performance_measure_up', $formData['performance_measure_up'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(h) If the answer to (g) above is No, state what solution or admonition was given for the shortcomings:</label>
                        <textarea name="solution_admonition" class="kt-input" rows="3">{{ old('solution_admonition', $formData['solution_admonition'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(i) Was there any final evaluation of the entire targets at the beginning of the year to evaluate the total Accomplishment of the goals set for your Division/Branch/Section in relation to the achievements of your Ministry/Department's programme for the year?</label>
                        <textarea name="final_evaluation" class="kt-input" rows="3">{{ old('final_evaluation', $formData['final_evaluation'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(j) State any adhoc duties performed by you in addition to your normal schedule of duties which were not of a continuous nature</label>
                        <textarea name="adhoc_duties" class="kt-input" rows="3">{{ old('adhoc_duties', $formData['adhoc_duties'] ?? '') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(k) Did the performance of these ad hoc duties affect your real duties and if so, did you bring these to the attention of your supervisor?</label>
                        <select name="adhoc_affected_duties" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('adhoc_affected_duties', $formData['adhoc_affected_duties'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('adhoc_affected_duties', $formData['adhoc_affected_duties'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label mb-1">(l) State the period that you have been on the schedule of duty referred to in (a) above: From</label>
                            <input type="date" name="schedule_duty_from" class="kt-input" value="{{ old('schedule_duty_from', $formData['schedule_duty_from'] ?? '') }}">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="kt-form-label mb-1">To</label>
                            <input type="date" name="schedule_duty_to" class="kt-input" value="{{ old('schedule_duty_to', $formData['schedule_duty_to'] ?? '') }}">
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label mb-1">(m) I have served for over 6 months under: (I) Mr/Mrs.</label>
                        <input type="text" name="served_under_supervisor" class="kt-input" value="{{ old('served_under_supervisor', $formData['served_under_supervisor'] ?? '') }}" placeholder="Enter supervisor name">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

