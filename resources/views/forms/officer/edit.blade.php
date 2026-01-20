@extends('layouts.app')

@section('title', 'Edit Officer')
@section('page-title', 'Edit Officer')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.officers') }}">Officers</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.officers.show', $officer->id) }}">{{ $officer->initials }} {{ $officer->surname }}</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    @if($errors->any())
    <div class="kt-alert kt-alert-danger">
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

    <form method="POST" action="{{ route('hrd.officers.update', $officer->id) }}" id="officer-edit-form" class="flex flex-col gap-5" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Personal Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Personal Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Service Number</label>
                        <input type="text" class="kt-input" value="{{ $officer->service_number ?? 'N/A' }}" readonly/>
                        <small class="text-muted">Auto-generated</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Initials <span class="text-danger">*</span></label>
                        <input type="text" name="initials" class="kt-input" value="{{ old('initials', $officer->initials) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Surname <span class="text-danger">*</span></label>
                        <input type="text" name="surname" class="kt-input" value="{{ old('surname', $officer->surname) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Sex <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="sex" id="sex_id" value="{{ old('sex', $officer->sex) ?? '' }}" required>
                            <button type="button" 
                                    id="sex_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="sex_select_text">{{ old('sex', $officer->sex) ? (old('sex', $officer->sex) === 'M' ? 'Male' : 'Female') : 'Select...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="sex_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="sex_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search..."
                                           autocomplete="off">
                                </div>
                                <div id="sex_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="kt-input" value="{{ old('date_of_birth', $officer->date_of_birth?->format('Y-m-d')) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">State of Origin <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="state_of_origin" id="state_of_origin_id" value="{{ old('state_of_origin', $officer->state_of_origin) ?? '' }}" required>
                            <button type="button" 
                                    id="state_of_origin_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="state_of_origin_select_text">{{ old('state_of_origin', $officer->state_of_origin) ? old('state_of_origin', $officer->state_of_origin) : 'Select State...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="state_of_origin_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="state_of_origin_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search state..."
                                           autocomplete="off">
                                </div>
                                <div id="state_of_origin_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">LGA <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   id="lga_search" 
                                   class="kt-input w-full" 
                                   placeholder="Select State first, then search LGA..."
                                   autocomplete="off"
                                   readonly>
                            <input type="hidden" 
                                   name="lga" 
                                   id="lga_hidden" 
                                   value="{{ old('lga', $officer->lga) }}"
                                   required>
                            <div id="lga_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            </div>
                        </div>
                        <div id="selected_lga" class="mt-2 p-2 bg-muted/50 rounded-lg {{ old('lga', $officer->lga) ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" id="selected_lga_name">{{ old('lga', $officer->lga) }}</span>
                                <button type="button" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger"
                                        onclick="clearLgaSelection()">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Geopolitical Zone <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="geopolitical_zone" id="geopolitical_zone_id" value="{{ old('geopolitical_zone', $officer->geopolitical_zone) ?? '' }}" required>
                            <button type="button" 
                                    id="geopolitical_zone_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="geopolitical_zone_select_text">{{ old('geopolitical_zone', $officer->geopolitical_zone) ? old('geopolitical_zone', $officer->geopolitical_zone) : 'Select Zone...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="geopolitical_zone_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="geopolitical_zone_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search zone..."
                                           autocomplete="off">
                                </div>
                                <div id="geopolitical_zone_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Marital Status <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="marital_status" id="marital_status_id" value="{{ old('marital_status', $officer->marital_status) ?? '' }}" required>
                            <button type="button" 
                                    id="marital_status_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="marital_status_select_text">{{ old('marital_status', $officer->marital_status) ? old('marital_status', $officer->marital_status) : 'Select...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="marital_status_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="marital_status_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search..."
                                           autocomplete="off">
                                </div>
                                <div id="marital_status_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="phone_number" class="kt-input" value="{{ old('phone_number', $officer->phone_number) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Email Address</label>
                        <input type="email" name="email" class="kt-input" value="{{ old('email', $officer->email) }}"/>
                    </div>
                </div>
                <div class="flex flex-col gap-1 mt-5">
                    <label class="kt-form-label">Residential Address <span class="text-danger">*</span></label>
                    <textarea name="residential_address" class="kt-input" rows="3" required>{{ old('residential_address', $officer->residential_address) }}</textarea>
                </div>
                <div class="flex flex-col gap-1 mt-5">
                    <label class="kt-form-label">Permanent Home Address <span class="text-danger">*</span></label>
                    <textarea name="permanent_home_address" class="kt-input" rows="3" required>{{ old('permanent_home_address', $officer->permanent_home_address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Employment Details -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Employment Details</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of First Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_first_appointment" class="kt-input" value="{{ old('date_of_first_appointment', $officer->date_of_first_appointment?->format('Y-m-d')) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Present Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_present_appointment" class="kt-input" value="{{ old('date_of_present_appointment', $officer->date_of_present_appointment?->format('Y-m-d')) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Substantive Rank <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="substantive_rank" id="substantive_rank" value="{{ old('substantive_rank', $officer->substantive_rank) ?? '' }}" required>
                            <button type="button" 
                                    id="substantive_rank_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="substantive_rank_select_text">{{ old('substantive_rank', $officer->substantive_rank) ? old('substantive_rank', $officer->substantive_rank) : 'Select Rank...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="substantive_rank_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="substantive_rank_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search rank..."
                                           autocomplete="off">
                                </div>
                                <div id="substantive_rank_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Salary Grade Level <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="salary_grade_level" id="salary_grade_level" value="{{ old('salary_grade_level', $officer->salary_grade_level) ?? '' }}" required>
                            <button type="button" 
                                    id="salary_grade_level_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="salary_grade_level_select_text">{{ old('salary_grade_level', $officer->salary_grade_level) ? old('salary_grade_level', $officer->salary_grade_level) : 'Select Grade Level...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="salary_grade_level_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="salary_grade_level_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search grade level..."
                                           autocomplete="off">
                                </div>
                                <div id="salary_grade_level_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Zone <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="zone_id" id="zone_id" value="{{ old('zone_id', $officer->presentStation?->zone_id) ?? '' }}" required>
                            <button type="button" 
                                    id="zone_id_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="zone_id_select_text">{{ old('zone_id', $officer->presentStation?->zone_id) ? ($zones->firstWhere('id', old('zone_id', $officer->presentStation?->zone_id))->name ?? 'Select Zone...') : 'Select Zone...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="zone_id_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="zone_id_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search zone..."
                                           autocomplete="off">
                                </div>
                                <div id="zone_id_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
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
                                   name="present_station" 
                                   id="command_id" 
                                   value="{{ old('present_station', $officer->present_station) }}"
                                   required>
                            <div id="command_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            </div>
                        </div>
                        <div id="selected_command" class="mt-2 p-2 bg-muted/50 rounded-lg {{ old('present_station', $officer->present_station) ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" id="selected_command_name">{{ $officer->presentStation->name ?? '' }}</span>
                                <button type="button" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger"
                                        onclick="clearCommandSelection()">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date Posted to Station <span class="text-danger">*</span></label>
                        <input type="date" name="date_posted_to_station" class="kt-input" value="{{ old('date_posted_to_station', $officer->date_posted_to_station?->format('Y-m-d')) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Unit</label>
                        <div class="relative">
                            <input type="hidden" name="unit" id="unit_id" value="{{ old('unit', $officer->unit) ?? '' }}">
                            <button type="button" 
                                    id="unit_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="unit_select_text">{{ old('unit', $officer->unit) ? old('unit', $officer->unit) : 'Select Unit...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="unit_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="unit_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search unit..."
                                           autocomplete="off">
                                </div>
                                <div id="unit_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Education Section -->
                <div class="flex flex-col gap-5 pt-5 border-t border-input mt-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Education</h3>
                        <button type="button" id="add-education-btn" class="kt-btn kt-btn-sm text-white" style="background-color: #068b57; border-color: #068b57;">
                            <i class="ki-filled ki-plus" style="color: white;"></i> Add Education
                        </button>
                    </div>
                    
                    <div id="education-entries" class="flex flex-col gap-5">
                        <!-- Education entries will be added here dynamically -->
                    </div>
                </div>
            </div>
        </div>


        <!-- Status Flags -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Status Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid lg:grid-cols-3 gap-5">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="interdicted" id="interdicted" value="1" {{ old('interdicted', $officer->interdicted) ? 'checked' : '' }} class="kt-checkbox"/>
                        <label for="interdicted" class="kt-form-label">Interdicted</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="suspended" id="suspended" value="1" {{ old('suspended', $officer->suspended) ? 'checked' : '' }} class="kt-checkbox"/>
                        <label for="suspended" class="kt-form-label">Suspended</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="quartered" id="quartered" value="1" {{ old('quartered', $officer->quartered) ? 'checked' : '' }} class="kt-checkbox"/>
                        <label for="quartered" class="kt-form-label">Quartered</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hrd.officers.show', $officer->id) }}" class="kt-btn kt-btn-secondary">Cancel</a>
            <button type="submit" class="kt-btn text-white" style="background-color: #068b57; border-color: #068b57;">
                <i class="ki-filled ki-check" style="color: white;"></i> Update Officer
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Include all JavaScript from onboarding forms
// Nigerian States and LGAs data
const nigerianStatesLGAs = {
    'Abia': ['Aba North', 'Aba South', 'Arochukwu', 'Bende', 'Ikwuano', 'Isiala Ngwa North', 'Isiala Ngwa South', 'Isuikwuato', 'Obi Ngwa', 'Ohafia', 'Osisioma', 'Ugwunagbo', 'Ukwa East', 'Ukwa West', 'Umuahia North', 'Umuahia South', 'Umu Nneochi'],
    'Adamawa': ['Demsa', 'Fufure', 'Ganye', 'Gayuk', 'Gombi', 'Grie', 'Hong', 'Jada', 'Larmurde', 'Madagali', 'Maiha', 'Mayo Belwa', 'Michika', 'Mubi North', 'Mubi South', 'Numan', 'Shelleng', 'Song', 'Toungo', 'Yola North', 'Yola South'],
    'Akwa Ibom': ['Abak', 'Eastern Obolo', 'Eket', 'Esit Eket', 'Essien Udim', 'Etim Ekpo', 'Etinan', 'Ibeno', 'Ibesikpo Asutan', 'Ibiono-Ibom', 'Ika', 'Ikono', 'Ikot Abasi', 'Ikot Ekpene', 'Ini', 'Itu', 'Mbo', 'Mkpat-Enin', 'Nsit-Atai', 'Nsit-Ibom', 'Nsit-Ubium', 'Obot Akara', 'Okobo', 'Onna', 'Oron', 'Oruk Anam', 'Udung-Uko', 'Ukanafun', 'Uruan', 'Urue-Offong/Oruko', 'Uyo'],
    'Anambra': ['Aguata', 'Anambra East', 'Anambra West', 'Anaocha', 'Awka North', 'Awka South', 'Ayamelum', 'Dunukofia', 'Ekwusigo', 'Idemili North', 'Idemili South', 'Ihiala', 'Njikoka', 'Nnewi North', 'Nnewi South', 'Ogbaru', 'Onitsha North', 'Onitsha South', 'Orumba North', 'Orumba South', 'Oyi'],
    'Bauchi': ['Alkaleri', 'Bauchi', 'Bogoro', 'Damban', 'Darazo', 'Dass', 'Gamawa', 'Ganjuwa', 'Giade', 'Itas/Gadau', 'Jama\'are', 'Katagum', 'Kirfi', 'Misau', 'Ningi', 'Shira', 'Tafawa Balewa', 'Toro', 'Warji', 'Zaki'],
    'Bayelsa': ['Brass', 'Ekeremor', 'Kolokuma/Opokuma', 'Nembe', 'Ogbia', 'Sagbama', 'Southern Ijaw', 'Yenagoa'],
    'Benue': ['Ado', 'Agatu', 'Apa', 'Buruku', 'Gboko', 'Guma', 'Gwer East', 'Gwer West', 'Katsina-Ala', 'Konshisha', 'Kwande', 'Logo', 'Makurdi', 'Obi', 'Ogbadibo', 'Ohimini', 'Oju', 'Okpokwu', 'Otukpo', 'Tarka', 'Ukum', 'Ushongo', 'Vandeikya'],
    'Borno': ['Abadam', 'Askira/Uba', 'Bama', 'Bayo', 'Biu', 'Chibok', 'Damboa', 'Dikwa', 'Gubio', 'Guzamala', 'Gwoza', 'Hawul', 'Jere', 'Kaga', 'Kala/Balge', 'Konduga', 'Kukawa', 'Kwaya Kusar', 'Mafa', 'Magumeri', 'Maiduguri', 'Marte', 'Mobbar', 'Monguno', 'Ngala', 'Nganzai', 'Shani'],
    'Cross River': ['Abi', 'Akamkpa', 'Akpabuyo', 'Bakassi', 'Bekwarra', 'Biase', 'Boki', 'Calabar Municipal', 'Calabar South', 'Etung', 'Ikom', 'Obanliku', 'Obubra', 'Obudu', 'Odukpani', 'Ogoja', 'Yakuur', 'Yala'],
    'Delta': ['Aniocha North', 'Aniocha South', 'Bomadi', 'Burutu', 'Ethiope East', 'Ethiope West', 'Ika North East', 'Ika South', 'Isoko North', 'Isoko South', 'Ndokwa East', 'Ndokwa West', 'Okpe', 'Oshimili North', 'Oshimili South', 'Patani', 'Sapele', 'Udu', 'Ughelli North', 'Ughelli South', 'Ukwuani', 'Uvwie', 'Warri North', 'Warri South', 'Warri South West'],
    'Ebonyi': ['Abakaliki', 'Afikpo North', 'Afikpo South', 'Ebonyi', 'Ezza North', 'Ezza South', 'Ikwo', 'Ishielu', 'Ivo', 'Ohaozara', 'Ohaukwu', 'Onicha'],
    'Edo': ['Akoko-Edo', 'Egor', 'Esan Central', 'Esan North-East', 'Esan South-East', 'Esan West', 'Etsako Central', 'Etsako East', 'Etsako West', 'Igueben', 'Ikpoba Okha', 'Orhionmwon', 'Oredo', 'Ovia North-East', 'Ovia South-West', 'Owan East', 'Owan West', 'Uhunmwonde'],
    'Ekiti': ['Ado Ekiti', 'Efon', 'Ekiti East', 'Ekiti South-West', 'Ekiti West', 'Emure', 'Gbonyin', 'Ido Osi', 'Ijero', 'Ikere', 'Ikole', 'Ilejemeje', 'Irepodun/Ifelodun', 'Ise/Orun', 'Moba', 'Oye'],
    'Enugu': ['Aninri', 'Awgu', 'Enugu East', 'Enugu North', 'Enugu South', 'Ezeagu', 'Igbo Etiti', 'Igbo Eze North', 'Igbo Eze South', 'Isi Uzo', 'Nkanu East', 'Nkanu West', 'Nsukka', 'Oji River', 'Udenu', 'Udi', 'Uzo Uwani'],
    'FCT': ['Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council'],
    'Gombe': ['Akko', 'Balanga', 'Billiri', 'Dukku', 'Funakaye', 'Gombe', 'Kaltungo', 'Kwami', 'Nafada', 'Shongom', 'Yamaltu/Deba'],
    'Imo': ['Aboh Mbaise', 'Ahiazu Mbaise', 'Ehime Mbano', 'Ezinihitte', 'Ideato North', 'Ideato South', 'Ihitte/Uboma', 'Ikeduru', 'Isiala Mbano', 'Isu', 'Mbaitoli', 'Ngor Okpala', 'Njaba', 'Nkwerre', 'Nwangele', 'Obowo', 'Oguta', 'Ohaji/Egbema', 'Okigwe', 'Orlu', 'Orsu', 'Oru East', 'Oru West', 'Owerri Municipal', 'Owerri North', 'Owerri West', 'Unuimo'],
    'Jigawa': ['Auyo', 'Babura', 'Biriniwa', 'Birnin Kudu', 'Buji', 'Dutse', 'Gagarawa', 'Garki', 'Gumel', 'Guri', 'Gwaram', 'Gwiwa', 'Hadejia', 'Jahun', 'Kafin Hausa', 'Kazaure', 'Kiri Kasama', 'Kiyawa', 'Kaugama', 'Maigatari', 'Malam Madori', 'Miga', 'Ringim', 'Roni', 'Sule Tankarkar', 'Taura', 'Yankwashi'],
    'Kaduna': ['Birnin Gwari', 'Chikun', 'Giwa', 'Igabi', 'Ikara', 'Jaba', 'Jema\'a', 'Kachia', 'Kaduna North', 'Kaduna South', 'Kagarko', 'Kajuru', 'Kaura', 'Kauru', 'Kubau', 'Kudan', 'Lere', 'Makarfi', 'Sabon Gari', 'Sanga', 'Soba', 'Zangon Kataf', 'Zaria'],
    'Kano': ['Ajingi', 'Albasu', 'Bagwai', 'Bebeji', 'Bichi', 'Bunkure', 'Dala', 'Dambatta', 'Dawakin Kudu', 'Dawakin Tofa', 'Doguwa', 'Fagge', 'Gabasawa', 'Garko', 'Garun Mallam', 'Gaya', 'Gezawa', 'Gwale', 'Gwarzo', 'Kabo', 'Kano Municipal', 'Karaye', 'Kibiya', 'Kiru', 'Kumbotso', 'Kunchi', 'Kura', 'Madobi', 'Makoda', 'Minjibir', 'Nasarawa', 'Rano', 'Rimin Gado', 'Rogo', 'Shanono', 'Sumaila', 'Takai', 'Tarauni', 'Tofa', 'Tsanyawa', 'Tudun Wada', 'Ungogo', 'Warawa', 'Wudil'],
    'Katsina': ['Bakori', 'Batagarawa', 'Batsari', 'Baure', 'Bindawa', 'Charanchi', 'Dandume', 'Danja', 'Dan Musa', 'Daura', 'Dutsi', 'Dutsin Ma', 'Faskari', 'Funtua', 'Ingawa', 'Jibia', 'Kafur', 'Kaita', 'Kankara', 'Kankia', 'Katsina', 'Kurfi', 'Kusada', 'Mai\'Adua', 'Malumfashi', 'Mani', 'Mashi', 'Matazu', 'Musawa', 'Rimi', 'Sabuwa', 'Safana', 'Sandamu', 'Zango'],
    'Kebbi': ['Aleiro', 'Arewa Dandi', 'Argungu', 'Augie', 'Bagudo', 'Bunza', 'Dandi', 'Fakai', 'Gwandu', 'Jega', 'Kalgo', 'Koko/Besse', 'Maiyama', 'Ngaski', 'Sakaba', 'Shanga', 'Suru', 'Wasagu/Danko', 'Yauri', 'Zuru'],
    'Kogi': ['Adavi', 'Ajaokuta', 'Ankpa', 'Bassa', 'Dekina', 'Ibaji', 'Idah', 'Igalamela Odolu', 'Ijumu', 'Kabba/Bunu', 'Kogi', 'Lokoja', 'Mopa Muro', 'Ofu', 'Ogori/Magongo', 'Okehi', 'Okene', 'Olamaboro', 'Omala', 'Yagba East', 'Yagba West'],
    'Kwara': ['Asa', 'Baruten', 'Edu', 'Ekiti', 'Ifelodun', 'Ilorin East', 'Ilorin South', 'Ilorin West', 'Irepodun', 'Isin', 'Kaiama', 'Moro', 'Offa', 'Oke Ero', 'Oyun', 'Pategi'],
    'Lagos': ['Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe', 'Eti Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'],
    'Nasarawa': ['Akwanga', 'Awe', 'Doma', 'Karu', 'Keana', 'Keffi', 'Kokona', 'Lafia', 'Nasarawa', 'Nasarawa Egon', 'Obi', 'Toto', 'Wamba'],
    'Niger': ['Agaie', 'Agwara', 'Bida', 'Borgu', 'Bosso', 'Chanchaga', 'Edati', 'Gbako', 'Gurara', 'Katcha', 'Kontagora', 'Lapai', 'Lavun', 'Magama', 'Mariga', 'Mashegu', 'Mokwa', 'Moya', 'Paikoro', 'Rafi', 'Rijau', 'Shiroro', 'Suleja', 'Tafa', 'Wushishi'],
    'Ogun': ['Abeokuta North', 'Abeokuta South', 'Ado-Odo/Ota', 'Egbado North', 'Egbado South', 'Ewekoro', 'Ifo', 'Ijebu East', 'Ijebu North', 'Ijebu North East', 'Ijebu Ode', 'Ikenne', 'Imeko Afon', 'Ipokia', 'Obafemi Owode', 'Odeda', 'Odogbolu', 'Ogun Waterside', 'Remo North', 'Shagamu', 'Yewa North', 'Yewa South'],
    'Ondo': ['Akoko North-East', 'Akoko North-West', 'Akoko South-West', 'Akoko South-East', 'Akure North', 'Akure South', 'Ese Odo', 'Idanre', 'Ifedore', 'Ilaje', 'Ile Oluji/Okeigbo', 'Irele', 'Odigbo', 'Okitipupa', 'Ondo East', 'Ondo West', 'Ose', 'Owo'],
    'Osun': ['Atakunmosa East', 'Atakunmosa West', 'Aiyedaade', 'Aiyedire', 'Boluwaduro', 'Boripe', 'Ede North', 'Ede South', 'Ife Central', 'Ife East', 'Ife North', 'Ife South', 'Ifedayo', 'Ifelodun', 'Ila', 'Ilesa East', 'Ilesa West', 'Irepodun', 'Irewole', 'Isokan', 'Iwo', 'Obokun', 'Odo Otin', 'Ola Oluwa', 'Olorunda', 'Oriade', 'Orolu', 'Osogbo'],
    'Oyo': ['Afijio', 'Akinyele', 'Atiba', 'Atisbo', 'Egbeda', 'Ibadan North', 'Ibadan North-East', 'Ibadan North-West', 'Ibadan South-East', 'Ibadan South-West', 'Ibarapa Central', 'Ibarapa East', 'Ibarapa North', 'Ido', 'Irepo', 'Iseyin', 'Itesiwaju', 'Iwajowa', 'Kajola', 'Lagelu', 'Ogbomoso North', 'Ogbomoso South', 'Ogo Oluwa', 'Olorunsogo', 'Oluyole', 'Ona Ara', 'Orelope', 'Ori Ire', 'Oyo', 'Oyo East', 'Saki East', 'Saki West', 'Surulere'],
    'Plateau': ['Bokkos', 'Barkin Ladi', 'Bassa', 'Jos East', 'Jos North', 'Jos South', 'Kanam', 'Kanke', 'Langtang North', 'Langtang South', 'Mangu', 'Mikang', 'Pankshin', 'Qua\'an Pan', 'Riyom', 'Shendam', 'Wase'],
    'Rivers': ['Abua/Odual', 'Ahoada East', 'Ahoada West', 'Akuku-Toru', 'Andoni', 'Asari-Toru', 'Bonny', 'Degema', 'Eleme', 'Emuoha', 'Etche', 'Gokana', 'Ikwerre', 'Khana', 'Obio/Akpor', 'Ogba/Egbema/Ndoni', 'Ogu/Bolo', 'Okrika', 'Omuma', 'Opobo/Nkoro', 'Oyigbo', 'Port Harcourt', 'Tai'],
    'Sokoto': ['Binji', 'Bodinga', 'Dange Shuni', 'Gada', 'Goronyo', 'Gudu', 'Gwadabawa', 'Illela', 'Isa', 'Kebbe', 'Kware', 'Rabah', 'Sabon Birni', 'Shagari', 'Silame', 'Sokoto North', 'Sokoto South', 'Tambuwal', 'Tangaza', 'Tureta', 'Wamako', 'Wurno', 'Yabo'],
    'Taraba': ['Ardo Kola', 'Bali', 'Donga', 'Gashaka', 'Gassol', 'Ibi', 'Jalingo', 'Karim Lamido', 'Kumi', 'Lau', 'Sardauna', 'Takum', 'Ussa', 'Wukari', 'Yorro', 'Zing'],
    'Yobe': ['Bade', 'Bursari', 'Damaturu', 'Fika', 'Fune', 'Geidam', 'Gujba', 'Gulani', 'Jakusko', 'Karasuwa', 'Machina', 'Nangere', 'Nguru', 'Potiskum', 'Tarmuwa', 'Yunusari', 'Yusufari'],
    'Zamfara': ['Anka', 'Bakura', 'Birnin Magaji/Kiyaw', 'Bukkuyum', 'Bungudu', 'Gummi', 'Kaura Namoda', 'Maradun', 'Maru', 'Shinkafi', 'Talata Mafara', 'Chafe', 'Zurmi']
};

// Institutions (master list from DB)
const nigerianUniversities = @json($institutions ?? []);

// Qualifications (master list from DB)
const qualifications = @json($qualifications ?? []);

// Disciplines (master list from DB)
const disciplines = @json($disciplines ?? []);

let educationEntryCount = 0;

// State to Geopolitical Zone mapping
const stateToZoneMap = {
    // North Central
    'Benue': 'North Central',
    'Kogi': 'North Central',
    'Kwara': 'North Central',
    'Nasarawa': 'North Central',
    'Niger': 'North Central',
    'Plateau': 'North Central',
    'FCT': 'North Central',
    // North East
    'Adamawa': 'North East',
    'Bauchi': 'North East',
    'Borno': 'North East',
    'Gombe': 'North East',
    'Taraba': 'North East',
    'Yobe': 'North East',
    // North West
    'Kaduna': 'North West',
    'Kano': 'North West',
    'Katsina': 'North West',
    'Kebbi': 'North West',
    'Jigawa': 'North West',
    'Sokoto': 'North West',
    'Zamfara': 'North West',
    // South East
    'Abia': 'South East',
    'Anambra': 'South East',
    'Ebonyi': 'South East',
    'Enugu': 'South East',
    'Imo': 'South East',
    // South South
    'Akwa Ibom': 'South South',
    'Bayelsa': 'South South',
    'Cross River': 'South South',
    'Delta': 'South South',
    'Edo': 'South South',
    'Rivers': 'South South',
    // South West
    'Ekiti': 'South West',
    'Lagos': 'South West',
    'Ogun': 'South West',
    'Ondo': 'South West',
    'Osun': 'South West',
    'Oyo': 'South West'
};

// Function to set geopolitical zone based on state
function setGeopoliticalZoneFromState(state) {
    const zone = stateToZoneMap[state];
    if (zone) {
        const zoneHiddenInput = document.getElementById('geopolitical_zone_id');
        const zoneDisplayText = document.getElementById('geopolitical_zone_select_text');
        if (zoneHiddenInput && zoneDisplayText) {
            zoneHiddenInput.value = zone;
            zoneDisplayText.textContent = zone;
        }
    }
}

// Rank to Grade Level mapping
const rankToGradeLevel = {
    'CGC': 'GL 17',
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

// Reusable function to create searchable select
function createSearchableSelect(config) {
    const {
        triggerId,
        hiddenInputId,
        dropdownId,
        searchInputId,
        optionsContainerId,
        displayTextId,
        options,
        displayFn,
        onSelect,
        placeholder = 'Select...',
        searchPlaceholder = 'Search...'
    } = config;

    const trigger = document.getElementById(triggerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const dropdown = document.getElementById(dropdownId);
    const searchInput = document.getElementById(searchInputId);
    const optionsContainer = document.getElementById(optionsContainerId);
    const displayText = document.getElementById(displayTextId);

    if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) {
        return;
    }

    let selectedOption = null;
    let filteredOptions = [...options];

    // Render options
    function renderOptions(opts) {
        if (opts.length === 0) {
            optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
            return;
        }

        optionsContainer.innerHTML = opts.map(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
            return `
                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                     data-id="${value}" 
                     data-name="${display}">
                    <div class="text-sm text-foreground">${display}</div>
                </div>
            `;
        }).join('');

        // Add click handlers
        optionsContainer.querySelectorAll('.select-option').forEach(option => {
            option.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                selectedOption = options.find(o => {
                    const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                    return String(optValue) === String(id);
                });
                
                if (selectedOption || id === '') {
                    hiddenInput.value = id;
                    displayText.textContent = name;
                    dropdown.classList.add('hidden');
                    searchInput.value = '';
                    filteredOptions = [...options];
                    renderOptions(filteredOptions);
                    
                    if (onSelect) onSelect(selectedOption || {id: id, name: name});
                }
            });
        });
    }

    // Initial render
    renderOptions(filteredOptions);

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filteredOptions = options.filter(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            return String(display).toLowerCase().includes(searchTerm);
        });
        renderOptions(filteredOptions);
    });

    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            setTimeout(() => searchInput.focus(), 100);
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

function initializeSearchableSelects() {
    // Sex options
    const sexOptions = [
        {id: '', name: 'Select...'},
        {id: 'M', name: 'Male'},
        {id: 'F', name: 'Female'}
    ];

    // State options
    const stateOptions = [
        {id: '', name: 'Select State...'},
        @foreach($nigerianStates as $state)
        {id: '{{ $state }}', name: '{{ $state }}'},
        @endforeach
    ];

    // Geopolitical zone options
    const zoneOptions = [
        {id: '', name: 'Select Zone...'},
        @foreach($geopoliticalZones as $zone)
        {id: '{{ $zone }}', name: '{{ $zone }}'},
        @endforeach
    ];

    // Marital status options
    const maritalStatusOptions = [
        {id: '', name: 'Select...'},
        {id: 'Single', name: 'Single'},
        {id: 'Married', name: 'Married'},
        {id: 'Divorced', name: 'Divorced'},
        {id: 'Widowed', name: 'Widowed'}
    ];

    // Rank options
    const rankOptions = [
        {id: '', name: 'Select Rank...'},
        @foreach($ranks as $rank)
        {id: '{{ $rank }}', name: '{{ $rank }}'},
        @endforeach
    ];

    // Grade level options
    const gradeLevelOptions = [
        {id: '', name: 'Select Grade Level...'},
        @foreach($gradeLevels as $grade)
        {id: '{{ $grade }}', name: '{{ $grade }}'},
        @endforeach
    ];

    // Zone options
    const zoneIdOptions = [
        {id: '', name: 'Select Zone...'},
        @foreach($zones as $zone)
        {id: '{{ $zone->id }}', name: '{{ $zone->name }}'},
        @endforeach
    ];

    // Unit options
    const unitOptions = [
        {id: '', name: 'Select Unit...'},
        {id: 'General Duty (GD)', name: 'General Duty (GD)'},
        {id: 'Support Staff (SS)', name: 'Support Staff (SS)'}
    ];

    // Initialize sex select
    createSearchableSelect({
        triggerId: 'sex_select_trigger',
        hiddenInputId: 'sex_id',
        dropdownId: 'sex_dropdown',
        searchInputId: 'sex_search_input',
        optionsContainerId: 'sex_options',
        displayTextId: 'sex_select_text',
        options: sexOptions,
        placeholder: 'Select...',
        searchPlaceholder: 'Search...'
    });

    // Initialize state of origin select
    createSearchableSelect({
        triggerId: 'state_of_origin_select_trigger',
        hiddenInputId: 'state_of_origin_id',
        dropdownId: 'state_of_origin_dropdown',
        searchInputId: 'state_of_origin_search_input',
        optionsContainerId: 'state_of_origin_options',
        displayTextId: 'state_of_origin_select_text',
        options: stateOptions,
        placeholder: 'Select State...',
        searchPlaceholder: 'Search state...',
        onSelect: function(option) {
            // Load LGAs when state is selected
            if (option.id) {
                loadLGAsForState(option.id);
                // Automatically set geopolitical zone based on state
                setGeopoliticalZoneFromState(option.id);
            } else {
                clearLgaSelection();
                // Clear zone if state is cleared
                const zoneHiddenInput = document.getElementById('geopolitical_zone_id');
                const zoneDisplayText = document.getElementById('geopolitical_zone_select_text');
                if (zoneHiddenInput && zoneDisplayText) {
                    zoneHiddenInput.value = '';
                    zoneDisplayText.textContent = 'Select Zone...';
                }
            }
        }
    });

    // Initialize geopolitical zone select
    createSearchableSelect({
        triggerId: 'geopolitical_zone_select_trigger',
        hiddenInputId: 'geopolitical_zone_id',
        dropdownId: 'geopolitical_zone_dropdown',
        searchInputId: 'geopolitical_zone_search_input',
        optionsContainerId: 'geopolitical_zone_options',
        displayTextId: 'geopolitical_zone_select_text',
        options: zoneOptions,
        placeholder: 'Select Zone...',
        searchPlaceholder: 'Search zone...'
    });

    // Initialize marital status select
    createSearchableSelect({
        triggerId: 'marital_status_select_trigger',
        hiddenInputId: 'marital_status_id',
        dropdownId: 'marital_status_dropdown',
        searchInputId: 'marital_status_search_input',
        optionsContainerId: 'marital_status_options',
        displayTextId: 'marital_status_select_text',
        options: maritalStatusOptions,
        placeholder: 'Select...',
        searchPlaceholder: 'Search...'
    });

    // Initialize substantive rank select
    createSearchableSelect({
        triggerId: 'substantive_rank_select_trigger',
        hiddenInputId: 'substantive_rank',
        dropdownId: 'substantive_rank_dropdown',
        searchInputId: 'substantive_rank_search_input',
        optionsContainerId: 'substantive_rank_options',
        displayTextId: 'substantive_rank_select_text',
        options: rankOptions,
        placeholder: 'Select Rank...',
        searchPlaceholder: 'Search rank...',
        onSelect: function(option) {
            // Auto-select grade level when rank is selected
            if (option.id && rankToGradeLevel[option.id]) {
                const gradeLevelHiddenInput = document.getElementById('salary_grade_level');
                const gradeLevelDisplayText = document.getElementById('salary_grade_level_select_text');
                if (gradeLevelHiddenInput && gradeLevelDisplayText) {
                    gradeLevelHiddenInput.value = rankToGradeLevel[option.id];
                    gradeLevelDisplayText.textContent = rankToGradeLevel[option.id];
                }
            }
        }
    });

    // Initialize salary grade level select
    createSearchableSelect({
        triggerId: 'salary_grade_level_select_trigger',
        hiddenInputId: 'salary_grade_level',
        dropdownId: 'salary_grade_level_dropdown',
        searchInputId: 'salary_grade_level_search_input',
        optionsContainerId: 'salary_grade_level_options',
        displayTextId: 'salary_grade_level_select_text',
        options: gradeLevelOptions,
        placeholder: 'Select Grade Level...',
        searchPlaceholder: 'Search grade level...'
    });

    // Initialize zone select
    createSearchableSelect({
        triggerId: 'zone_id_select_trigger',
        hiddenInputId: 'zone_id',
        dropdownId: 'zone_id_dropdown',
        searchInputId: 'zone_id_search_input',
        optionsContainerId: 'zone_id_options',
        displayTextId: 'zone_id_select_text',
        options: zoneIdOptions,
        placeholder: 'Select Zone...',
        searchPlaceholder: 'Search zone...',
        onSelect: function(option) {
            // Load commands when zone is selected
            if (option.id) {
                loadCommandsForZone(option.id);
            } else {
                clearCommandSelection();
            }
        }
    });

    // Initialize unit select
    createSearchableSelect({
        triggerId: 'unit_select_trigger',
        hiddenInputId: 'unit_id',
        dropdownId: 'unit_dropdown',
        searchInputId: 'unit_search_input',
        optionsContainerId: 'unit_options',
        displayTextId: 'unit_select_text',
        options: unitOptions,
        placeholder: 'Select Unit...',
        searchPlaceholder: 'Search unit...'
    });
}

document.addEventListener('DOMContentLoaded', async function() {
    // Initialize searchable selects
    initializeSearchableSelects();
    
    // Initialize LGA dropdown
    initializeLGASection();
    
    // Initialize Zone/Command selection
    await initializeZoneCommandSection();
    
        // Initialize Education section
    initializeEducationSection();
    
    // Initialize rank to grade level auto-selection
    initializeRankToGradeLevel();
    
    // Form submission confirmation
    const form = document.getElementById('officer-edit-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Update Officer Information?',
                text: 'Are you sure you want to update this officer\'s information?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#068b57',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});

// LGA Functions
function initializeLGASection() {
    const stateHiddenInput = document.getElementById('state_of_origin_id');
    const savedState = '{{ old('state_of_origin', $officer->state_of_origin) }}';
    const savedLga = '{{ old('lga', $officer->lga) }}';
    
    if (savedState) {
        loadLGAsForState(savedState, savedLga);
        setGeopoliticalZoneFromState(savedState);
    }
    
    // Listen for changes on the state hidden input
    if (stateHiddenInput) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    const selectedState = stateHiddenInput.value;
                    if (selectedState) {
                        loadLGAsForState(selectedState);
                        setGeopoliticalZoneFromState(selectedState);
                    } else {
                        clearLgaSelection();
                        // Clear zone if state is cleared
                        const zoneHiddenInput = document.getElementById('geopolitical_zone_id');
                        const zoneDisplayText = document.getElementById('geopolitical_zone_select_text');
                        if (zoneHiddenInput && zoneDisplayText) {
                            zoneHiddenInput.value = '';
                            zoneDisplayText.textContent = 'Select Zone...';
                        }
                    }
                }
            });
        });
        observer.observe(stateHiddenInput, { attributes: true, attributeFilter: ['value'] });

        // Also listen for direct value changes
        stateHiddenInput.addEventListener('input', function() {
            const selectedState = this.value;
            if (selectedState) {
                loadLGAsForState(selectedState);
                setGeopoliticalZoneFromState(selectedState);
            } else {
                clearLgaSelection();
                // Clear zone if state is cleared
                const zoneHiddenInput = document.getElementById('geopolitical_zone_id');
                const zoneDisplayText = document.getElementById('geopolitical_zone_select_text');
                if (zoneHiddenInput && zoneDisplayText) {
                    zoneHiddenInput.value = '';
                    zoneDisplayText.textContent = 'Select Zone...';
                }
            }
        });
    }
}

function loadLGAsForState(state, savedLga = '') {
    const lgas = nigerianStatesLGAs[state] || [];
    const lgaSearch = document.getElementById('lga_search');
    const lgaHidden = document.getElementById('lga_hidden');
    const lgaDropdown = document.getElementById('lga_dropdown');
    const selectedLga = document.getElementById('selected_lga');
    const selectedLgaName = document.getElementById('selected_lga_name');
    
    lgaSearch.value = '';
    lgaHidden.value = '';
    selectedLga.classList.add('hidden');
    
    lgaSearch.readOnly = false;
    lgaSearch.placeholder = 'Search LGA...';
    
    window.currentLGAs = lgas.map(lga => ({ name: lga }));
    
    if (savedLga && lgas.includes(savedLga)) {
        lgaSearch.value = savedLga;
        lgaHidden.value = savedLga;
        selectedLgaName.textContent = savedLga;
        selectedLga.classList.remove('hidden');
    }
    
    initializeLGASearch();
}

function clearLgaSelection() {
    const lgaSearch = document.getElementById('lga_search');
    const lgaHidden = document.getElementById('lga_hidden');
    const lgaDropdown = document.getElementById('lga_dropdown');
    const selectedLga = document.getElementById('selected_lga');
    
    lgaSearch.value = '';
    lgaHidden.value = '';
    lgaSearch.readOnly = true;
    lgaSearch.placeholder = 'Select State first, then search LGA...';
    lgaDropdown.classList.add('hidden');
    selectedLga.classList.add('hidden');
    window.currentLGAs = [];
}

function initializeLGASearch() {
    const lgaSearch = document.getElementById('lga_search');
    const lgaHidden = document.getElementById('lga_hidden');
    const lgaDropdown = document.getElementById('lga_dropdown');
    const selectedLga = document.getElementById('selected_lga');
    const selectedLgaName = document.getElementById('selected_lga_name');
    
    const newSearch = lgaSearch.cloneNode(true);
    lgaSearch.parentNode.replaceChild(newSearch, lgaSearch);
    
    const searchInput = document.getElementById('lga_search');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const lgas = window.currentLGAs || [];
        
        const filtered = lgas.filter(lga => lga.name.toLowerCase().includes(searchTerm));
        
        if (filtered.length > 0 && searchTerm.length > 0) {
            lgaDropdown.innerHTML = filtered.map(lga => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" data-name="' + lga.name + '">' + lga.name + '</div>'
            ).join('');
            lgaDropdown.classList.remove('hidden');
        } else {
            lgaDropdown.classList.add('hidden');
        }
    });
    
    lgaDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-name]');
        if (option) {
            const lgaName = option.dataset.name;
            lgaHidden.value = lgaName;
            searchInput.value = lgaName;
            selectedLgaName.textContent = lgaName;
            selectedLga.classList.remove('hidden');
            lgaDropdown.classList.add('hidden');
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !lgaDropdown.contains(e.target)) {
            lgaDropdown.classList.add('hidden');
        }
    });
}

// Zone/Command Functions
async function initializeZoneCommandSection() {
    const token = window.API_CONFIG?.token || '{{ auth()->user()?->createToken('token')->plainTextToken ?? '' }}';
    const savedZoneId = '{{ old('zone_id', $officer->presentStation?->zone_id) }}';
    const savedCommandId = '{{ old('present_station', $officer->present_station) }}';
    
    try {
        const zonesRes = await fetch('/api/v1/zones', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        const commandsRes = await fetch('/api/v1/commands', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (zonesRes.ok && commandsRes.ok) {
            const zonesData = await zonesRes.json();
            const commandsData = await commandsRes.json();
            
            const zones = zonesData.data || zonesData;
            const commands = commandsData.data || commandsData;
            
            window.allCommands = commands.map(cmd => ({
                id: cmd.id,
                name: cmd.name,
                zone_id: cmd.zone_id || (cmd.zone ? cmd.zone.id : null)
            }));
            
            if (savedZoneId) {
                setTimeout(() => {
                    loadCommandsForZone(savedZoneId, savedCommandId);
                }, 100);
            }
            
            const zoneHiddenInput = document.getElementById('zone_id');
            if (zoneHiddenInput) {
                // Listen for changes on the zone hidden input
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            const selectedZoneId = zoneHiddenInput.value;
                            if (selectedZoneId) {
                                loadCommandsForZone(selectedZoneId);
                            } else {
                                clearCommandSelection();
                            }
                        }
                    });
                });
                observer.observe(zoneHiddenInput, { attributes: true, attributeFilter: ['value'] });

                // Also listen for direct value changes
                zoneHiddenInput.addEventListener('input', function() {
                    const selectedZoneId = this.value;
                    if (selectedZoneId) {
                        loadCommandsForZone(selectedZoneId);
                    } else {
                        clearCommandSelection();
                    }
                });
            }
        }
    } catch (error) {
        console.error('Error loading zones/commands:', error);
    }
}

function loadCommandsForZone(zoneId, savedCommandId = null) {
    window.commands = window.allCommands.filter(cmd => {
        const cmdZoneId = cmd.zone?.id || cmd.zone_id;
        return cmdZoneId == zoneId;
    });
    
    clearCommandSelection();
    
    const commandSearch = document.getElementById('command_search');
    commandSearch.readOnly = false;
    commandSearch.placeholder = 'Search command...';
    
    initializeCommandSearch();
    
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
        
        const filtered = commands.filter(cmd => cmd.name.toLowerCase().includes(searchTerm));
        
        if (filtered.length > 0 && searchTerm.length > 0) {
            commandDropdown.innerHTML = filtered.map(cmd => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" data-id="' + cmd.id + '" data-name="' + cmd.name + '">' + cmd.name + '</div>'
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
        }
    });
    
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
    
    const zoneId = document.getElementById('zone_id').value;
    if (!zoneId) {
        commandSearch.readOnly = true;
        commandSearch.placeholder = 'Select zone first, then search command...';
    }
}

// Education Functions
function initializeEducationSection() {
    const addBtn = document.getElementById('add-education-btn');
    const entriesContainer = document.getElementById('education-entries');
    
    const savedEducation = @json($educationData ?? []);
    
    if (savedEducation && Array.isArray(savedEducation) && savedEducation.length > 0) {
        savedEducation.forEach(edu => {
            if (edu && (edu.university || edu.qualification)) {
                // Ensure university is a string, even if empty
                if (!edu.hasOwnProperty('university')) {
                    edu.university = '';
                }
                addEducationEntry(edu);
            }
        });
    }
    
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
    
    const savedUniversity = (data && data.university) ? String(data.university).trim() : '';
    const savedQualification = (data && data.qualification) ? String(data.qualification) : '';
    const savedDiscipline = (data && data.discipline) ? String(data.discipline) : '';
    const isCustomDiscipline = savedDiscipline && !disciplines.includes(savedDiscipline);
    
    // Escape HTML to prevent XSS and ensure proper display
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };
    
    entryDiv.innerHTML = `
        <div class="grid lg:grid-cols-3 gap-5">
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">University <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="text" 
                           name="education[${entryId}][university]" 
                           id="university_search_${entryId}"
                           class="kt-input w-full education-university" 
                           value="${escapeHtml(savedUniversity)}"
                           placeholder="Search or type university name..."
                           autocomplete="off"
                           required>
                    <div id="university_dropdown_${entryId}" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Entry Qualification <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][qualification]" id="education_qualification_${entryId}_id" value="${savedQualification}" required>
                    <button type="button" 
                            id="education_qualification_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_qualification_${entryId}_select_text">${savedQualification ? savedQualification : '-- Select Qualification --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_qualification_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_qualification_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search qualification..."
                                   autocomplete="off">
                        </div>
                        <div id="education_qualification_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Discipline <span class="text-muted">(Optional)</span></label>
                <div class="relative">
                    <input type="hidden" id="discipline_final_${entryId}" name="education[${entryId}][discipline]" value="${savedDiscipline}">
                    <button type="button" 
                            id="discipline_select_${entryId}_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="discipline_select_${entryId}_text">${savedDiscipline ? (isCustomDiscipline ? '-- Custom (Enter below) --' : savedDiscipline) : '-- Select Discipline --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="discipline_select_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="discipline_select_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search discipline..."
                                   autocomplete="off">
                        </div>
                        <div id="discipline_select_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                </div>
                <input type="text" 
                       id="discipline_custom_${entryId}"
                       class="kt-input mt-2 education-discipline-custom ${isCustomDiscipline ? '' : 'hidden'}" 
                       value="${isCustomDiscipline ? savedDiscipline : ''}"
                       placeholder="Enter custom discipline..."
                       oninput="handleCustomDiscipline(${entryId})">
            </div>
        </div>
        <div class="flex items-center justify-end mt-3">
            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-education-btn" onclick="removeEducationEntry(${entryId})">
                <i class="ki-filled ki-trash"></i> Remove
            </button>
        </div>
    `;
    
    entriesContainer.appendChild(entryDiv);
    
    // Set university value after element is created to ensure it's properly populated
    const universityInput = document.getElementById(`university_search_${entryId}`);
    if (universityInput) {
        // Always set the value, even if empty, to ensure proper initialization
        universityInput.value = savedUniversity || '';
        // If there's a saved university, also trigger any necessary updates
        if (savedUniversity) {
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                if (universityInput.value !== savedUniversity) {
                    universityInput.value = savedUniversity;
                }
            }, 10);
        }
    }
    
    initializeUniversitySearch(entryId);
    
    // Initialize qualification searchable select
    const qualificationOptions = [
        {id: '', name: '-- Select Qualification --'},
        ...qualifications.map(qual => ({id: qual, name: qual}))
    ];
    
    createSearchableSelect({
        triggerId: `education_qualification_${entryId}_select_trigger`,
        hiddenInputId: `education_qualification_${entryId}_id`,
        dropdownId: `education_qualification_${entryId}_dropdown`,
        searchInputId: `education_qualification_${entryId}_search_input`,
        optionsContainerId: `education_qualification_${entryId}_options`,
        displayTextId: `education_qualification_${entryId}_select_text`,
        options: qualificationOptions,
        placeholder: '-- Select Qualification --',
        searchPlaceholder: 'Search qualification...'
    });
    
    // Initialize discipline searchable select
    const disciplineOptions = [
        {id: '', name: '-- Select Discipline --'},
        ...disciplines.map(disc => ({id: disc, name: disc})),
        {id: '__CUSTOM__', name: '-- Custom (Enter below) --'}
    ];
    
    createSearchableSelect({
        triggerId: `discipline_select_${entryId}_trigger`,
        hiddenInputId: `discipline_final_${entryId}`,
        dropdownId: `discipline_select_${entryId}_dropdown`,
        searchInputId: `discipline_select_${entryId}_search_input`,
        optionsContainerId: `discipline_select_${entryId}_options`,
        displayTextId: `discipline_select_${entryId}_text`,
        options: disciplineOptions,
        placeholder: '-- Select Discipline --',
        searchPlaceholder: 'Search discipline...',
        onSelect: function(option) {
            handleDisciplineChange(entryId, option.id);
        }
    });
    
    if (isCustomDiscipline) {
        document.getElementById(`discipline_custom_${entryId}`).classList.remove('hidden');
    }
}

function removeEducationEntry(entryId) {
    const entry = document.querySelector(`[data-entry-id="${entryId}"]`);
    if (entry) {
        entry.remove();
    }
    
    const entriesContainer = document.getElementById('education-entries');
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
}

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
        
        const filtered = nigerianUniversities.filter(uni => uni.toLowerCase().includes(searchTerm));
        
        if (filtered.length > 0) {
            universityDropdown.innerHTML = filtered.map(uni => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" data-value="' + uni + '">' + uni + '</div>'
            ).join('');
            universityDropdown.classList.remove('hidden');
        } else {
            universityDropdown.classList.add('hidden');
        }
    });
    
    universityDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            universityInput.value = selectedValue;
            universityDropdown.classList.add('hidden');
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!universityInput.contains(e.target) && !universityDropdown.contains(e.target)) {
            universityDropdown.classList.add('hidden');
        }
    });
    
    universityInput.addEventListener('blur', function() {
        setTimeout(() => {
            universityDropdown.classList.add('hidden');
        }, 200);
    });
}

function handleDisciplineChange(entryId, selectedValue = null) {
    const disciplineCustom = document.getElementById(`discipline_custom_${entryId}`);
    const disciplineFinal = document.getElementById(`discipline_final_${entryId}`);
    const disciplineDisplayText = document.getElementById(`discipline_select_${entryId}_text`);
    
    if (!disciplineCustom || !disciplineFinal) return;
    
    // If selectedValue is not provided, get it from the hidden input
    if (selectedValue === null) {
        selectedValue = disciplineFinal.value;
    }
    
    if (selectedValue === '__CUSTOM__') {
        disciplineCustom.classList.remove('hidden');
        disciplineCustom.focus();
        if (disciplineDisplayText) {
            disciplineDisplayText.textContent = '-- Custom (Enter below) --';
        }
    } else if (selectedValue) {
        disciplineCustom.classList.add('hidden');
        disciplineCustom.value = '';
        disciplineFinal.value = selectedValue;
        if (disciplineDisplayText) {
            disciplineDisplayText.textContent = selectedValue;
        }
    } else {
        disciplineCustom.classList.add('hidden');
        disciplineCustom.value = '';
        disciplineFinal.value = '';
        if (disciplineDisplayText) {
            disciplineDisplayText.textContent = '-- Select Discipline --';
        }
    }
}

function handleCustomDiscipline(entryId) {
    const disciplineCustom = document.getElementById(`discipline_custom_${entryId}`);
    const disciplineFinal = document.getElementById(`discipline_final_${entryId}`);
    
    if (!disciplineCustom || !disciplineFinal) return;
    
    disciplineFinal.value = disciplineCustom.value.trim();
}

// Rank to Grade Level Functions
function initializeRankToGradeLevel() {
    const rankHiddenInput = document.getElementById('substantive_rank');
    const gradeLevelHiddenInput = document.getElementById('salary_grade_level');
    const gradeLevelDisplayText = document.getElementById('salary_grade_level_select_text');
    
    if (!rankHiddenInput || !gradeLevelHiddenInput) return;
    
    // Set initial grade level if rank is already selected
    const currentRank = rankHiddenInput.value;
    if (currentRank && rankToGradeLevel[currentRank]) {
        gradeLevelHiddenInput.value = rankToGradeLevel[currentRank];
        if (gradeLevelDisplayText) {
            gradeLevelDisplayText.textContent = rankToGradeLevel[currentRank];
        }
    }
    
    // Listen for rank changes (already handled in onSelect callback, but keeping for compatibility)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                const selectedRank = rankHiddenInput.value;
                if (selectedRank && rankToGradeLevel[selectedRank]) {
                    gradeLevelHiddenInput.value = rankToGradeLevel[selectedRank];
                    if (gradeLevelDisplayText) {
                        gradeLevelDisplayText.textContent = rankToGradeLevel[selectedRank];
                    }
                } else {
                    gradeLevelHiddenInput.value = '';
                    if (gradeLevelDisplayText) {
                        gradeLevelDisplayText.textContent = 'Select Grade Level...';
                    }
                }
            }
        });
    });
    observer.observe(rankHiddenInput, { attributes: true, attributeFilter: ['value'] });
}

</script>
@endpush
@endsection

