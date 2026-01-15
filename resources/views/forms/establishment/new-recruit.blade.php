@extends('layouts.app')

@section('title', 'Add New Recruit')
@section('page-title', 'Add New Recruit')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.new-recruits') }}">New Recruits</a>
    <span>/</span>
    <span class="text-primary">Add New Recruit</span>
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

    @if(session('csv_errors') && count(session('csv_errors')) > 0)
        <div class="kt-card bg-warning/10 border border-warning/20 mb-5">
            <div class="kt-card-header">
                <h3 class="kt-card-title">CSV Upload Warnings</h3>
            </div>
            <div class="kt-card-content">
                <ul class="list-disc list-inside space-y-1 text-sm text-secondary-foreground">
                    @foreach(session('csv_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Add New Recruit(s)</h3>
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
                        Bulk Create (Up to 10)
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
                    <form action="{{ route('establishment.new-recruits.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="entry_type" value="single">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="initials" class="block text-sm font-medium text-foreground mb-2">
                                    Initials <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="initials" 
                                       name="initials" 
                                       value="{{ old('initials') }}"
                                       class="kt-input w-full" 
                                       required>
                            </div>

                            <div>
                                <label for="surname" class="block text-sm font-medium text-foreground mb-2">
                                    Surname <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="surname" 
                                       name="surname" 
                                       value="{{ old('surname') }}"
                                       class="kt-input w-full" 
                                       required>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-foreground mb-2">
                                    Email (Personal) <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       class="kt-input w-full" 
                                       required>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    Personal email for onboarding. ICT will create customs.gov.ng email later.
                                </p>
                            </div>

                            <div>
                                <label for="substantive_rank" class="block text-sm font-medium text-foreground mb-2">
                                    Entry Rank <span class="text-danger">*</span>
                                </label>
                                <select id="substantive_rank" name="substantive_rank" class="kt-input w-full" required>
                                    <option value="">Select Entry Rank</option>
                                    <option value="ASC II" {{ old('substantive_rank') == 'ASC II' ? 'selected' : '' }}>ASC II</option>
                                    <option value="ASC I" {{ old('substantive_rank') == 'ASC I' ? 'selected' : '' }}>ASC I</option>
                                    <option value="DSC" {{ old('substantive_rank') == 'DSC' ? 'selected' : '' }}>DSC</option>
                                    <option value="SC" {{ old('substantive_rank') == 'SC' ? 'selected' : '' }}>SC</option>
                                    <option value="CSC" {{ old('substantive_rank') == 'CSC' ? 'selected' : '' }}>CSC</option>
                                    <option value="AC" {{ old('substantive_rank') == 'AC' ? 'selected' : '' }}>AC</option>
                                    <option value="DC" {{ old('substantive_rank') == 'DC' ? 'selected' : '' }}>DC</option>
                                    <option value="CC" {{ old('substantive_rank') == 'CC' ? 'selected' : '' }}>CC</option>
                                    <option value="ACG" {{ old('substantive_rank') == 'ACG' ? 'selected' : '' }}>ACG</option>
                                    <option value="DCG" {{ old('substantive_rank') == 'DCG' ? 'selected' : '' }}>DCG</option>
                                </select>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20 mt-5">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>Appointment number will be assigned after recruit is added</li>
                                            <li>Service number will be assigned after training results are uploaded by TRADOC</li>
                                            <li>ICT will create customs.gov.ng email after service number assignment</li>
                                            <li>Officer will complete remaining information during onboarding</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border mt-5">
                            <a href="{{ route('establishment.new-recruits') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Add Recruit
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bulk Create Form -->
                <div id="bulk-tab" class="tab-content hidden">
                    <form action="{{ route('establishment.new-recruits.store') }}" method="POST" id="bulk-form">
                        @csrf
                        <input type="hidden" name="entry_type" value="bulk">
                        
                        <div id="bulk-entries" class="space-y-4">
                            <!-- Entries will be added here -->
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-border mt-5">
                            <button type="button" onclick="addBulkEntry()" class="kt-btn kt-btn-secondary" id="add-entry-btn">
                                <i class="ki-filled ki-plus"></i> Add Entry
                            </button>
                            <button type="submit" class="kt-btn kt-btn-primary" id="bulk-submit-btn" disabled>
                                <i class="ki-filled ki-check"></i>
                                Create All Recruits
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CSV Upload Form -->
                <div id="csv-tab" class="tab-content hidden">
                    <form action="{{ route('establishment.new-recruits.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="csv">
                        
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
                                CSV file must have columns: <strong>initials</strong>, <strong>surname</strong>, <strong>email</strong>, and <strong>substantive_rank</strong>. Maximum 50 entries per upload.
                            </p>
                            <span class="text-xs" style="color: red; display: block; margin-top: 0.5rem;">
                                <strong>Document Type Allowed:</strong> CSV, TXT<br>
                                <strong>Document Size Allowed:</strong> Maximum 5MB
                            </span>
                            <div class="mt-2 p-3 bg-muted/50 rounded border border-input">
                                <p class="text-xs font-semibold mb-2">CSV Format Example:</p>
                                <pre class="text-xs font-mono">initials,surname,email,substantive_rank
J.D,Smith,john.smith@example.com,ASC I
M.A,Johnson,mary.johnson@example.com,DSC</pre>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20 mt-5">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>Appointment number will be assigned after recruit is added</li>
                                            <li>Service number will be assigned after training results are uploaded by TRADOC</li>
                                            <li>ICT will create customs.gov.ng email after service number assignment</li>
                                            <li>Officer will complete remaining information during onboarding</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border mt-5">
                            <a href="{{ route('establishment.new-recruits') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-file-up"></i>
                                Upload CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let bulkEntryCount = 0;
        const maxEntries = 10;

        function showTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('border-primary', 'text-primary');
                el.classList.add('border-transparent', 'text-secondary-foreground');
            });

            // Show selected tab
            const tabContent = document.getElementById(tab + '-tab');
            const tabButton = document.getElementById('tab-' + tab + '-btn');
            
            if (tabContent) {
                tabContent.classList.remove('hidden');
            }
            if (tabButton) {
                tabButton.classList.remove('border-transparent', 'text-secondary-foreground');
                tabButton.classList.add('border-primary', 'text-primary');
            }
        }

        function addBulkEntry() {
            if (bulkEntryCount >= maxEntries) {
                alert('Maximum ' + maxEntries + ' entries allowed');
                return;
            }

            bulkEntryCount++;
            const entryHtml = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-muted/30 rounded border border-input" id="entry-${bulkEntryCount}">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Initials <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="entries[${bulkEntryCount}][initials]" 
                               class="kt-input w-full"
                               placeholder="e.g., J.D"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Surname <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="entries[${bulkEntryCount}][surname]" 
                               class="kt-input w-full"
                               placeholder="e.g., Smith"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               name="entries[${bulkEntryCount}][email]" 
                               class="kt-input w-full"
                               placeholder="officer@example.com"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Entry Rank <span class="text-danger">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <select name="entries[${bulkEntryCount}][substantive_rank]" class="kt-input flex-1" required>
                                <option value="">Select Rank</option>
                                <option value="ASC II">ASC II</option>
                                <option value="ASC I">ASC I</option>
                                <option value="DSC">DSC</option>
                                <option value="SC">SC</option>
                                <option value="CSC">CSC</option>
                                <option value="AC">AC</option>
                                <option value="DC">DC</option>
                                <option value="CC">CC</option>
                                <option value="ACG">ACG</option>
                                <option value="DCG">DCG</option>
                            </select>
                            <button type="button" 
                                    onclick="removeBulkEntry(${bulkEntryCount})" 
                                    class="kt-btn kt-btn-sm kt-btn-danger">
                                <i class="ki-filled ki-cross"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('bulk-entries').insertAdjacentHTML('beforeend', entryHtml);
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            showTab('single');
        });
    </script>
    @endpush
@endsection
