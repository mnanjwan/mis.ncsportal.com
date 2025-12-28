@extends('layouts.public')

@section('title', 'Recruit Onboarding - Step 1: Personal Information')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Progress Indicator -->
        <div class="kt-card">
            <div class="kt-card-content p-4 lg:p-5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-2">
                    <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #068b57; color: white;">1</div>
                        <span class="text-xs sm:text-sm font-medium" style="color: #068b57;">Personal Information</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">2</div>
                        <span class="text-xs sm:text-sm" style="color: #6c757d;">Employment Details</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">3</div>
                        <span class="text-xs sm:text-sm" style="color: #6c757d;">Banking Information</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">4</div>
                        <span class="text-xs sm:text-sm" style="color: #6c757d;">Next of Kin</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">5</div>
                        <span class="text-xs sm:text-sm" style="color: #6c757d;">Preview</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <div class="flex items-center justify-between">
                    <h3 class="kt-card-title">Personal Information</h3>
                    @if(isset($recruit) && $recruit && $recruit->appointment_number)
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-secondary-foreground">Appointment Number:</span>
                        <span class="text-lg font-semibold text-primary">{{ $recruit->appointment_number }}</span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="kt-card-content">
                @if($errors->any())
                <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
                    <div class="kt-card-content p-4">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-3">
                                <i class="ki-filled ki-information text-danger text-xl"></i>
                                <p class="text-sm font-semibold text-danger">Please fix the following errors:</p>
                            </div>
                            <ul class="list-disc list-inside text-sm text-danger ml-8">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <form action="{{ route('recruit.onboarding.step1') }}" method="POST" id="createRecruitForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ request('token') }}">

                    <div class="flex flex-col gap-5">
                        <!-- Personal Information Fields (Matching Onboarding Step 1) -->
                        <div class="grid lg:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Initials <span class="text-danger">*</span></label>
                                <input type="text" name="initials" class="kt-input" value="{{ old('initials', $savedData['initials'] ?? $recruit->initials ?? '') }}" required/>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Surname <span class="text-danger">*</span></label>
                                <input type="text" name="surname" class="kt-input" value="{{ old('surname', $savedData['surname'] ?? $recruit->surname ?? '') }}" required/>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="kt-input" value="{{ old('first_name', $savedData['first_name'] ?? $recruit->first_name ?? '') }}" required/>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="kt-input" value="{{ old('middle_name', $savedData['middle_name'] ?? $recruit->middle_name ?? '') }}"/>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Sex <span class="text-danger">*</span></label>
                                <select name="sex" class="kt-input" required>
                                    <option value="">Select...</option>
                                    <option value="M" {{ old('sex', $savedData['sex'] ?? $recruit->sex ?? '') == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('sex', $savedData['sex'] ?? $recruit->sex ?? '') == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" class="kt-input" value="{{ old('date_of_birth', $savedData['date_of_birth'] ?? ($recruit->date_of_birth ? \Carbon\Carbon::parse($recruit->date_of_birth)->format('Y-m-d') : '')) }}" required max="{{ date('Y-m-d', strtotime('-18 years')) }}"/>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">State of Origin <span class="text-danger">*</span></label>
                                <select name="state_of_origin" class="kt-input" id="state-select" required>
                                    <option value="">Select State...</option>
                                </select>
                                <span class="error-message text-danger text-sm hidden"></span>
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
                                           value="{{ old('lga', $savedData['lga'] ?? $recruit->lga ?? '') }}"
                                           required>
                                    <div id="lga_dropdown" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                                <div id="selected_lga" class="mt-2 p-2 bg-muted/50 rounded-lg hidden">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium" id="selected_lga_name"></span>
                                        <button type="button" 
                                                class="kt-btn kt-btn-sm kt-btn-ghost text-danger"
                                                onclick="clearLgaSelection()">
                                            <i class="ki-filled ki-cross"></i>
                                        </button>
                                    </div>
                                </div>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Geopolitical Zone <span class="text-danger">*</span></label>
                                <select name="geopolitical_zone" class="kt-input" required>
                                    <option value="">Select Zone...</option>
                                    <option value="North Central" {{ old('geopolitical_zone', $savedData['geopolitical_zone'] ?? $recruit->geopolitical_zone ?? '') == 'North Central' ? 'selected' : '' }}>North Central</option>
                                    <option value="North East" {{ old('geopolitical_zone', $savedData['geopolitical_zone'] ?? $recruit->geopolitical_zone ?? '') == 'North East' ? 'selected' : '' }}>North East</option>
                                    <option value="North West" {{ old('geopolitical_zone', $savedData['geopolitical_zone'] ?? $recruit->geopolitical_zone ?? '') == 'North West' ? 'selected' : '' }}>North West</option>
                                    <option value="South East" {{ old('geopolitical_zone', $savedData['geopolitical_zone'] ?? $recruit->geopolitical_zone ?? '') == 'South East' ? 'selected' : '' }}>South East</option>
                                    <option value="South South" {{ old('geopolitical_zone', $savedData['geopolitical_zone'] ?? $recruit->geopolitical_zone ?? '') == 'South South' ? 'selected' : '' }}>South South</option>
                                    <option value="South West" {{ old('geopolitical_zone', $savedData['geopolitical_zone'] ?? $recruit->geopolitical_zone ?? '') == 'South West' ? 'selected' : '' }}>South West</option>
                                </select>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Marital Status <span class="text-danger">*</span></label>
                                <select name="marital_status" class="kt-input" required>
                                    <option value="">Select...</option>
                                    <option value="Single" {{ old('marital_status', $savedData['marital_status'] ?? $recruit->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ old('marital_status', $savedData['marital_status'] ?? $recruit->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Divorced" {{ old('marital_status', $savedData['marital_status'] ?? $recruit->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="Widowed" {{ old('marital_status', $savedData['marital_status'] ?? $recruit->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone_number" class="kt-input" value="{{ old('phone_number', $savedData['phone_number'] ?? $recruit->phone_number ?? '') }}" required/>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="kt-input" value="{{ old('email', $savedData['email'] ?? $recruit->email ?? '') }}" required/>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    Personal email for onboarding
                                </p>
                                <span class="error-message text-danger text-sm hidden"></span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Residential Address <span class="text-danger">*</span></label>
                            <textarea name="residential_address" class="kt-input" rows="3" required>{{ old('residential_address', $savedData['residential_address'] ?? $recruit->residential_address ?? '') }}</textarea>
                            <span class="error-message text-danger text-sm hidden"></span>
                        </div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Permanent Home Address <span class="text-danger">*</span></label>
                            <textarea name="permanent_home_address" class="kt-input" rows="3" required>{{ old('permanent_home_address', $savedData['permanent_home_address'] ?? $recruit->permanent_home_address ?? '') }}</textarea>
                            <span class="error-message text-danger text-sm hidden"></span>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-sm text-secondary-foreground">
                                    <strong>Note:</strong> Employment details, banking information, and next of kin will be collected in the following steps.
                                </p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-5 border-t border-input">
                            <button type="submit" class="kt-btn kt-btn-primary w-full sm:flex-1 whitespace-nowrap">
                                Next: Employment Details
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('styles')
<style>
    /* Ensure all asterisks in forms are red */
    .kt-form-label span.text-danger,
    .kt-form-label .text-danger,
    label span.text-danger,
    label .text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Nigerian States and LGAs data (same as onboarding)
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

// Load Nigerian states
document.addEventListener('DOMContentLoaded', () => {
    const states = Object.keys(nigerianStatesLGAs);
    
    const stateSelect = document.getElementById('state-select');
    const savedState = '{{ old('state_of_origin', $savedData['state_of_origin'] ?? $recruit->state_of_origin ?? '') }}';
    const savedLga = '{{ old('lga', $savedData['lga'] ?? $recruit->lga ?? '') }}';
    
    states.forEach(state => {
        const option = document.createElement('option');
        option.value = state;
        option.textContent = state;
        if (state === savedState) {
            option.selected = true;
        }
        stateSelect.appendChild(option);
    });
    
    // If state is already selected, load LGAs
    if (savedState) {
        loadLGAsForState(savedState, savedLga);
    }
    
    // Handle state change
    stateSelect.addEventListener('change', function() {
        const selectedState = this.value;
        if (selectedState) {
            loadLGAsForState(selectedState);
        } else {
            clearLgaSelection();
        }
    });
});

function loadLGAsForState(state, savedLga = '') {
    const lgas = nigerianStatesLGAs[state] || [];
    const lgaSearch = document.getElementById('lga_search');
    const lgaHidden = document.getElementById('lga_hidden');
    const lgaDropdown = document.getElementById('lga_dropdown');
    const selectedLga = document.getElementById('selected_lga');
    const selectedLgaName = document.getElementById('selected_lga_name');
    
    // Clear previous selection
    lgaSearch.value = '';
    lgaHidden.value = '';
    selectedLga.classList.add('hidden');
    
    // Enable search input
    lgaSearch.readOnly = false;
    lgaSearch.placeholder = 'Search LGA...';
    
    // Store current LGAs for this state
    window.currentLGAs = lgas.map(lga => ({ name: lga }));
    
    // If saved LGA exists, set it
    if (savedLga && lgas.includes(savedLga)) {
        lgaSearch.value = savedLga;
        lgaHidden.value = savedLga;
        selectedLgaName.textContent = savedLga;
        selectedLga.classList.remove('hidden');
    }
    
    // Initialize searchable select
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
    
    // Remove existing listeners by cloning
    const newSearch = lgaSearch.cloneNode(true);
    lgaSearch.parentNode.replaceChild(newSearch, lgaSearch);
    
    const searchInput = document.getElementById('lga_search');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const lgas = window.currentLGAs || [];
        
        const filtered = lgas.filter(lga => 
            lga.name.toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0 && searchTerm.length > 0) {
            lgaDropdown.innerHTML = filtered.map(lga => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-name="' + lga.name + '">' + lga.name + '</div>'
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
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !lgaDropdown.contains(e.target)) {
            lgaDropdown.classList.add('hidden');
        }
    });
}

// Form validation
function validateForm() {
    let isValid = true;
    
    // Required fields
    const requiredFields = {
        'initials': 'Initials is required',
        'surname': 'Surname is required',
        'first_name': 'First Name is required',
        'sex': 'Sex is required',
        'date_of_birth': 'Date of Birth is required',
        'state_of_origin': 'State of Origin is required',
        'lga': 'LGA is required',
        'geopolitical_zone': 'Geopolitical Zone is required',
        'marital_status': 'Marital Status is required',
        'phone_number': 'Phone Number is required',
        'email': 'Email is required',
        'residential_address': 'Residential Address is required',
        'permanent_home_address': 'Permanent Home Address is required',
    };
    
    // Validate required fields
    Object.keys(requiredFields).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        const value = input?.value?.trim();
        
        if (!value || value === '') {
            isValid = false;
            const errorSpan = input?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = requiredFields[field];
                errorSpan.classList.remove('hidden');
                input?.classList.add('border-danger');
            }
        } else {
            const errorSpan = input?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.classList.add('hidden');
                input?.classList.remove('border-danger');
            }
        }
    });
    
    // Validate email format
    const email = document.querySelector('[name="email"]')?.value?.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        isValid = false;
        const errorSpan = document.querySelector('[name="email"]')?.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = 'Please enter a valid email address';
            errorSpan.classList.remove('hidden');
        }
    }
    
    return isValid;
}

// Form submission handler
document.getElementById('createRecruitForm').addEventListener('submit', function(e) {
    if (!validateForm()) {
        e.preventDefault();
        // Scroll to first error
        const firstError = document.querySelector('.error-message:not(.hidden)');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
});
</script>
@endpush
@endsection
