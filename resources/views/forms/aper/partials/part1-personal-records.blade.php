<!-- PART 1: PERSONAL RECORDS OF OFFICER -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">PART 1: PERSONAL RECORDS OF OFFICER</h3>
        <p class="text-sm text-secondary-foreground italic">(To be completed by the officer being reported upon)</p>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-5">
            <!-- Period of Report -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-muted/50 rounded-lg">
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label mb-1">Period of Report - From</label>
                    <input type="date" name="period_from" class="kt-input" value="{{ old('period_from', $formData['period_from'] ?? '') }}">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label mb-1">Period of Report - To</label>
                    <input type="date" name="period_to" class="kt-input" value="{{ old('period_to', $formData['period_to'] ?? '') }}">
                </div>
            </div>

            <!-- 1. Service Number -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label mb-1">1. SERVICE NO</label>
                <input type="text" name="service_number" class="kt-input" value="{{ old('service_number', $formData['service_number'] ?? $officer->service_number ?? '') }}" readonly>
            </div>

            <!-- 2. Full Name -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label mb-1">2. Full name of officer (Block Letters) Surname First</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Title</label>
                        <select name="title" class="kt-input">
                            <option value="">Select...</option>
                            <option value="Mr" {{ old('title', $formData['title'] ?? '') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs" {{ old('title', $formData['title'] ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Miss" {{ old('title', $formData['title'] ?? '') == 'Miss' ? 'selected' : '' }}>Miss.</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Surname</label>
                        <input type="text" name="surname" class="kt-input" value="{{ old('surname', $formData['surname'] ?? $officer->surname ?? '') }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Forenames</label>
                        <input type="text" name="forenames" class="kt-input" value="{{ old('forenames', $formData['forenames'] ?? $officer->initials ?? '') }}" readonly>
                    </div>
                </div>
            </div>

            <!-- 3(A). Personal Data -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">3(A). Personal Data</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Department/Area</label>
                        <input type="text" name="department_area" class="kt-input" value="{{ old('department_area', $formData['department_area'] ?? '') }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Cadre (Specify whether GD or SS)</label>
                        <select name="cadre" class="kt-input" readonly>
                            <option value="">Select...</option>
                            <option value="GD" {{ old('cadre', $formData['cadre'] ?? '') == 'GD' ? 'selected' : '' }}>GD</option>
                            <option value="SS" {{ old('cadre', $formData['cadre'] ?? '') == 'SS' ? 'selected' : '' }}>SS</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Unit (For Support Staff)</label>
                        <input type="text" name="unit" class="kt-input" value="{{ old('unit', $formData['unit'] ?? '') }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Zone</label>
                        <input type="text" name="zone" class="kt-input" value="{{ old('zone', $formData['zone'] ?? '') }}" readonly>
                    </div>
                </div>
            </div>

            <!-- 3(B). Personal Particulars -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">3(B). Personal Particulars</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Date of 1st Appointment</label>
                        <input type="date" name="date_of_first_appointment" class="kt-input" value="{{ old('date_of_first_appointment', $formData['date_of_first_appointment'] ?? ($officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('Y-m-d') : '')) }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Date of Present Appointment</label>
                        <input type="date" name="date_of_present_appointment" class="kt-input" value="{{ old('date_of_present_appointment', $formData['date_of_present_appointment'] ?? ($officer->date_of_present_appointment ? $officer->date_of_present_appointment->format('Y-m-d') : '')) }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Rank</label>
                        <input type="text" name="rank" class="kt-input" value="{{ old('rank', $formData['rank'] ?? $officer->substantive_rank ?? '') }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">HAPASS</label>
                        <input type="text" name="hapass" class="kt-input" value="{{ old('hapass', $formData['hapass'] ?? '') }}">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="kt-input" value="{{ old('date_of_birth', $formData['date_of_birth'] ?? ($officer->date_of_birth ? $officer->date_of_birth->format('Y-m-d') : '')) }}" readonly>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label text-sm mb-1">State of Origin</label>
                        <input type="text" name="state_of_origin" class="kt-input" value="{{ old('state_of_origin', $formData['state_of_origin'] ?? $officer->state_of_origin ?? '') }}" readonly>
                    </div>
                </div>
            </div>

            <!-- 3(c) Qualification Held and Year Obtained -->
            <div class="flex flex-col gap-2">
                <label class="kt-form-label font-semibold mb-1">3(c) Qualification Held and Year Obtained</label>
                <p class="text-xs text-secondary-foreground mb-2">Qualifications are automatically fetched from your profile. You can add more below.</p>
                <div class="overflow-x-auto">
                    <table class="kt-table w-full" id="qualifications-table">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Qualification Held (Academic, Professional or Technical)</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Year Obtained</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="qualifications-tbody">
                            @php
                                $qualifications = old('qualifications', $formData['qualifications'] ?? []);
                                if (is_string($qualifications)) {
                                    $qualifications = json_decode($qualifications, true) ?? [];
                                }
                                // Ensure at least 4 rows, but allow more
                                $qualCount = max(4, count($qualifications));
                                for ($i = 0; $i < $qualCount; $i++) {
                                    $qual = $qualifications[$i] ?? ['qualification' => '', 'year' => ''];
                            @endphp
                            <tr class="qualification-row">
                                <td class="py-3 px-4">
                                    <input type="text" name="qualifications[{{ $i }}][qualification]" class="kt-input" 
                                           value="{{ $qual['qualification'] ?? '' }}" placeholder="Enter qualification">
                                </td>
                                <td class="py-3 px-4">
                                    <input type="text" name="qualifications[{{ $i }}][year]" class="kt-input" 
                                           value="{{ $qual['year'] ?? '' }}" placeholder="Year">
                                </td>
                                <td class="py-3 px-4">
                                    @if($i >= 4)
                                        <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-qualification" onclick="removeQualificationRow(this)">
                                            <i class="ki-filled ki-cross"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @php } @endphp
                        </tbody>
                    </table>
                    <div class="mt-2">
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="addQualificationRow()">
                            <i class="ki-filled ki-plus"></i> Add Qualification
                        </button>
                    </div>
                </div>
            </div>

            <script>
            let qualificationIndex = {{ $qualCount }};
            
            function addQualificationRow() {
                const tbody = document.getElementById('qualifications-tbody');
                const row = document.createElement('tr');
                row.className = 'qualification-row';
                row.innerHTML = `
                    <td class="py-3 px-4">
                        <input type="text" name="qualifications[${qualificationIndex}][qualification]" class="kt-input" placeholder="Enter qualification">
                    </td>
                    <td class="py-3 px-4">
                        <input type="text" name="qualifications[${qualificationIndex}][year]" class="kt-input" placeholder="Year">
                    </td>
                    <td class="py-3 px-4">
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-qualification" onclick="removeQualificationRow(this)">
                            <i class="ki-filled ki-cross"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
                qualificationIndex++;
            }
            
            function removeQualificationRow(button) {
                button.closest('tr').remove();
            }
            </script>
        </div>
    </div>
</div>

