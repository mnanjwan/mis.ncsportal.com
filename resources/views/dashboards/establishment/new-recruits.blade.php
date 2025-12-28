@extends('layouts.app')

@section('title', 'New Recruits')
@section('page-title', 'New Recruits')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <span class="text-primary">New Recruits</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Success/Error Messages -->
        @if(session('bulk_results'))
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Bulk Onboarding Results</h3>
                </div>
                <div class="kt-card-content">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm">Recruit ID</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm">Email</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm">Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('bulk_results') as $result)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3 px-4 text-sm font-mono">{{ $result['recruit_id'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $result['email'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4">
                                            @if($result['status'] === 'success')
                                                <span class="kt-badge kt-badge-success">Success</span>
                                            @else
                                                <span class="kt-badge kt-badge-danger">Error</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $result['message'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Initiate Onboarding Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Initiate Onboarding</h3>
            </div>
            <div class="kt-card-content">
                <!-- Tabs for Single vs Bulk -->
                <div class="flex border-b border-border mb-5">
                    <button type="button" 
                            onclick="showTab('single')" 
                            id="tab-single-btn"
                            class="px-4 py-2 text-sm font-medium border-b-2 border-primary text-primary">
                        Single Entry
                    </button>
                    <button type="button" 
                            onclick="showTab('bulk')" 
                            id="tab-bulk-btn"
                            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-secondary-foreground hover:text-primary">
                        Bulk Upload (Up to 10)
                    </button>
                    <button type="button" 
                            onclick="showTab('csv')" 
                            id="tab-csv-btn"
                            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-secondary-foreground hover:text-primary">
                        CSV Upload
                    </button>
                </div>

                <!-- Single Entry Form -->
                <div id="single-tab" class="tab-content">
                    <form action="{{ route('establishment.onboarding.initiate-create') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-foreground">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email"
                                       value="{{ old('email') }}"
                                       class="kt-input @error('email') kt-input-error @enderror"
                                       placeholder="recruit@example.com"
                                       required>
                                @error('email')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-secondary-foreground">
                                    Personal email for onboarding link
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="initials" class="block text-sm font-medium text-foreground">
                                    Initials <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="initials" 
                                       id="initials"
                                       value="{{ old('initials') }}"
                                       class="kt-input @error('initials') kt-input-error @enderror"
                                       placeholder="e.g., J.D"
                                       maxlength="50"
                                       required>
                                @error('initials')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="surname" class="block text-sm font-medium text-foreground">
                                    Surname <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="surname" 
                                       id="surname"
                                       value="{{ old('surname') }}"
                                       class="kt-input @error('surname') kt-input-error @enderror"
                                       placeholder="e.g., Adeleke"
                                       maxlength="255"
                                       required>
                                @error('surname')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="substantive_rank" class="block text-sm font-medium text-foreground">
                                    Substantive Rank <span class="text-danger">*</span>
                                </label>
                                <select name="substantive_rank" 
                                       id="substantive_rank"
                                       class="kt-input @error('substantive_rank') kt-input-error @enderror"
                                       required>
                                    <option value="">Select Rank...</option>
                                    @php
                                        $ranks = ['DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I', 'CA II', 'CA III'];
                                    @endphp
                                    @foreach($ranks as $rank)
                                        <option value="{{ $rank }}" {{ old('substantive_rank') == $rank ? 'selected' : '' }}>
                                            {{ $rank }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('substantive_rank')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-secondary-foreground">
                                    Used to determine appointment number prefix (CDT/RCT)
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="salary_grade_level" class="block text-sm font-medium text-foreground">
                                    Salary Grade Level <span class="text-danger">*</span>
                                </label>
                                <select name="salary_grade_level" 
                                       id="salary_grade_level"
                                       class="kt-input @error('salary_grade_level') kt-input-error @enderror"
                                       required>
                                    <option value="">Select Grade Level...</option>
                                    @php
                                        $gradeLevels = ['GL 03', 'GL 04', 'GL 05', 'GL 06', 'GL 07', 'GL 08', 'GL 09', 'GL 10', 'GL 11', 'GL 12', 'GL 13', 'GL 14', 'GL 15', 'GL 16', 'GL 17', 'GL 18'];
                                    @endphp
                                    @foreach($gradeLevels as $gl)
                                        <option value="{{ $gl }}" {{ old('salary_grade_level') == $gl ? 'selected' : '' }}>
                                            {{ $gl }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('salary_grade_level')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-secondary-foreground">
                                    Used with rank to determine CDT vs RCT prefix
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="date_of_first_appointment" class="block text-sm font-medium text-foreground">
                                    Date of First Appointment <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="date_of_first_appointment" 
                                       id="date_of_first_appointment"
                                       value="{{ old('date_of_first_appointment') }}"
                                       class="kt-input @error('date_of_first_appointment') kt-input-error @enderror"
                                       required>
                                @error('date_of_first_appointment')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="date_of_present_appointment" class="block text-sm font-medium text-foreground">
                                    Date of Present Appointment <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="date_of_present_appointment" 
                                       id="date_of_present_appointment"
                                       value="{{ old('date_of_present_appointment') }}"
                                       class="kt-input @error('date_of_present_appointment') kt-input-error @enderror"
                                       required>
                                @error('date_of_present_appointment')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="date_posted_to_station" class="block text-sm font-medium text-foreground">
                                    Date Posted to Station <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="date_posted_to_station" 
                                       id="date_posted_to_station"
                                       value="{{ old('date_posted_to_station') }}"
                                       class="kt-input @error('date_posted_to_station') kt-input-error @enderror"
                                       required>
                                @error('date_posted_to_station')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="command_id" class="block text-sm font-medium text-foreground">
                                    Command/Present Station <span class="text-danger">*</span>
                                </label>
                                <select name="command_id" 
                                       id="command_id"
                                       class="kt-input @error('command_id') kt-input-error @enderror"
                                       required>
                                    <option value="">Select Command...</option>
                                    @php
                                        $commands = \App\Models\Command::orderBy('name')->get();
                                    @endphp
                                    @foreach($commands as $command)
                                        <option value="{{ $command->id }}" {{ old('command_id') == $command->id ? 'selected' : '' }}>
                                            {{ $command->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('command_id')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="unit" class="block text-sm font-medium text-foreground">
                                    Unit
                                </label>
                                <select name="unit" 
                                       id="unit"
                                       class="kt-input @error('unit') kt-input-error @enderror">
                                    <option value="">Select Unit...</option>
                                    <option value="General Duty (GD)" {{ old('unit') == 'General Duty (GD)' ? 'selected' : '' }}>General Duty (GD)</option>
                                    <option value="Support Staff (SS)" {{ old('unit') == 'Support Staff (SS)' ? 'selected' : '' }}>Support Staff (SS)</option>
                                </select>
                                @error('unit')
                                    <p class="text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="kt-card bg-info/10 border border-info/20 p-4">
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-information text-info text-lg mt-0.5"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-info mb-1">What happens next?</p>
                                    <ul class="text-xs text-secondary-foreground space-y-1 list-disc list-inside">
                                        <li>A recruit record will be created with the information provided</li>
                                        <li>An onboarding link will be sent to the email address</li>
                                        <li>The recruit will complete onboarding steps 1-4 and upload documents</li>
                                        <li>You can verify their documents after completion</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-send"></i>
                                Create Intake & Send Onboarding Link
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bulk Upload Form (Up to 10) -->
                <div id="bulk-tab" class="tab-content hidden">
                    <form action="{{ route('establishment.onboarding.bulk-initiate') }}" method="POST" class="space-y-4" id="bulk-form">
                        @csrf
                        <div id="bulk-entries" class="space-y-4">
                            <!-- Entries will be added here -->
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-border">
                            <button type="button" onclick="addBulkEntry()" class="kt-btn kt-btn-secondary" id="add-entry-btn">
                                <i class="ki-filled ki-plus"></i> Add Entry
                            </button>
                            <button type="submit" class="kt-btn kt-btn-primary" id="bulk-submit-btn" disabled>
                                <i class="ki-filled ki-send"></i>
                                Send Onboarding Links
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CSV Upload Form -->
                <div id="csv-tab" class="tab-content hidden">
                    <form action="{{ route('establishment.onboarding.csv-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label for="csv_file" class="block text-sm font-medium text-foreground">
                                CSV File <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   name="csv_file" 
                                   id="csv_file"
                                   accept=".csv,.txt"
                                   class="kt-input @error('csv_file') kt-input-error @enderror"
                                   required>
                            @error('csv_file')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-secondary-foreground">
                                CSV file must have columns: <strong>email</strong>, <strong>initials</strong>, <strong>surname</strong>, <strong>substantive_rank</strong>, <strong>salary_grade_level</strong>, <strong>date_of_first_appointment</strong>, <strong>date_of_present_appointment</strong>, <strong>date_posted_to_station</strong>, <strong>command_id</strong>, <strong>unit</strong> (optional). Maximum 10 entries per upload.
                            </p>
                            <div class="mt-2 p-3 bg-muted/50 rounded border border-input">
                                <p class="text-xs font-semibold mb-2">CSV Format Example:</p>
                                <pre class="text-xs font-mono">email,initials,surname,substantive_rank,salary_grade_level,date_of_first_appointment,date_of_present_appointment,date_posted_to_station,command_id,unit
recruit1@example.com,J.D,Adeleke,ASC II,GL 08,2024-01-15,2024-01-15,2024-01-20,1,General Duty (GD)
recruit2@example.com,M.K,Smith,IC,GL 07,2024-01-15,2024-01-15,2024-01-20,1,Support Staff (SS)</pre>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-file-up"></i>
                                Upload CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-mono">New Recruits</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('establishment.new-recruits.create') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Add New Intake
                </a>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="kt-card bg-primary/10 border border-primary/20" id="bulkActionsBar" style="display: none;">
            <div class="kt-card-content p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-check-circle text-primary text-xl"></i>
                        <span class="text-sm font-medium text-foreground">
                            <span id="selectedCount">0</span> intake(s) selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="clearSelection()" class="kt-btn kt-btn-sm kt-btn-ghost">
                            Clear Selection
                        </button>
                        <button type="button" onclick="showBulkDeleteModal()" class="kt-btn kt-btn-sm kt-btn-danger" id="bulkDeleteBtn">
                            <i class="ki-filled ki-trash"></i> Delete Selected
                        </button>
                        <button type="button" onclick="showBulkAssignModal()" class="kt-btn kt-btn-sm kt-btn-primary" id="bulkAssignBtn">
                            <i class="ki-filled ki-check"></i> Assign Appointment Numbers
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">New Recruits</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <form id="bulkAssignForm" action="{{ route('establishment.assign-appointment-numbers') }}" method="POST" style="display: none;">
                    @csrf
                    <div id="bulkOfficerIdsContainer"></div>
                    <input type="hidden" name="appointment_number_prefix" id="bulkPrefix" value="">
                </form>
                
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground w-12" style="white-space: nowrap;">
                                    <input type="checkbox" id="selectAll" class="kt-checkbox">
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Recruit Details
                                        @if(request('sort_by') === 'name')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'appointment_number', 'sort_order' => request('sort_by') === 'appointment_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Appointment Number
                                        @if(request('sort_by') === 'appointment_number')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'substantive_rank', 'sort_order' => request('sort_by') === 'substantive_rank' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Entry Rank
                                        @if(request('sort_by') === 'substantive_rank')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Appointment Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Onboarding Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Verification Status
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recruits as $recruit)
                                @php
                                    $initials = $recruit->initials ?? '';
                                    $surname = $recruit->surname ?? '';
                                    $fullName = trim("{$initials} {$surname}");
                                    $avatarInitials = strtoupper(($initials[0] ?? '') . ($surname[0] ?? ''));
                                    $canAssign = !$recruit->appointment_number;
                                    $canDelete = !$recruit->service_number; // Can delete if no service number
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        @if($canAssign || $canDelete)
                                            <input type="checkbox" 
                                                   name="selected_recruits[]" 
                                                   value="{{ $recruit->id }}"
                                                   class="kt-checkbox recruit-checkbox"
                                                   data-recruit-id="{{ $recruit->id }}"
                                                   data-can-delete="{{ $canDelete ? '1' : '0' }}">
                                        @endif
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm" style="flex-shrink: 0;">
                                                {{ $avatarInitials }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-foreground">{{ $fullName }}</div>
                                                <div class="text-xs text-secondary-foreground">{{ $recruit->email ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="text-sm font-mono text-foreground">
                                            {{ $recruit->appointment_number ?? 'Not Assigned' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $recruit->substantive_rank ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        @if($recruit->appointment_number && $recruit->service_number)
                                            <span class="kt-badge kt-badge-success kt-badge-sm">Complete</span>
                                        @elseif($recruit->appointment_number)
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">Pending Service Number</span>
                                        @else
                                            <span class="kt-badge kt-badge-secondary kt-badge-sm">Pending Appointment</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        @if($recruit->onboarding_status === 'verified')
                                            <span class="kt-badge kt-badge-success kt-badge-sm">
                                                <i class="ki-filled ki-check-circle"></i> Verified
                                            </span>
                                        @elseif($recruit->onboarding_status === 'completed')
                                            <span class="kt-badge kt-badge-info kt-badge-sm">
                                                <i class="ki-filled ki-check"></i> Completed
                                            </span>
                                        @elseif($recruit->onboarding_status === 'in_progress')
                                            <span class="kt-badge kt-badge-warning kt-badge-sm">
                                                <i class="ki-filled ki-clock"></i> In Progress
                                            </span>
                                        @elseif($recruit->onboarding_status === 'link_sent')
                                            <span class="kt-badge kt-badge-secondary kt-badge-sm">
                                                <i class="ki-filled ki-send"></i> Link Sent
                                            </span>
                                        @else
                                            <span class="kt-badge kt-badge-secondary kt-badge-sm">
                                                <i class="ki-filled ki-information"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        @if($recruit->verification_status === 'verified')
                                            <span class="kt-badge kt-badge-success kt-badge-sm">Verified</span>
                                        @elseif($recruit->verification_status === 'rejected')
                                            <span class="kt-badge kt-badge-danger kt-badge-sm">Rejected</span>
                                        @else
                                            <span class="kt-badge kt-badge-secondary kt-badge-sm">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                        <div class="relative inline-block text-right">
                                            <button type="button" 
                                                    onclick="toggleActionMenu({{ $recruit->id }}, event)"
                                                    class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost hover:bg-muted"
                                                    id="action-btn-{{ $recruit->id }}"
                                                    aria-label="Actions">
                                                <i class="ki-filled ki-dots-vertical text-lg"></i>
                                            </button>
                                            <div class="fixed w-56 bg-background border border-border rounded-md shadow-lg z-50 hidden"
                                                 id="action-menu-{{ $recruit->id }}">
                                                <div class="py-1">
                                                    @if($canAssign)
                                                        <button type="button" 
                                                                onclick="showAssignAppointmentModal({{ $recruit->id }}, '{{ $fullName }}'); closeActionMenu({{ $recruit->id }});"
                                                                class="w-full text-left px-4 py-2 text-sm text-foreground hover:bg-muted/50 transition-colors flex items-center gap-2">
                                                            <i class="ki-filled ki-check text-primary"></i>
                                                            <span>Assign Appointment</span>
                                                        </button>
                                                    @endif
                                                    @if($recruit->onboarding_status === 'pending' || $recruit->onboarding_status === 'link_sent')
                                                        <form action="{{ route('establishment.onboarding.initiate') }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="recruit_id" value="{{ $recruit->id }}">
                                                            <button type="submit" 
                                                                    onclick="closeActionMenu({{ $recruit->id }});"
                                                                    class="w-full text-left px-4 py-2 text-sm text-foreground hover:bg-muted/50 transition-colors flex items-center gap-2">
                                                                <i class="ki-filled ki-send text-primary"></i>
                                                                <span>Send Onboarding Link</span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($recruit->onboarding_status === 'link_sent' || $recruit->onboarding_status === 'in_progress')
                                                        <form action="{{ route('establishment.onboarding.resend-link', $recruit->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    onclick="closeActionMenu({{ $recruit->id }});"
                                                                    class="w-full text-left px-4 py-2 text-sm text-foreground hover:bg-muted/50 transition-colors flex items-center gap-2">
                                                                <i class="ki-filled ki-arrows-circle text-info"></i>
                                                                <span>Resend Link</span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($recruit->onboarding_status === 'completed' && $recruit->verification_status === 'pending')
                                                        <a href="{{ route('establishment.new-recruits.view', $recruit->id) }}" 
                                                           class="block px-4 py-2 text-sm text-info hover:bg-info/10 transition-colors flex items-center gap-2">
                                                            <i class="ki-filled ki-eye"></i>
                                                            <span>View Details & Documents</span>
                                                        </a>
                                                        <button type="button" 
                                                                onclick="showVerifyModal({{ $recruit->id }}, '{{ $fullName }}'); closeActionMenu({{ $recruit->id }});"
                                                                class="w-full text-left px-4 py-2 text-sm text-success hover:bg-success/10 transition-colors flex items-center gap-2">
                                                            <i class="ki-filled ki-check-circle"></i>
                                                            <span>Verify Documents</span>
                                                        </button>
                                                    @else
                                                    <a href="{{ route('establishment.new-recruits.view', $recruit->id) }}" 
                                                       class="block px-4 py-2 text-sm text-foreground hover:bg-muted/50 transition-colors flex items-center gap-2">
                                                        <i class="ki-filled ki-eye text-info"></i>
                                                        <span>View Details</span>
                                                    </a>
                                                    @endif
                                                    <a href="{{ route('hrd.officers.show', $recruit->id) }}" 
                                                       class="block px-4 py-2 text-sm text-foreground hover:bg-muted/50 transition-colors flex items-center gap-2">
                                                        <i class="ki-filled ki-user text-secondary"></i>
                                                        <span>View in HRD</span>
                                                    </a>
                                                    @if($canAssign && !$recruit->service_number)
                                                        <button type="button" 
                                                                onclick="showDeleteModal({{ $recruit->id }}, '{{ $fullName }}', '{{ $recruit->email }}'); closeActionMenu({{ $recruit->id }});"
                                                                class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-danger/10 transition-colors flex items-center gap-2">
                                                            <i class="ki-filled ki-trash"></i>
                                                            <span>Delete</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center">
                                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground">No new recruits found</p>
                                        <p class="text-sm text-muted-foreground mt-1">Add a new recruit to get started</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assign Appointment Modal (Single) -->
        <div class="kt-modal" data-kt-modal="true" id="assign-appointment-modal">
            <div class="kt-modal-content max-w-[400px] top-[20%]">
                <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                            <i class="ki-filled ki-information text-primary text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Assign Appointment Number</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-4 px-5">
                    <p class="text-sm text-secondary-foreground mb-3">
                        Assign appointment number to <strong id="modal-recruit-name"></strong>?
                    </p>
                    <div class="kt-card bg-primary/10 border border-primary/20 p-3">
                        <div class="flex items-start gap-2">
                            <i class="ki-filled ki-information text-primary text-sm mt-0.5"></i>
                            <div class="text-xs text-secondary-foreground">
                                <p class="font-medium text-primary mb-1">Auto Prefix</p>
                                <p>Prefix (CDT or RCT) will be automatically determined based on the recruit's rank and GL level.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-modal-footer py-3 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('establishment.assign-appointment-numbers') }}" method="POST" class="inline" id="assignAppointmentForm">
                        @csrf
                        <input type="hidden" name="officer_ids[]" id="modal-officer-id">
                        <input type="hidden" name="auto_prefix" value="1">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i>
                            <span>Assign</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Recruit Modal -->
        <div class="kt-modal" data-kt-modal="true" id="delete-recruit-modal">
            <div class="kt-modal-content max-w-[400px] top-[20%]">
                <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                            <i class="ki-filled ki-information text-danger text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Delete Recruit</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-4 px-5">
                    <p class="text-sm text-secondary-foreground">
                        Are you sure you want to delete <strong id="delete-recruit-name"></strong>?
                    </p>
                    <p class="text-xs text-secondary-foreground mt-2">
                        Email: <span id="delete-recruit-email" class="font-mono"></span>
                    </p>
                    <p class="text-sm text-danger mt-3 font-medium">
                        This action cannot be undone. Only recruits without appointment numbers can be deleted.
                    </p>
                </div>
                <div class="kt-modal-footer py-3 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="" method="POST" class="inline" id="deleteRecruitForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-trash"></i>
                            <span>Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Delete Modal -->
        <div class="kt-modal" data-kt-modal="true" id="bulk-delete-modal">
            <div class="kt-modal-content max-w-[500px] top-[20%]">
                <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                            <i class="ki-filled ki-information text-danger text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Delete Selected Recruits</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-4 px-5">
                    <p class="text-sm text-secondary-foreground mb-3">
                        Are you sure you want to delete <strong id="bulk-delete-count">0</strong> selected intake(s)?
                    </p>
                    <div class="kt-card bg-warning/10 border border-warning/20 mb-3">
                        <div class="kt-card-content p-3">
                            <div class="flex items-start gap-2">
                                <i class="ki-filled ki-information text-warning text-sm mt-0.5"></i>
                                <div class="text-xs text-secondary-foreground">
                                    <p class="font-medium text-warning mb-1">Important:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Only recruits without service numbers can be deleted</li>
                                        <li>Recruits with service numbers will be automatically excluded</li>
                                        <li>This action cannot be undone</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="bulk-delete-warning" class="hidden">
                        <p class="text-sm text-danger font-medium">
                            <span id="bulk-delete-excluded-count">0</span> intake(s) have service numbers and cannot be deleted.
                        </p>
                    </div>
                </div>
                <div class="kt-modal-footer py-3 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <form action="{{ route('establishment.new-recruits.bulk-delete') }}" method="POST" class="inline" id="bulkDeleteForm">
                        @csrf
                        @method('DELETE')
                        <div id="bulkDeleteIdsContainer"></div>
                        <button type="submit" class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-trash"></i>
                            <span>Delete Selected</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Verify Recruit Modal -->
        <div class="kt-modal" data-kt-modal="true" id="verify-recruit-modal">
            <div class="kt-modal-content max-w-[500px] top-[20%]">
                <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                            <i class="ki-filled ki-check-circle text-success text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Verify Recruit Documents</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <form action="" method="POST" id="verifyRecruitForm">
                    @csrf
                    <div class="kt-modal-body py-4 px-5">
                        <p class="text-sm text-secondary-foreground mb-4">
                            Verify documents for <strong id="verify-recruit-name"></strong>?
                        </p>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Verification Status <span class="text-danger">*</span>
                                </label>
                                <select name="verification_status" class="kt-input" required>
                                    <option value="">Select status...</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Verification Notes
                                </label>
                                <textarea name="verification_notes" 
                                         class="kt-input" 
                                         rows="3"
                                         placeholder="Optional notes about the verification..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="kt-modal-footer py-3 px-5 flex items-center justify-end gap-2.5">
                        <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                            Cancel
                        </button>
                        <button type="submit" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-check"></i>
                            <span>Verify</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Assign Appointment Modal -->
        <div class="kt-modal" data-kt-modal="true" id="bulk-assign-modal">
            <div class="kt-modal-content max-w-[400px] top-[15%]">
                <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                            <i class="ki-filled ki-information text-primary text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Assign Appointment Numbers</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-4 px-5">
                    <p class="text-sm text-secondary-foreground mb-3">
                        Assign appointment numbers to <strong id="bulk-modal-count">0</strong> selected intake(s)?
                    </p>
                    <div class="kt-card bg-primary/10 border border-primary/20 p-3 mb-3">
                        <div class="flex items-start gap-2">
                            <i class="ki-filled ki-information text-primary text-sm mt-0.5"></i>
                            <div class="text-xs text-secondary-foreground">
                                <p class="font-medium text-primary mb-1">Auto Prefix Assignment</p>
                                <p>Prefixes (CDT or RCT) will be automatically determined based on rank and GL level:</p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    <li>ASC II GL 08+ → CDT</li>
                                    <li>IC GL 07- → RCT</li>
                                    <li>AIC → RCT</li>
                                    <li>DSC → CDT</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 mb-2">
                            <input type="checkbox" 
                                   id="auto-prefix-checkbox" 
                                   checked
                                   class="kt-checkbox"
                                   onchange="togglePrefixInput()">
                            <span class="text-sm font-medium text-foreground">Auto-determine prefix based on rank</span>
                        </label>
                        <div id="manual-prefix-container" class="hidden">
                            <label for="bulk-prefix-input" class="block text-sm font-medium text-foreground mb-2">
                                Manual Prefix (Optional)
                        </label>
                        <input type="text" 
                               id="bulk-prefix-input" 
                                   value="" 
                               maxlength="20"
                               class="kt-input w-full"
                                   placeholder="e.g., CDT, RCT">
                        <p class="text-xs text-secondary-foreground mt-1">
                                Leave empty to use auto prefix. Numbers will be generated sequentially per prefix.
                        </p>
                        </div>
                    </div>
                </div>
                <div class="kt-modal-footer py-3 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <button type="button" onclick="submitBulkAssign()" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        <span>Assign to Selected</span>
                    </button>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            // Tab management
            function showTab(tab) {
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[id^="tab-"]').forEach(el => {
                    el.classList.remove('border-primary', 'text-primary');
                    el.classList.add('border-transparent', 'text-secondary-foreground');
                });

                // Show selected tab
                document.getElementById(tab + '-tab').classList.remove('hidden');
                document.getElementById('tab-' + tab + '-btn').classList.remove('border-transparent', 'text-secondary-foreground');
                document.getElementById('tab-' + tab + '-btn').classList.add('border-primary', 'text-primary');
            }

            // Bulk entry management
            let bulkEntryCount = 0;
            const maxEntries = 10;
            const recruits = @json($recruits->items());

            function addBulkEntry() {
                if (bulkEntryCount >= maxEntries) {
                    alert('Maximum ' + maxEntries + ' entries allowed');
                    return;
                }

                bulkEntryCount++;
                const ranks = ['DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I', 'CA II', 'CA III'];
                const gradeLevels = ['GL 03', 'GL 04', 'GL 05', 'GL 06', 'GL 07', 'GL 08', 'GL 09', 'GL 10', 'GL 11', 'GL 12', 'GL 13', 'GL 14', 'GL 15', 'GL 16', 'GL 17', 'GL 18'];
                
                const commands = @json(\App\Models\Command::orderBy('name')->get());
                const commandsOptions = commands.map(cmd => `<option value="${cmd.id}">${cmd.name}</option>`).join('');
                
                const entryHtml = `
                    <div class="space-y-4 p-4 bg-muted/30 rounded border border-input" id="entry-${bulkEntryCount}">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="entries[${bulkEntryCount}][email]" 
                                       class="kt-input"
                                       placeholder="recruit@example.com"
                                       required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Initials <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="entries[${bulkEntryCount}][initials]" 
                                       class="kt-input"
                                       placeholder="J.D"
                                       maxlength="50"
                                       required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Surname <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="entries[${bulkEntryCount}][surname]" 
                                       class="kt-input"
                                       placeholder="Adeleke"
                                       maxlength="255"
                                       required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Rank <span class="text-danger">*</span>
                                </label>
                                <select name="entries[${bulkEntryCount}][substantive_rank]" 
                                       class="kt-input entry-rank-select"
                                       data-entry-index="${bulkEntryCount}"
                                       required>
                                    <option value="">Select...</option>
                                    ${ranks.map(rank => `<option value="${rank}">${rank}</option>`).join('')}
                                </select>
                            </div>
                            <div class="space-y-2 flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-foreground">
                                        GL <span class="text-danger">*</span>
                                    </label>
                                    <select name="entries[${bulkEntryCount}][salary_grade_level]" 
                                           class="kt-input entry-grade-level-select"
                                           data-entry-index="${bulkEntryCount}"
                                           required>
                                        <option value="">Select...</option>
                                        ${gradeLevels.map(gl => `<option value="${gl}">${gl}</option>`).join('')}
                                    </select>
                                </div>
                                <button type="button" 
                                        onclick="removeBulkEntry(${bulkEntryCount})" 
                                        class="kt-btn kt-btn-sm kt-btn-danger">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Date of First Appointment <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="entries[${bulkEntryCount}][date_of_first_appointment]" 
                                       class="kt-input"
                                       required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Date of Present Appointment <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="entries[${bulkEntryCount}][date_of_present_appointment]" 
                                       class="kt-input"
                                       required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Date Posted to Station <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="entries[${bulkEntryCount}][date_posted_to_station]" 
                                       class="kt-input"
                                       required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Command/Present Station <span class="text-danger">*</span>
                                </label>
                                <select name="entries[${bulkEntryCount}][command_id]" 
                                       class="kt-input"
                                       required>
                                    <option value="">Select...</option>
                                    ${commandsOptions}
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground">
                                    Unit
                                </label>
                                <select name="entries[${bulkEntryCount}][unit]" 
                                       class="kt-input">
                                    <option value="">Select...</option>
                                    <option value="General Duty (GD)">General Duty (GD)</option>
                                    <option value="Support Staff (SS)">Support Staff (SS)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('bulk-entries').insertAdjacentHTML('beforeend', entryHtml);
                
                // Setup event listener for the new rank select
                const newRankSelect = document.querySelector(`select[name="entries[${bulkEntryCount}][substantive_rank]"]`);
                const newGradeLevelSelect = document.querySelector(`select[name="entries[${bulkEntryCount}][salary_grade_level]"]`);
                
                if (newRankSelect && newGradeLevelSelect) {
                    newRankSelect.addEventListener('change', function() {
                        const selectedRank = this.value;
                        if (selectedRank && rankToGradeMap[selectedRank]) {
                            newGradeLevelSelect.value = rankToGradeMap[selectedRank];
                        } else {
                            newGradeLevelSelect.value = '';
                        }
                    });
                }
                
                updateBulkSubmitButton();
            }

            function removeBulkEntry(id) {
                document.getElementById('entry-' + id).remove();
                bulkEntryCount--;
                updateBulkSubmitButton();
            }

            function updateBulkSubmitButton() {
                const submitBtn = document.getElementById('bulk-submit-btn');
                const addBtn = document.getElementById('add-entry-btn');
                
                if (bulkEntryCount > 0) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
                
                if (bulkEntryCount >= maxEntries) {
                    addBtn.disabled = true;
                    addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    addBtn.disabled = false;
                    addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            // Verify modal
            function showVerifyModal(recruitId, recruitName) {
                document.getElementById('verify-recruit-name').textContent = recruitName;
                document.getElementById('verifyRecruitForm').action = '{{ route("establishment.onboarding.verify", ":id") }}'.replace(':id', recruitId);
                document.getElementById('view-recruit-details-link').href = '{{ route("establishment.new-recruits.view", ":id") }}'.replace(':id', recruitId);
                const modal = document.getElementById('verify-recruit-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            // Rank to Grade Level mapping
            const rankToGradeMap = {
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
                'CA III': 'GL 03'
            };

            // Auto-select grade level when rank is selected
            function setupRankGradeLevelMapping() {
                const rankSelect = document.getElementById('substantive_rank');
                const gradeLevelSelect = document.getElementById('salary_grade_level');

                if (rankSelect && gradeLevelSelect) {
                    rankSelect.addEventListener('change', function() {
                        const selectedRank = this.value;
                        if (selectedRank && rankToGradeMap[selectedRank]) {
                            gradeLevelSelect.value = rankToGradeMap[selectedRank];
                        } else {
                            gradeLevelSelect.value = '';
                        }
                    });
                }

                // Also setup for bulk entries
                document.addEventListener('change', function(e) {
                    if (e.target.name && e.target.name.includes('[substantive_rank]')) {
                        const entryMatch = e.target.name.match(/entries\[(\d+)\]/);
                        if (entryMatch) {
                            const entryIndex = entryMatch[1];
                            const gradeLevelSelect = document.querySelector(`select[name="entries[${entryIndex}][salary_grade_level]"]`);
                            if (gradeLevelSelect && rankToGradeMap[e.target.value]) {
                                gradeLevelSelect.value = rankToGradeMap[e.target.value];
                            }
                        }
                    }
                });
            }

            // Initialize tabs on page load
            document.addEventListener('DOMContentLoaded', function() {
                showTab('single');
                setupRankGradeLevelMapping();
            });

            // Single assign modal
            function showAssignAppointmentModal(officerId, recruitName) {
                document.getElementById('modal-officer-id').value = officerId;
                document.getElementById('modal-recruit-name').textContent = recruitName;
                const modal = document.getElementById('assign-appointment-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            // Bulk selection handling
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.recruit-checkbox');
                const selectAll = document.getElementById('selectAll');
                const bulkActionsBar = document.getElementById('bulkActionsBar');
                const selectedCount = document.getElementById('selectedCount');

                function updateBulkActions() {
                    const selected = document.querySelectorAll('.recruit-checkbox:checked').length;
                    
                    if (selected > 0) {
                        bulkActionsBar.style.display = 'block';
                        selectedCount.textContent = selected;
                    } else {
                        bulkActionsBar.style.display = 'none';
                    }

                    // Update select all checkbox
                    if (selectAll && checkboxes.length > 0) {
                        selectAll.checked = selected === checkboxes.length;
                        selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
                    }
                }

                // Handle individual checkbox selection
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateBulkActions);
                });

                // Handle select all - only select checkboxes that are visible/enabled
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(cb => {
                            if (!cb.disabled && cb.offsetParent !== null) {
                                cb.checked = this.checked;
                            }
                        });
                        updateBulkActions();
                    });
                }
            });

            function clearSelection() {
                document.querySelectorAll('.recruit-checkbox').forEach(cb => {
                    cb.checked = false;
                });
                if (document.getElementById('selectAll')) {
                    document.getElementById('selectAll').checked = false;
                    document.getElementById('selectAll').indeterminate = false;
                }
                document.getElementById('bulkActionsBar').style.display = 'none';
            }

            function showBulkAssignModal() {
                const selected = document.querySelectorAll('.recruit-checkbox:checked');
                if (selected.length === 0) {
                    alert('Please select at least one intake.');
                    return;
                }

                document.getElementById('bulk-modal-count').textContent = selected.length;
                const modal = document.getElementById('bulk-assign-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            function togglePrefixInput() {
                const autoCheckbox = document.getElementById('auto-prefix-checkbox');
                const manualContainer = document.getElementById('manual-prefix-container');
                const prefixInput = document.getElementById('bulk-prefix-input');
                
                if (autoCheckbox.checked) {
                    manualContainer.classList.add('hidden');
                    prefixInput.value = '';
                } else {
                    manualContainer.classList.remove('hidden');
                }
            }

            function submitBulkAssign() {
                const selected = document.querySelectorAll('.recruit-checkbox:checked');
                const officerIds = Array.from(selected).map(cb => cb.value);
                const autoPrefix = document.getElementById('auto-prefix-checkbox').checked;
                const manualPrefix = document.getElementById('bulk-prefix-input').value.trim();

                if (officerIds.length === 0) {
                    alert('Please select at least one intake.');
                    return;
                }

                // Clear existing hidden inputs
                const container = document.getElementById('bulkOfficerIdsContainer');
                container.innerHTML = '';

                // Add hidden inputs for each officer ID
                officerIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'officer_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });

                // Set auto prefix flag
                const autoInput = document.createElement('input');
                autoInput.type = 'hidden';
                autoInput.name = 'auto_prefix';
                autoInput.value = autoPrefix ? '1' : '0';
                container.appendChild(autoInput);

                // Set prefix (only if manual override provided)
                if (!autoPrefix && manualPrefix) {
                    document.getElementById('bulkPrefix').value = manualPrefix;
                } else {
                    document.getElementById('bulkPrefix').value = '';
                }

                // Submit form
                document.getElementById('bulkAssignForm').submit();
            }

            // Bulk delete functionality
            function showBulkDeleteModal() {
                const selected = document.querySelectorAll('.recruit-checkbox:checked');
                if (selected.length === 0) {
                    alert('Please select at least one intake to delete.');
                    return;
                }

                // Filter to only those that can be deleted (no service number)
                const deletable = Array.from(selected).filter(cb => cb.dataset.canDelete === '1');
                const excluded = selected.length - deletable.length;

                if (deletable.length === 0) {
                    alert('None of the selected intakes can be deleted. Only intakes without service numbers can be deleted.');
                    return;
                }

                document.getElementById('bulk-delete-count').textContent = deletable.length;
                
                // Show warning if some were excluded
                const warningDiv = document.getElementById('bulk-delete-warning');
                if (excluded > 0) {
                    document.getElementById('bulk-delete-excluded-count').textContent = excluded;
                    warningDiv.classList.remove('hidden');
                } else {
                    warningDiv.classList.add('hidden');
                }

                // Clear existing hidden inputs
                const container = document.getElementById('bulkDeleteIdsContainer');
                container.innerHTML = '';

                // Add hidden inputs for each deletable officer ID
                deletable.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'officer_ids[]';
                    input.value = cb.value;
                    container.appendChild(input);
                });

                const modal = document.getElementById('bulk-delete-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            // Delete recruit modal
            function showDeleteModal(recruitId, recruitName, recruitEmail) {
                document.getElementById('delete-recruit-name').textContent = recruitName;
                document.getElementById('delete-recruit-email').textContent = recruitEmail;
                document.getElementById('deleteRecruitForm').action = '{{ route("establishment.new-recruits.delete", ":id") }}'.replace(':id', recruitId);
                const modal = document.getElementById('delete-recruit-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            // Action menu toggle
            function toggleActionMenu(recruitId, event) {
                const menu = document.getElementById('action-menu-' + recruitId);
                const button = document.getElementById('action-btn-' + recruitId);
                const isHidden = menu.classList.contains('hidden');
                
                // Close all other menus
                document.querySelectorAll('[id^="action-menu-"]').forEach(m => {
                    if (m.id !== 'action-menu-' + recruitId) {
                        m.classList.add('hidden');
                    }
                });
                
                // Toggle current menu
                if (isHidden) {
                    // Get button position
                    const buttonRect = button.getBoundingClientRect();
                    const menuHeight = 120; // Approximate menu height
                    const menuWidth = 192; // w-48 = 192px
                    
                    // Calculate position - prefer below, but show above if near bottom
                    let top = buttonRect.bottom + 4; // 4px gap
                    let left = buttonRect.right - menuWidth; // Align to right edge of button
                    
                    // Check if menu would go below viewport
                    if (top + menuHeight > window.innerHeight) {
                        // Position above button instead
                        top = buttonRect.top - menuHeight - 4;
                    }
                    
                    // Ensure menu doesn't go off left edge
                    if (left < 8) {
                        left = 8;
                    }
                    
                    // Ensure menu doesn't go off right edge
                    if (left + menuWidth > window.innerWidth - 8) {
                        left = window.innerWidth - menuWidth - 8;
                    }
                    
                    // Set position
                    menu.style.top = top + 'px';
                    menu.style.left = left + 'px';
                    menu.classList.remove('hidden');
                } else {
                    menu.classList.add('hidden');
                }
                
                // Prevent event bubbling
                if (event) {
                    event.stopPropagation();
                }
            }

            function closeActionMenu(recruitId) {
                const menu = document.getElementById('action-menu-' + recruitId);
                if (menu) {
                    menu.classList.add('hidden');
                }
            }

            // Close menus when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('[id^="action-menu-"]') && !event.target.closest('[id^="action-btn-"]')) {
                    document.querySelectorAll('[id^="action-menu-"]').forEach(menu => {
                        menu.classList.add('hidden');
                    });
                }
            });
            
            // Close menus on scroll
            window.addEventListener('scroll', function() {
                document.querySelectorAll('[id^="action-menu-"]').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }, true);
        </script>
        @endpush

        @if($recruits->hasPages())
            <div class="mt-6 pt-4 border-t border-border px-4">
                {{ $recruits->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection