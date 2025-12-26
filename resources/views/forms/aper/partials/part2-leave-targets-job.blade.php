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
                
                <!-- (A) Sick Leave -->
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label font-semibold mb-3">(A) Total number of days absent on sick leave during the period covered by Report</label>
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-2 px-3 text-sm">Type</th>
                                    <th class="text-left py-2 px-3 text-sm">From</th>
                                    <th class="text-left py-2 px-3 text-sm">To</th>
                                    <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 3; $i++)
                                    @php
                                        $sickLeave = old('sick_leave_records.'.$i, $formData['sick_leave_records'][$i] ?? ['type' => '', 'from' => '', 'to' => '', 'days' => '']);
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-3">
                                            <select name="sick_leave_records[{{ $i }}][type]" class="kt-input text-sm">
                                                <option value="">Select...</option>
                                                <option value="Hospitalisation" {{ ($sickLeave['type'] ?? '') == 'Hospitalisation' ? 'selected' : '' }}>Hospitalisation</option>
                                                <option value="Treatment Abroad" {{ ($sickLeave['type'] ?? '') == 'Treatment Abroad' ? 'selected' : '' }}>Treatment Received Abroad</option>
                                                <option value="Sick Leave" {{ ($sickLeave['type'] ?? '') == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                            </select>
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="sick_leave_records[{{ $i }}][from]" class="kt-input text-sm" value="{{ $sickLeave['from'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="sick_leave_records[{{ $i }}][to]" class="kt-input text-sm" value="{{ $sickLeave['to'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="sick_leave_records[{{ $i }}][days]" class="kt-input text-sm" value="{{ $sickLeave['days'] ?? '' }}" min="0">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- (B) Maternity Leave -->
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label font-semibold mb-3">(B) Maternity Leave</label>
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-2 px-3 text-sm">From</th>
                                    <th class="text-left py-2 px-3 text-sm">To</th>
                                    <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 2; $i++)
                                    @php
                                        $maternityLeave = old('maternity_leave_records.'.$i, $formData['maternity_leave_records'][$i] ?? ['from' => '', 'to' => '', 'days' => '']);
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-3">
                                            <input type="date" name="maternity_leave_records[{{ $i }}][from]" class="kt-input text-sm" value="{{ $maternityLeave['from'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="maternity_leave_records[{{ $i }}][to]" class="kt-input text-sm" value="{{ $maternityLeave['to'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="maternity_leave_records[{{ $i }}][days]" class="kt-input text-sm" value="{{ $maternityLeave['days'] ?? '' }}" min="0">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- (B) Annual/Casual Leave -->
                <div class="p-4 bg-muted/50 rounded-lg">
                    <label class="kt-form-label font-semibold mb-3">(B) (i) Annual Leave (ii) Casual Leave - Total number of days spent on Annual/Casual Leave</label>
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-2 px-3 text-sm">From</th>
                                    <th class="text-left py-2 px-3 text-sm">To</th>
                                    <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 2; $i++)
                                    @php
                                        $annualLeave = old('annual_casual_leave_records.'.$i, $formData['annual_casual_leave_records'][$i] ?? ['from' => '', 'to' => '', 'days' => '']);
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-3">
                                            <input type="date" name="annual_casual_leave_records[{{ $i }}][from]" class="kt-input text-sm" value="{{ $annualLeave['from'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="date" name="annual_casual_leave_records[{{ $i }}][to]" class="kt-input text-sm" value="{{ $annualLeave['to'] ?? '' }}">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="annual_casual_leave_records[{{ $i }}][days]" class="kt-input text-sm" value="{{ $annualLeave['days'] ?? '' }}" min="0">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 5(A) Target Setting - Division -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">5(A) Target Setting</h4>
                <p class="text-sm text-secondary-foreground">The Chief Executive in consultation with the Director-General and the Directors set out the following targets for my Division/Branch/section to achieve:</p>
                <div class="flex flex-col gap-3">
                    @for($i = 0; $i < 4; $i++)
                        @php
                            $target = old('division_targets.'.$i, $formData['division_targets'][$i] ?? '');
                        @endphp
                        <div>
                            <label class="kt-form-label text-sm">({{ $i == 0 ? 'I' : ($i == 1 ? 'ii' : ($i == 2 ? 'iii' : 'iv')) }})</label>
                            <textarea name="division_targets[{{ $i }}]" class="kt-input" rows="2" placeholder="Enter target">{{ $target }}</textarea>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- 5(B) Target Setting - Individual -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">5(B) Target Setting for the Appraise</h4>
                <p class="text-sm text-secondary-foreground">The head of the Department in consultation with the Head of my Division/Section/Unit set out the following targets for me to achieve:</p>
                <div class="flex flex-col gap-3">
                    @for($i = 0; $i < 5; $i++)
                        @php
                            $target = old('individual_targets.'.$i, $formData['individual_targets'][$i] ?? '');
                        @endphp
                        <div>
                            <label class="kt-form-label text-sm">({{ $i == 0 ? 'I' : ($i == 1 ? 'ii' : ($i == 2 ? 'iii' : ($i == 3 ? 'iv' : 'v'))) }})</label>
                            <textarea name="individual_targets[{{ $i }}]" class="kt-input" rows="2" placeholder="Enter target">{{ $target }}</textarea>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- 5(C) Achievement of Targets -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">5(C) Achievement of Targets</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="kt-form-label">(I) What was the estimated cost of the Project/Assignment/Responsibility set for your Division/Branch/Section?</label>
                        <input type="text" name="project_cost" class="kt-input" value="{{ old('project_cost', $formData['project_cost'] ?? '') }}">
                    </div>
                    <div>
                        <label class="kt-form-label">(ii) What was the agreed time for the completion of the Project/Assignment/Responsibility?</label>
                        <input type="text" name="completion_time" class="kt-input" value="{{ old('completion_time', $formData['completion_time'] ?? '') }}">
                    </div>
                    <div>
                        <label class="kt-form-label">(iii) Was the quantity of work performed during the period of the report in conformity with the set standard?</label>
                        <textarea name="quantity_conformity" class="kt-input" rows="3">{{ old('quantity_conformity', $formData['quantity_conformity'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(iv) Did the quality of the Project/Assignment/Responsibility so far completed agreed with the set standard?</label>
                        <textarea name="quality_conformity" class="kt-input" rows="3">{{ old('quality_conformity', $formData['quality_conformity'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 6. Job Description -->
            <div class="flex flex-col gap-4">
                <h4 class="text-lg font-semibold">(6) Job Description</h4>
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="kt-form-label">(a) State below in order of importance the main duties performed in relation to the targets set during the period of report</label>
                        <textarea name="main_duties" class="kt-input" rows="6" placeholder="Enter main duties">{{ old('main_duties', $formData['main_duties'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(b) Was there any joint discussion between you and your Supervisor on how to accomplish the targets set?</label>
                        <select name="joint_discussion" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('joint_discussion', $formData['joint_discussion'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('joint_discussion', $formData['joint_discussion'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div>
                        <label class="kt-form-label">(c) Were you properly equipped professionally/Technical/ administratively to perform the jobs Allocated to You. YES/NO. If not what were your difficulties or constraints?</label>
                        <div class="flex flex-col gap-2">
                            <select name="properly_equipped" class="kt-input">
                                <option value="">Select...</option>
                                <option value="YES" {{ old('properly_equipped', $formData['properly_equipped'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                                <option value="NO" {{ old('properly_equipped', $formData['properly_equipped'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                            </select>
                            <textarea name="equipment_difficulties" class="kt-input" rows="3" placeholder="If NO, describe difficulties or constraints">{{ old('equipment_difficulties', $formData['equipment_difficulties'] ?? '') }}</textarea>
                        </div>
                    </div>
                    <div>
                        <label class="kt-form-label">(d) In the light of (c) above, state the various difficulties encountered in achieving the set targets and the efforts you and your supervisor put in to rectify them</label>
                        <textarea name="difficulties_encountered" class="kt-input" rows="3">{{ old('difficulties_encountered', $formData['difficulties_encountered'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(e) What were the methods adopted by your supervisor to assist you in solving the difficult problems?</label>
                        <textarea name="supervisor_assistance_methods" class="kt-input" rows="3">{{ old('supervisor_assistance_methods', $formData['supervisor_assistance_methods'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(f) Was there any periodic review of the targets set for you by your Supervisor to achieve the desired Goals? (Three months/Six months) respectively:</label>
                        <input type="text" name="periodic_review" class="kt-input" value="{{ old('periodic_review', $formData['periodic_review'] ?? '') }}" placeholder="e.g., Three months, Six months">
                    </div>
                    <div>
                        <label class="kt-form-label">(g) After the review, did your performance measure up the prescribed standards set at the beginning of the year?</label>
                        <select name="performance_measure_up" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('performance_measure_up', $formData['performance_measure_up'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('performance_measure_up', $formData['performance_measure_up'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div>
                        <label class="kt-form-label">(h) If the answer to (g) above is No, state what solution or admonition was given for the shortcomings:</label>
                        <textarea name="solution_admonition" class="kt-input" rows="3">{{ old('solution_admonition', $formData['solution_admonition'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(i) Was there any final evaluation of the entire targets at the beginning of the year to evaluate the total Accomplishment of the goals set for your Division/Branch/Section in relation to the achievements of your Ministry/Department's programme for the year?</label>
                        <textarea name="final_evaluation" class="kt-input" rows="3">{{ old('final_evaluation', $formData['final_evaluation'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(j) State any adhoc duties performed by you in addition to your normal schedule of duties which were not of a continuous nature</label>
                        <textarea name="adhoc_duties" class="kt-input" rows="3">{{ old('adhoc_duties', $formData['adhoc_duties'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="kt-form-label">(k) Did the performance of these ad hoc duties affect your real duties and if so, did you bring these to the attention of your supervisor?</label>
                        <select name="adhoc_affected_duties" class="kt-input">
                            <option value="">Select...</option>
                            <option value="YES" {{ old('adhoc_affected_duties', $formData['adhoc_affected_duties'] ?? '') == 'YES' ? 'selected' : '' }}>YES</option>
                            <option value="NO" {{ old('adhoc_affected_duties', $formData['adhoc_affected_duties'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="kt-form-label">(l) State the period that you have been on the schedule of duty referred to in (a) above: From</label>
                            <input type="date" name="schedule_duty_from" class="kt-input" value="{{ old('schedule_duty_from', $formData['schedule_duty_from'] ?? '') }}">
                        </div>
                        <div>
                            <label class="kt-form-label">To</label>
                            <input type="date" name="schedule_duty_to" class="kt-input" value="{{ old('schedule_duty_to', $formData['schedule_duty_to'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

