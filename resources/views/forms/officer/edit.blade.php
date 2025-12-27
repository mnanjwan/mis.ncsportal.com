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
                        <select name="sex" class="kt-input" required>
                            <option value="">Select...</option>
                            <option value="M" {{ old('sex', $officer->sex) == 'M' ? 'selected' : '' }}>Male</option>
                            <option value="F" {{ old('sex', $officer->sex) == 'F' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="kt-input" value="{{ old('date_of_birth', $officer->date_of_birth?->format('Y-m-d')) }}" required/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">State of Origin <span class="text-danger">*</span></label>
                        <select name="state_of_origin" class="kt-input" id="state-select" required>
                            <option value="">Select State...</option>
                            @foreach($nigerianStates as $state)
                            <option value="{{ $state }}" {{ old('state_of_origin', $officer->state_of_origin) == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
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
                        <select name="geopolitical_zone" class="kt-input" required>
                            <option value="">Select Zone...</option>
                            @foreach($geopoliticalZones as $zone)
                            <option value="{{ $zone }}" {{ old('geopolitical_zone', $officer->geopolitical_zone) == $zone ? 'selected' : '' }}>{{ $zone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Marital Status <span class="text-danger">*</span></label>
                        <select name="marital_status" class="kt-input" required>
                            <option value="">Select...</option>
                            <option value="Single" {{ old('marital_status', $officer->marital_status) == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('marital_status', $officer->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Divorced" {{ old('marital_status', $officer->marital_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="Widowed" {{ old('marital_status', $officer->marital_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
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
                        <select name="substantive_rank" id="substantive_rank" class="kt-input" required>
                            <option value="">Select Rank...</option>
                            @foreach($ranks as $rank)
                            <option value="{{ $rank }}" {{ old('substantive_rank', $officer->substantive_rank) == $rank ? 'selected' : '' }}>{{ $rank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Salary Grade Level <span class="text-danger">*</span></label>
                        <select name="salary_grade_level" id="salary_grade_level" class="kt-input" required>
                            <option value="">Select Grade Level...</option>
                            @foreach($gradeLevels as $grade)
                            <option value="{{ $grade }}" {{ old('salary_grade_level', $officer->salary_grade_level) == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Zone <span class="text-danger">*</span></label>
                        <select name="zone_id" id="zone_id" class="kt-input" required>
                            <option value="">Select Zone...</option>
                            @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ old('zone_id', $officer->presentStation?->zone_id) == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                            @endforeach
                        </select>
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
                        <select name="unit" class="kt-input">
                            <option value="">Select Unit...</option>
                            <option value="General Duty (GD)" {{ old('unit', $officer->unit) == 'General Duty (GD)' ? 'selected' : '' }}>General Duty (GD)</option>
                            <option value="Support Staff (SS)" {{ old('unit', $officer->unit) == 'Support Staff (SS)' ? 'selected' : '' }}>Support Staff (SS)</option>
                        </select>
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

// Nigerian Universities List (same as step2)
const nigerianUniversities = [
    'University of Lagos (UNILAG)', 'University of Ibadan (UI)', 'Ahmadu Bello University (ABU)',
    'University of Nigeria, Nsukka (UNN)', 'Obafemi Awolowo University (OAU)', 'University of Benin (UNIBEN)',
    'University of Ilorin (UNILORIN)', 'University of Port Harcourt (UNIPORT)', 'University of Calabar (UNICAL)',
    'University of Jos (UNIJOS)', 'University of Maiduguri (UNIMAID)', 'University of Uyo (UNIUYO)',
    'Nnamdi Azikiwe University (UNIZIK)', 'Federal University of Technology, Akure (FUTA)',
    'Federal University of Technology, Minna (FUTMINNA)', 'Federal University of Technology, Owerri (FUTO)',
    'Federal University of Agriculture, Abeokuta (FUNAAB)', 'Federal University of Agriculture, Makurdi (FUAM)',
    'Federal University of Petroleum Resources, Effurun (FUPRE)', 'Lagos State University (LASU)',
    'Rivers State University (RSU)', 'Delta State University (DELSU)', 'Enugu State University of Science and Technology (ESUT)',
    'Abia State University (ABSU)', 'Imo State University (IMSU)', 'Anambra State University (ANSU)',
    'Bayelsa Medical University (BMU)', 'Benue State University (BSU)', 'Cross River University of Technology (CRUTECH)',
    'Ebonyi State University (EBSU)', 'Ekiti State University (EKSU)', 'Kaduna State University (KASU)',
    'Kano University of Science and Technology (KUST)', 'Kebbi State University of Science and Technology (KSUSTA)',
    'Kwara State University (KWASU)', 'Nasarawa State University (NSUK)', 'Ondo State University of Science and Technology (OSUSTECH)',
    'Osun State University (UNIOSUN)', 'Plateau State University (PLASU)', 'Sokoto State University (SSU)',
    'Taraba State University (TSU)', 'Yobe State University (YSU)', 'Zamfara State University (ZASU)',
    'Covenant University', 'Babcock University', 'Afe Babalola University (ABUAD)', 'American University of Nigeria (AUN)',
    'Bells University of Technology', 'Benson Idahosa University', 'Bingham University', 'Bowen University',
    'Caleb University', 'Caritas University', 'Crawford University', 'Crescent University', 'Edwin Clark University',
    'Elizade University', 'Evangel University', 'Fountain University', 'Godfrey Okoye University', 'Gregory University',
    'Hallmark University', 'Hezekiah University', 'Igbinedion University', 'Joseph Ayo Babalola University',
    'Kings University', 'Kwararafa University', 'Landmark University', 'Lead City University', 'Madonna University',
    'McPherson University', 'Michael Okpara University of Agriculture, Umudike', 'Nile University of Nigeria',
    'Novena University', 'Obong University', 'Oduduwa University', 'Pan-Atlantic University', 'Paul University',
    'Redeemer\'s University', 'Rhema University', 'Ritman University', 'Salem University', 'Samuel Adegboyega University',
    'Southwestern University', 'Summit University', 'Tansian University', 'University of Mkar', 'Veritas University',
    'Wesley University', 'Western Delta University',
    'University of Abomey-Calavi (UAC)', 'University of Parakou', 'National University of Sciences, Technologies, Engineering, and Mathematics (UNSTIM)',
    'National University of Agriculture (UNA)', 'African School of Economics (ASE)', 'ESAE University (École Supérieure d\'Administration, d\'Économie, de Journalisme et des Métiers de l\'Audiovisuel)',
    'ESCAE-University, Benin', 'ISFOP Benin University', 'Houdegbe North American University Benin (HNAUB)',
    'Université Catholique de l\'Afrique de l\'Ouest (UCAO)', 'Université des Sciences et Technologies du Bénin',
    'Université Africaine de Technologie et de Management', 'Université Protestante de l\'Afrique de l\'Ouest',
    'Université Polytechnique Internationale du Bénin', 'Université des Sciences Appliquées et du Management', 'Other'
];

// Qualifications List
const qualifications = [
    'PhD', 'MBBS', 'MSc', 'MPhil', 'MA', 'B TECH', 'BA', 'BSc', 'HND', 'OND', 'WAEC', 'NECO', 'TRADE TEST',
    'DSc', 'DPharm', 'D Litt', 'DDS', 'DA', 'MMed', 'MEng', 'BArch', 'LLM', 'LLB', 'MBA', 'BEd', 'BPharm',
    'BVSc', 'DVM', 'BDS', 'BEng', 'BTech', 'BBA', 'BCom', 'BFA', 'BPE', 'BSc (Ed)', 'PGD', 'PGDE', 'Other'
];

// Comprehensive Disciplines List
const disciplines = [
    'Accounting', 'Actuarial Science', 'Agricultural Economics', 'Agricultural Engineering', 'Agricultural Extension',
    'Agriculture', 'Anatomy', 'Animal Science', 'Architecture', 'Banking and Finance', 'Biochemistry', 'Biology',
    'Biomedical Engineering', 'Botany', 'Business Administration', 'Chemical Engineering', 'Chemistry', 'Civil Engineering',
    'Computer Engineering', 'Computer Science', 'Criminology', 'Crop Science', 'Dentistry', 'Economics', 'Education',
    'Electrical Engineering', 'English Language', 'Environmental Science', 'Estate Management', 'Finance', 'Fisheries',
    'Food Science and Technology', 'Forestry', 'Geography', 'Geology', 'History', 'Human Resource Management',
    'Industrial Chemistry', 'Information Technology', 'Law', 'Library Science', 'Linguistics', 'Marine Engineering',
    'Marketing', 'Mass Communication', 'Mathematics', 'Mechanical Engineering', 'Medicine and Surgery', 'Microbiology',
    'Nursing', 'Petroleum Engineering', 'Pharmacy', 'Philosophy', 'Physics', 'Political Science', 'Psychology',
    'Public Administration', 'Quantity Surveying', 'Sociology', 'Soil Science', 'Statistics', 'Surveying and Geoinformatics',
    'Veterinary Medicine', 'Zoology', 'Agricultural Science', 'Animal Husbandry', 'Building Technology', 'Business Management',
    'Chemical Science', 'Communication Arts', 'Computer Education', 'Crop Production', 'Economics and Statistics',
    'Educational Administration', 'Educational Psychology', 'Electronics Engineering', 'Environmental Management',
    'Food Technology', 'Geophysics', 'Guidance and Counseling', 'Health Education', 'Home Economics', 'Human Kinetics',
    'Industrial Mathematics', 'Insurance', 'International Relations', 'Journalism', 'Laboratory Technology', 'Land Surveying',
    'Management', 'Marine Science', 'Materials Science', 'Mechanical Engineering Technology', 'Medical Laboratory Science',
    'Metallurgical Engineering', 'Nutrition and Dietetics', 'Office Technology and Management', 'Operations Research',
    'Optometry', 'Peace and Conflict Studies', 'Petroleum and Gas Engineering', 'Physics with Electronics', 'Plant Science',
    'Project Management', 'Public Health', 'Pure and Applied Mathematics', 'Radiography', 'Real Estate Management',
    'Religious Studies', 'Science Education', 'Social Work', 'Software Engineering', 'Soil Science and Land Management',
    'Statistics and Computer Science', 'Telecommunications Engineering', 'Textile Technology', 'Transport Management',
    'Urban and Regional Planning', 'Water Resources Engineering', 'Wildlife Management'
];

let educationEntryCount = 0;

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

document.addEventListener('DOMContentLoaded', async function() {
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
    const stateSelect = document.getElementById('state-select');
    const savedState = '{{ old('state_of_origin', $officer->state_of_origin) }}';
    const savedLga = '{{ old('lga', $officer->lga) }}';
    
    if (savedState) {
        loadLGAsForState(savedState, savedLga);
    }
    
    stateSelect.addEventListener('change', function() {
        const selectedState = this.value;
        if (selectedState) {
            loadLGAsForState(selectedState);
        } else {
            clearLgaSelection();
        }
    });
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
            
            const zoneSelect = document.getElementById('zone_id');
            zoneSelect.addEventListener('change', function() {
                const selectedZoneId = this.value;
                if (selectedZoneId) {
                    loadCommandsForZone(selectedZoneId);
                } else {
                    clearCommandSelection();
                }
            });
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
                <select name="education[${entryId}][qualification]" class="kt-input education-qualification" required>
                    <option value="">-- Select Qualification --</option>
                    ${qualifications.map(qual => 
                        `<option value="${qual}" ${savedQualification == qual ? 'selected' : ''}>${qual}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Discipline <span class="text-muted">(Optional)</span></label>
                <select id="discipline_select_${entryId}" class="kt-input flex-1 education-discipline-select" onchange="handleDisciplineChange(${entryId})">
                    <option value="">-- Select Discipline --</option>
                    ${disciplines.map(disc => 
                        `<option value="${disc}" ${savedDiscipline == disc ? 'selected' : ''}>${disc}</option>`
                    ).join('')}
                    <option value="__CUSTOM__" ${isCustomDiscipline ? 'selected' : ''}>-- Custom (Enter below) --</option>
                </select>
                <input type="text" 
                       id="discipline_custom_${entryId}"
                       class="kt-input mt-2 education-discipline-custom ${isCustomDiscipline ? '' : 'hidden'}" 
                       value="${isCustomDiscipline ? savedDiscipline : ''}"
                       placeholder="Enter custom discipline..."
                       oninput="handleCustomDiscipline(${entryId})">
                <input type="hidden" 
                       id="discipline_final_${entryId}"
                       name="education[${entryId}][discipline]"
                       value="${savedDiscipline}">
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

function handleDisciplineChange(entryId) {
    const disciplineSelect = document.getElementById(`discipline_select_${entryId}`);
    const disciplineCustom = document.getElementById(`discipline_custom_${entryId}`);
    const disciplineFinal = document.getElementById(`discipline_final_${entryId}`);
    
    if (!disciplineSelect || !disciplineCustom || !disciplineFinal) return;
    
    const selectedValue = disciplineSelect.value;
    
    if (selectedValue === '__CUSTOM__') {
        disciplineCustom.classList.remove('hidden');
        disciplineCustom.focus();
    } else if (selectedValue) {
        disciplineCustom.classList.add('hidden');
        disciplineCustom.value = '';
        disciplineFinal.value = selectedValue;
    } else {
        disciplineCustom.classList.add('hidden');
        disciplineCustom.value = '';
        disciplineFinal.value = '';
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
    const rankSelect = document.getElementById('substantive_rank');
    const gradeLevelSelect = document.getElementById('salary_grade_level');
    
    if (!rankSelect || !gradeLevelSelect) return;
    
    // Set initial grade level if rank is already selected
    const currentRank = rankSelect.value;
    if (currentRank && rankToGradeLevel[currentRank]) {
        gradeLevelSelect.value = rankToGradeLevel[currentRank];
    }
    
    // Listen for rank changes
    rankSelect.addEventListener('change', function() {
        const selectedRank = this.value;
        if (selectedRank && rankToGradeLevel[selectedRank]) {
            gradeLevelSelect.value = rankToGradeLevel[selectedRank];
        } else {
            // If rank is cleared or doesn't have a mapping, clear grade level
            gradeLevelSelect.value = '';
        }
    });
}

</script>
@endpush
@endsection

