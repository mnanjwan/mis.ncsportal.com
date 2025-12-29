@extends('layouts.app')

@section('title', 'Onboarding - Step 2: Employment Details')
@section('page-title', 'Onboarding - Step 2: Employment Details')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-4 lg:p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-2">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold text-sm">✓</div>
                    <span class="text-xs sm:text-sm text-success">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm">2</div>
                    <span class="text-xs sm:text-sm font-medium">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold text-sm">3</div>
                    <span class="text-xs sm:text-sm text-secondary-foreground">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold text-sm">4</div>
                    <span class="text-xs sm:text-sm text-secondary-foreground">Next of Kin</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Employment Details</h3>
        </div>
        <div class="kt-card-content">
            @if($errors->any())
            <div class="kt-alert kt-alert-danger mb-5">
                <div class="kt-alert-content">
                    <strong class="text-danger">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="onboarding-step2-form" method="POST" action="{{ route('onboarding.step2.save') }}" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of First Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_first_appointment" class="kt-input" value="{{ old('date_of_first_appointment', $savedData['date_of_first_appointment'] ?? '') }}" required/>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Present Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_present_appointment" class="kt-input" value="{{ old('date_of_present_appointment', $savedData['date_of_present_appointment'] ?? '') }}" required/>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Substantive Rank <span class="text-danger">*</span></label>
                        <select name="substantive_rank" id="substantive_rank" class="kt-input" required>
                            <option value="">Select Rank...</option>
                            @php
                                $ranks = ['CGC', 'DCG', 'ACG', 'CC', 'DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I', 'CA II', 'CA III'];
                            @endphp
                            @foreach($ranks as $rank)
                                <option value="{{ $rank }}" {{ old('substantive_rank', $savedData['substantive_rank'] ?? '') == $rank ? 'selected' : '' }}>{{ $rank }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Salary Grade Level <span class="text-danger">*</span></label>
                        <select name="salary_grade_level" id="salary_grade_level" class="kt-input" required>
                            <option value="">Select Grade Level...</option>
                            @php
                                $gradeLevels = ['GL 03', 'GL 04', 'GL 05', 'GL 06', 'GL 07', 'GL 08', 'GL 09', 'GL 10', 'GL 11', 'GL 12', 'GL 13', 'GL 14', 'GL 15', 'GL 16', 'GL 17', 'GL 18'];
                            @endphp
                            @foreach($gradeLevels as $gl)
                                <option value="{{ $gl }}" {{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') == $gl ? 'selected' : '' }}>{{ $gl }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Zone <span class="text-danger">*</span></label>
                        <select name="zone_id" id="zone_id" class="kt-input" required>
                            <option value="">Select Zone...</option>
                        </select>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Command/Present Station <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   id="command_search" 
                                   class="kt-input w-full" 
                                   placeholder="Select zone first, then search command..."
                                   autocomplete="off"
                                   readonly>
                            <input type="hidden" 
                                   name="command_id" 
                                   id="command_id" 
                                   value="{{ old('command_id', $savedData['command_id'] ?? '') }}"
                                   required>
                            <div id="command_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                <!-- Options will be populated by JavaScript -->
                            </div>
                        </div>
                        <div id="selected_command" class="mt-2 p-2 bg-muted/50 rounded-lg hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" id="selected_command_name"></span>
                                <button type="button" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger"
                                        onclick="clearCommandSelection()">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                    </div>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date Posted to Station <span class="text-danger">*</span></label>
                        <input type="date" name="date_posted_to_station" class="kt-input" value="{{ old('date_posted_to_station', $savedData['date_posted_to_station'] ?? '') }}" required/>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Unit</label>
                        <select name="unit" class="kt-input">
                            <option value="">Select Unit...</option>
                            <option value="General Duty (GD)" {{ old('unit', $savedData['unit'] ?? '') == 'General Duty (GD)' ? 'selected' : '' }}>General Duty (GD)</option>
                            <option value="Support Staff (SS)" {{ old('unit', $savedData['unit'] ?? '') == 'Support Staff (SS)' ? 'selected' : '' }}>Support Staff (SS)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Education Section -->
                <div class="flex flex-col gap-5 pt-5 border-t border-input">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Education</h3>
                        <button type="button" id="add-education-btn" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Add Education
                        </button>
                    </div>
                    
                    <div id="education-entries" class="flex flex-col gap-5">
                        <!-- Education entries will be added here dynamically -->
                    </div>
                </div>
                
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step1') }}'" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</button>
                    <button type="submit" class="kt-btn kt-btn-primary w-full sm:flex-1 whitespace-nowrap">Next: Banking Information</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure all asterisks in onboarding forms are red */
    .kt-form-label span.text-danger,
    .kt-form-label .text-danger,
    label span.text-danger,
    label .text-danger {
        color: #dc3545 !important;
    }
    
    /* Error messages should be red only when visible (not hidden) */
    .error-message:not(.hidden) {
        color: #dc3545 !important;
    }
    
    /* Laravel validation errors */
    .kt-alert-danger,
    .kt-alert-danger strong,
    .kt-alert-danger li,
    .kt-alert-danger p {
        color: #dc3545 !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Load zones and commands
    const token = window.API_CONFIG?.token || '{{ auth()->user()?->createToken('token')->plainTextToken ?? '' }}';
    const savedZoneId = '{{ old('zone_id', $savedData['zone_id'] ?? '') }}';
    const savedCommandId = '{{ old('command_id', $savedData['command_id'] ?? '') }}';
    
    try {
        // Load zones first
        const zonesRes = await fetch('/api/v1/zones', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        // Load all commands with zone information
        const commandsRes = await fetch('/api/v1/commands', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (zonesRes.ok && commandsRes.ok) {
            const zonesData = await zonesRes.json();
            const commandsData = await commandsRes.json();
            
            // Populate zones dropdown
            const zones = zonesData.data || zonesData; // Handle both response formats
            if (zones && Array.isArray(zones)) {
                const zoneSelect = document.getElementById('zone_id');
                zones.forEach(zone => {
                    const option = document.createElement('option');
                    option.value = zone.id;
                    option.textContent = zone.name;
                    if (zone.id == savedZoneId) {
                        option.selected = true;
                    }
                    zoneSelect.appendChild(option);
                });
            } else {
                console.error('Zones data format error:', zonesData);
            }
            
            // Store all commands with zone info
            const commands = commandsData.data || commandsData; // Handle both response formats
            if (commands && Array.isArray(commands)) {
                window.allCommands = commands.map(cmd => ({
                    id: cmd.id,
                    name: cmd.name,
                    zone_id: cmd.zone_id || (cmd.zone ? cmd.zone.id : null)
                }));
                
                // If saved zone exists, load commands for that zone
                if (savedZoneId) {
                    // Wait a bit for zone select to be populated
                    setTimeout(() => {
                        loadCommandsForZone(savedZoneId, savedCommandId);
                    }, 100);
                }
                
                // Handle zone change
                const zoneSelect = document.getElementById('zone_id');
                zoneSelect.addEventListener('change', function() {
                    const selectedZoneId = this.value;
                    if (selectedZoneId) {
                        loadCommandsForZone(selectedZoneId);
                    } else {
                        clearCommandSelection();
                    }
                });
            } else {
                console.error('Commands data format error:', commandsData);
            }
        } else {
            console.error('Error loading zones or commands:', {
                zones: zonesRes.status,
                commands: commandsRes.status,
                zonesText: await zonesRes.text().catch(() => ''),
                commandsText: await commandsRes.text().catch(() => '')
            });
        }
    } catch (error) {
        console.error('Error loading zones/commands:', error);
    }
    
    // Initialize education entries
    initializeEducationSection();
    
    // Initialize rank to grade level auto-mapping
    initializeRankGradeMapping();
});

// Nigerian Institutions List (Universities and other institutions)
const nigerianUniversities = [
    'University of Lagos (UNILAG)',
    'University of Ibadan (UI)',
    'Ahmadu Bello University (ABU)',
    'University of Nigeria, Nsukka (UNN)',
    'Obafemi Awolowo University (OAU)',
    'University of Benin (UNIBEN)',
    'University of Ilorin (UNILORIN)',
    'University of Port Harcourt (UNIPORT)',
    'University of Calabar (UNICAL)',
    'University of Jos (UNIJOS)',
    'University of Maiduguri (UNIMAID)',
    'University of Uyo (UNIUYO)',
    'Nnamdi Azikiwe University (UNIZIK)',
    'Federal University of Technology, Akure (FUTA)',
    'Federal University of Technology, Minna (FUTMINNA)',
    'Federal University of Technology, Owerri (FUTO)',
    'Federal University of Agriculture, Abeokuta (FUNAAB)',
    'Federal University of Agriculture, Makurdi (FUAM)',
    'Federal University of Petroleum Resources, Effurun (FUPRE)',
    'Lagos State University (LASU)',
    'Rivers State University (RSU)',
    'Delta State University (DELSU)',
    'Enugu State University of Science and Technology (ESUT)',
    'Abia State University (ABSU)',
    'Imo State University (IMSU)',
    'Anambra State University (ANSU)',
    'Bayelsa Medical University (BMU)',
    'Benue State University (BSU)',
    'Cross River University of Technology (CRUTECH)',
    'Ebonyi State University (EBSU)',
    'Ekiti State University (EKSU)',
    'Kaduna State University (KASU)',
    'Kano University of Science and Technology (KUST)',
    'Kebbi State University of Science and Technology (KSUSTA)',
    'Kwara State University (KWASU)',
    'Nasarawa State University (NSUK)',
    'Ondo State University of Science and Technology (OSUSTECH)',
    'Osun State University (UNIOSUN)',
    'Plateau State University (PLASU)',
    'Sokoto State University (SSU)',
    'Taraba State University (TSU)',
    'Yobe State University (YSU)',
    'Zamfara State University (ZASU)',
    'Covenant University',
    'Babcock University',
    'Afe Babalola University (ABUAD)',
    'American University of Nigeria (AUN)',
    'Bells University of Technology',
    'Benson Idahosa University',
    'Bingham University',
    'Bowen University',
    'Caleb University',
    'Caritas University',
    'Crawford University',
    'Crescent University',
    'Edwin Clark University',
    'Elizade University',
    'Evangel University',
    'Fountain University',
    'Godfrey Okoye University',
    'Gregory University',
    'Hallmark University',
    'Hezekiah University',
    'Igbinedion University',
    'Joseph Ayo Babalola University',
    'Kings University',
    'Kwararafa University',
    'Landmark University',
    'Lead City University',
    'Madonna University',
    'McPherson University',
    'Michael Okpara University of Agriculture, Umudike',
    'Nile University of Nigeria',
    'Novena University',
    'Obong University',
    'Oduduwa University',
    'Pan-Atlantic University',
    'Paul University',
    'Redeemer\'s University',
    'Rhema University',
    'Ritman University',
    'Salem University',
    'Samuel Adegboyega University',
    'Southwestern University',
    'Summit University',
    'Tansian University',
    'University of Mkar',
    'Veritas University',
    'Wesley University',
    'Western Delta University',
    // Benin Republic Universities
    'University of Abomey-Calavi (UAC)',
    'University of Parakou',
    'National University of Sciences, Technologies, Engineering, and Mathematics (UNSTIM)',
    'National University of Agriculture (UNA)',
    'African School of Economics (ASE)',
    'ESAE University (École Supérieure d\'Administration, d\'Économie, de Journalisme et des Métiers de l\'Audiovisuel)',
    'ESCAE-University, Benin',
    'ISFOP Benin University',
    'Houdegbe North American University Benin (HNAUB)',
    'Université Catholique de l\'Afrique de l\'Ouest (UCAO)',
    'Université des Sciences et Technologies du Bénin',
    'Université Africaine de Technologie et de Management',
    'Université Protestante de l\'Afrique de l\'Ouest',
    'Université Polytechnique Internationale du Bénin',
    'Université des Sciences Appliquées et du Management',
    'Other'
];

// Qualifications List - Entry Qualifications prioritized
const qualifications = [
    'WAEC',
    'NECO',
    'NABTEB',
    'HND',
    'OND',
    'PhD',
    'MBBS',
    'MSc',
    'MPhil',
    'MA',
    'B TECH',
    'BA',
    'BSc',
    'TRADE TEST',
    'DSc',
    'DPharm',
    'D Litt',
    'DDS',
    'DA',
    'MMed',
    'MEng',
    'BArch',
    'LLM',
    'LLB',
    'MBA',
    'BEd',
    'BPharm',
    'BVSc',
    'DVM',
    'BDS',
    'BEng',
    'BTech',
    'BBA',
    'BCom',
    'BFA',
    'BPE',
    'BSc (Ed)',
    'PGD',
    'PGDE',
    'Other'
];

// Comprehensive Disciplines List
const disciplines = [
    'Accounting',
    'Actuarial Science',
    'Agricultural Economics',
    'Agricultural Engineering',
    'Agricultural Extension',
    'Agriculture',
    'Anatomy',
    'Animal Science',
    'Architecture',
    'Banking and Finance',
    'Biochemistry',
    'Biology',
    'Biomedical Engineering',
    'Botany',
    'Business Administration',
    'Chemical Engineering',
    'Chemistry',
    'Civil Engineering',
    'Computer Engineering',
    'Computer Science',
    'Criminology',
    'Crop Science',
    'Dentistry',
    'Economics',
    'Education',
    'Electrical Engineering',
    'English Language',
    'Environmental Science',
    'Estate Management',
    'Finance',
    'Fisheries',
    'Food Science and Technology',
    'Forestry',
    'Geography',
    'Geology',
    'History',
    'Human Resource Management',
    'Industrial Chemistry',
    'Information Technology',
    'Law',
    'Library Science',
    'Linguistics',
    'Marine Engineering',
    'Marketing',
    'Mass Communication',
    'Mathematics',
    'Mechanical Engineering',
    'Medicine and Surgery',
    'Microbiology',
    'Nursing',
    'Petroleum Engineering',
    'Pharmacy',
    'Philosophy',
    'Physics',
    'Political Science',
    'Psychology',
    'Public Administration',
    'Quantity Surveying',
    'Sociology',
    'Soil Science',
    'Statistics',
    'Surveying and Geoinformatics',
    'Veterinary Medicine',
    'Zoology',
    'Agricultural Science',
    'Animal Husbandry',
    'Building Technology',
    'Business Management',
    'Chemical Science',
    'Communication Arts',
    'Computer Education',
    'Crop Production',
    'Economics and Statistics',
    'Educational Administration',
    'Educational Psychology',
    'Electronics Engineering',
    'Environmental Management',
    'Food Technology',
    'Geophysics',
    'Guidance and Counseling',
    'Health Education',
    'Home Economics',
    'Human Kinetics',
    'Industrial Mathematics',
    'Insurance',
    'International Relations',
    'Journalism',
    'Laboratory Technology',
    'Land Surveying',
    'Management',
    'Marine Science',
    'Materials Science',
    'Mechanical Engineering Technology',
    'Medical Laboratory Science',
    'Metallurgical Engineering',
    'Nutrition and Dietetics',
    'Office Technology and Management',
    'Operations Research',
    'Optometry',
    'Peace and Conflict Studies',
    'Petroleum and Gas Engineering',
    'Physics with Electronics',
    'Plant Science',
    'Project Management',
    'Public Health',
    'Pure and Applied Mathematics',
    'Radiography',
    'Real Estate Management',
    'Religious Studies',
    'Science Education',
    'Social Work',
    'Software Engineering',
    'Soil Science and Land Management',
    'Statistics and Computer Science',
    'Telecommunications Engineering',
    'Textile Technology',
    'Transport Management',
    'Urban and Regional Planning',
    'Water Resources Engineering',
    'Wildlife Management'
];

let educationEntryCount = 0;

function initializeEducationSection() {
    const addBtn = document.getElementById('add-education-btn');
    const entriesContainer = document.getElementById('education-entries');
    
    // Load saved education entries
    const savedEducation = @json(old('education', $savedData['education'] ?? []));
    
    if (savedEducation && Array.isArray(savedEducation) && savedEducation.length > 0) {
        // Handle both array of objects and object with numeric keys
        const educationArray = Array.isArray(savedEducation) 
            ? savedEducation 
            : Object.values(savedEducation);
            
        educationArray.forEach(edu => {
            if (edu && (edu.university || edu.qualification)) {
                addEducationEntry(edu);
            }
        });
    }
    
    // If no saved entries, add one empty entry by default
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
    
    addBtn.addEventListener('click', () => {
        addEducationEntry();
    });
}

function addEducationEntry(data = null) {
    const entriesContainer = document.getElementById('education-entries');
    const entryId = educationEntryCount++;
    
    const entryDiv = document.createElement('div');
    entryDiv.className = 'kt-card p-5 border border-input rounded-lg';
    entryDiv.dataset.entryId = entryId;
    
    const savedUniversity = data && data.university ? data.university : '';
    const savedQualification = data && data.qualification ? data.qualification : '';
    const savedDiscipline = data && data.discipline ? data.discipline : '';
    const savedYearObtained = data && data.year_obtained ? data.year_obtained : '';
    
    entryDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Institution <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="text" 
                           name="education[${entryId}][university]" 
                           id="university_search_${entryId}"
                           class="kt-input w-full education-university" 
                           value="${savedUniversity}"
                           placeholder="Search or type institution name..."
                           autocomplete="off"
                           required>
                    <input type="hidden" 
                           id="university_hidden_${entryId}"
                           value="${savedUniversity}">
                    <div id="university_dropdown_${entryId}" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        <!-- Options will be populated by JavaScript -->
                    </div>
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Entry Qualification <span class="text-danger">*</span></label>
                <select name="education[${entryId}][qualification]" class="kt-input education-qualification" required>
                    <option value="">-- Select Qualification --</option>
                    ${qualifications.map(qual => 
                        `<option value="${qual}" ${savedQualification == qual ? 'selected' : ''}>${qual}</option>`
                    ).join('')}
                </select>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Year Obtained <span class="text-danger">*</span></label>
                <input type="number" 
                       name="education[${entryId}][year_obtained]" 
                       class="kt-input education-year-obtained" 
                       value="${savedYearObtained}"
                       placeholder="e.g., 2020"
                       min="1950"
                       max="${new Date().getFullYear()}"
                       required>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Discipline <span class="text-muted">(Optional)</span></label>
                <div class="relative">
                    <input type="text" 
                           id="discipline_search_${entryId}"
                           class="kt-input w-full education-discipline-search" 
                           value="${savedDiscipline}"
                           placeholder="Search or type discipline..."
                           autocomplete="off">
                    <input type="hidden" 
                           id="discipline_hidden_${entryId}"
                           name="education[${entryId}][discipline]"
                           value="${savedDiscipline}">
                    <div id="discipline_dropdown_${entryId}" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        <!-- Options will be populated by JavaScript -->
                    </div>
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
        </div>
        <div class="flex items-center justify-end mt-3">
            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-education-btn" onclick="removeEducationEntry(${entryId})">
                <i class="ki-filled ki-trash"></i> Remove
            </button>
        </div>
    `;
    
    entriesContainer.appendChild(entryDiv);
    
    // Initialize university search for this entry
    initializeUniversitySearch(entryId);
    
    // Initialize discipline search for this entry
    initializeDisciplineSearch(entryId);
}

function removeEducationEntry(entryId) {
    const entry = document.querySelector(`[data-entry-id="${entryId}"]`);
    if (entry) {
        entry.remove();
    }
    
    // If no entries left, add one
    const entriesContainer = document.getElementById('education-entries');
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
}

// Initialize university search functionality
function initializeUniversitySearch(entryId) {
    const universityInput = document.getElementById(`university_search_${entryId}`);
    const universityDropdown = document.getElementById(`university_dropdown_${entryId}`);
    
    if (!universityInput || !universityDropdown) return;
    
    universityInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            universityDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = nigerianUniversities.filter(uni => 
            uni.toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0) {
            universityDropdown.innerHTML = filtered.map(uni => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-value="' + uni + '">' + uni + '</div>'
            ).join('');
            universityDropdown.classList.remove('hidden');
        } else {
            universityDropdown.classList.add('hidden');
        }
    });
    
    // Handle selection from dropdown
    universityDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            universityInput.value = selectedValue;
            universityDropdown.classList.add('hidden');
            clearError(`education[${entryId}][university]`);
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!universityInput.contains(e.target) && !universityDropdown.contains(e.target)) {
            universityDropdown.classList.add('hidden');
        }
    });
    
    // Allow free text input (user can type custom university)
    universityInput.addEventListener('blur', function() {
        // Small delay to allow dropdown click to register
        setTimeout(() => {
            universityDropdown.classList.add('hidden');
        }, 200);
    });
}

// Initialize discipline search functionality
function initializeDisciplineSearch(entryId) {
    const disciplineInput = document.getElementById(`discipline_search_${entryId}`);
    const disciplineDropdown = document.getElementById(`discipline_dropdown_${entryId}`);
    const disciplineHidden = document.getElementById(`discipline_hidden_${entryId}`);
    
    if (!disciplineInput || !disciplineDropdown || !disciplineHidden) return;
    
    disciplineInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        // Update hidden field with current value
        disciplineHidden.value = this.value.trim();
        
        if (searchTerm.length === 0) {
            disciplineDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = disciplines.filter(disc => 
            disc.toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0) {
            disciplineDropdown.innerHTML = filtered.map(disc => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-value="' + disc + '">' + disc + '</div>'
            ).join('');
            disciplineDropdown.classList.remove('hidden');
        } else {
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Handle selection from dropdown
    disciplineDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            disciplineInput.value = selectedValue;
            disciplineHidden.value = selectedValue;
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!disciplineInput.contains(e.target) && !disciplineDropdown.contains(e.target)) {
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Allow free text input (user can type custom discipline)
    disciplineInput.addEventListener('blur', function() {
        // Small delay to allow dropdown click to register
        setTimeout(() => {
            disciplineDropdown.classList.add('hidden');
            // Ensure hidden field is updated
            disciplineHidden.value = this.value.trim();
        }, 200);
    });
}

function loadCommandsForZone(zoneId, savedCommandId = null) {
    // Filter commands by zone - check both zone_id and zone.id
    window.commands = window.allCommands.filter(cmd => {
        const cmdZoneId = cmd.zone?.id || cmd.zone_id;
        return cmdZoneId == zoneId;
    });
    
    // Clear previous command selection
    clearCommandSelection();
    
    // Enable command search
    const commandSearch = document.getElementById('command_search');
    commandSearch.readOnly = false;
    commandSearch.placeholder = 'Search command...';
    
    // Re-initialize searchable select with filtered commands
    initializeCommandSearch();
    
    // If saved command exists and belongs to this zone, set it
    if (savedCommandId) {
        const savedCmd = window.commands.find(c => c.id == savedCommandId);
        if (savedCmd) {
            commandSearch.value = savedCmd.name;
            document.getElementById('command_id').value = savedCmd.id;
            document.getElementById('selected_command_name').textContent = savedCmd.name;
            document.getElementById('selected_command').classList.remove('hidden');
        }
    }
}

function initializeCommandSearch() {
    const commandSearch = document.getElementById('command_search');
    const commandId = document.getElementById('command_id');
    const commandDropdown = document.getElementById('command_dropdown');
    const selectedCommand = document.getElementById('selected_command');
    const selectedCommandName = document.getElementById('selected_command_name');
    
    commandSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const commands = window.commands || [];
        
        const filtered = commands.filter(cmd => 
            cmd.name.toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0 && searchTerm.length > 0) {
            commandDropdown.innerHTML = filtered.map(cmd => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-id="' + cmd.id + '" ' +
                'data-name="' + cmd.name + '">' + cmd.name + '</div>'
            ).join('');
            commandDropdown.classList.remove('hidden');
        } else {
            commandDropdown.classList.add('hidden');
        }
    });
    
    commandDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-id]');
        if (option) {
            const cmdId = option.dataset.id;
            const cmdName = option.dataset.name;
            commandId.value = cmdId;
            commandSearch.value = cmdName;
            selectedCommandName.textContent = cmdName;
            selectedCommand.classList.remove('hidden');
            commandDropdown.classList.add('hidden');
            clearError('command_id');
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!commandSearch.contains(e.target) && !commandDropdown.contains(e.target)) {
            commandDropdown.classList.add('hidden');
        }
    });
}

function clearCommandSelection() {
    const commandSearch = document.getElementById('command_search');
    const commandId = document.getElementById('command_id');
    const selectedCommand = document.getElementById('selected_command');
    
    commandSearch.value = '';
    commandId.value = '';
    selectedCommand.classList.add('hidden');
    
    // Reset command search to readonly if no zone selected
    const zoneId = document.getElementById('zone_id').value;
    if (!zoneId) {
        commandSearch.readOnly = true;
        commandSearch.placeholder = 'Select zone first, then search command...';
    }
    
    clearError('command_id');
}

// Validation functions
function showError(field, message) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.remove('hidden');
        input?.classList.add('border-danger');
    }
}

function clearError(field) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.classList.add('hidden');
        input?.classList.remove('border-danger');
    }
}

function validateStep2() {
    let isValid = true;
    
    const requiredFields = {
        'date_of_first_appointment': 'Date of First Appointment is required',
        'date_of_present_appointment': 'Date of Present Appointment is required',
        'substantive_rank': 'Substantive Rank is required',
        'salary_grade_level': 'Salary Grade Level is required',
        'zone_id': 'Zone is required',
        'command_id': 'Command/Present Station is required',
        'date_posted_to_station': 'Date Posted to Station is required'
    };
    
    // Validate education entries
    const educationCards = document.querySelectorAll('#education-entries .kt-card');
    let hasEducationError = false;
    
    educationCards.forEach((card, index) => {
        const entryId = card.dataset.entryId;
        const university = card.querySelector('.education-university');
        const qualification = card.querySelector('.education-qualification');
        const yearObtained = card.querySelector('.education-year-obtained');
        
        if (!university || !university.value.trim()) {
            const errorSpan = university?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Institution is required';
                errorSpan.classList.remove('hidden');
                university?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
        
        if (!qualification || !qualification.value.trim()) {
            const errorSpan = qualification?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Entry Qualification is required';
                errorSpan.classList.remove('hidden');
                qualification?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
        
        if (!yearObtained || !yearObtained.value.trim()) {
            const errorSpan = yearObtained?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Year Obtained is required';
                errorSpan.classList.remove('hidden');
                yearObtained?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
    });

    // Clear all errors first
    Object.keys(requiredFields).forEach(field => clearError(field));

    // Validate required fields
    Object.keys(requiredFields).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        const value = input?.value?.trim();
        
        if (!value || value === '') {
            showError(field, requiredFields[field]);
            isValid = false;
        }
    });
    
    // Validate that if zone is selected, command must be selected
    const zoneId = document.querySelector('[name="zone_id"]')?.value;
    const commandId = document.querySelector('[name="command_id"]')?.value;
    if (zoneId && !commandId) {
        showError('command_id', 'Please select a command from the selected zone');
        isValid = false;
    }

    // Validate date logic
    const dofa = document.querySelector('[name="date_of_first_appointment"]')?.value;
    const dopa = document.querySelector('[name="date_of_present_appointment"]')?.value;
    const dopts = document.querySelector('[name="date_posted_to_station"]')?.value;

    if (dofa && dopa && new Date(dofa) > new Date(dopa)) {
        showError('date_of_present_appointment', 'Date of Present Appointment must be after Date of First Appointment');
        isValid = false;
    }

    if (dopa && dopts && new Date(dopa) > new Date(dopts)) {
        showError('date_posted_to_station', 'Date Posted to Station must be after Date of Present Appointment');
        isValid = false;
    }

    return isValid;
}

// Form submission handler
document.getElementById('onboarding-step2-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateStep2()) {
        const firstError = document.querySelector('.error-message:not(.hidden)');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    this.submit();
});

// Rank to Grade Level mapping
const rankToGradeMap = {
    'CGC': 'GL 18',
    'DCG': 'GL 17',
    'ACG': 'GL 16',
    'CC': 'GL 15',
    'DC': 'GL 14',
    'AC': 'GL 13',
    'CSC': 'GL 12',
    'SC': 'GL 11',
    'DSC': 'GL 10',
    'ASC I': 'GL 09',
    'ASC II': 'GL 08',
    'IC': 'GL 07',
    'AIC': 'GL 06',
    'CA I': 'GL 05',
    'CA II': 'GL 04',
    'CA III': 'GL 03',
};

// Auto-populate grade level when rank is selected
function initializeRankGradeMapping() {
    const rankSelect = document.getElementById('substantive_rank');
    const gradeLevelSelect = document.getElementById('salary_grade_level');
    
    if (rankSelect && gradeLevelSelect) {
        rankSelect.addEventListener('change', function() {
            const selectedRank = this.value;
            
            if (selectedRank && rankToGradeMap[selectedRank]) {
                const gradeLevel = rankToGradeMap[selectedRank];
                gradeLevelSelect.value = gradeLevel;
                clearError('salary_grade_level');
                
                // Trigger change event to ensure form validation recognizes the change
                gradeLevelSelect.dispatchEvent(new Event('change'));
            } else if (!selectedRank) {
                gradeLevelSelect.value = '';
            }
        });
        
        // Auto-populate on page load if rank is already selected
        if (rankSelect.value && rankToGradeMap[rankSelect.value]) {
            gradeLevelSelect.value = rankToGradeMap[rankSelect.value];
        }
    }
}

// Clear errors on input
document.querySelectorAll('#onboarding-step2-form input, #onboarding-step2-form select').forEach(input => {
    input.addEventListener('input', function() {
        clearError(this.name);
        // Clear education field errors
        const errorSpan = this.parentElement?.querySelector('.error-message');
        if (errorSpan && (this.classList.contains('education-university') || this.classList.contains('education-qualification') || this.classList.contains('education-year-obtained'))) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            this.classList.remove('border-danger');
        }
    });
    input.addEventListener('change', function() {
        clearError(this.name);
        // Clear education field errors
        const errorSpan = this.parentElement?.querySelector('.error-message');
        if (errorSpan && (this.classList.contains('education-university') || this.classList.contains('education-qualification') || this.classList.contains('education-year-obtained'))) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            this.classList.remove('border-danger');
        }
    });
});
</script>
@endpush
@endsection


