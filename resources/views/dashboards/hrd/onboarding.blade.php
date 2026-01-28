@extends('layouts.app')

@section('title', 'Officer Onboarding')
@section('page-title', 'Officer Onboarding Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Officer Onboarding</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('bulk_results'))
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Bulk Upload Results</h3>
            </div>
            <div class="kt-card-content">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('bulk_results') as $result)
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-3 px-4 text-sm font-mono">{{ $result['service_number'] }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $result['email'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4">
                                        @if($result['status'] === 'success')
                                            <span class="kt-badge kt-badge-success">Success</span>
                                        @else
                                            <span class="kt-badge kt-badge-danger">Error</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $result['message'] }}</td>
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
                <form action="{{ route('hrd.onboarding.initiate') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label for="service_number" class="block text-sm font-medium text-foreground">
                                Service Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="service_number" 
                                   id="service_number"
                                   value="{{ old('service_number') }}"
                                   class="kt-input @error('service_number') kt-input-error @enderror"
                                   placeholder="e.g., 57616"
                                   required>
                            @error('service_number')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-foreground">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   value="{{ old('email') }}"
                                   class="kt-input @error('email') kt-input-error @enderror"
                                   placeholder="officer@example.com"
                                   required>
                            @error('email')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-foreground">
                                Name (Optional)
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name') }}"
                                   class="kt-input @error('name') kt-input-error @enderror"
                                   placeholder="Officer Name">
                            @error('name')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-send"></i>
                            Send Onboarding Link
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bulk Upload Form (Up to 10) -->
            <div id="bulk-tab" class="tab-content hidden">
                <form action="{{ route('hrd.onboarding.bulk-initiate') }}" method="POST" class="space-y-4" id="bulk-form">
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
                <form action="{{ route('hrd.onboarding.csv-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                            CSV file must have columns: <strong>service_number</strong>, <strong>email</strong>, and optionally <strong>name</strong>. Maximum 10 entries per upload.
                        </p>
                        <span class="text-xs" style="color: red; display: block; margin-top: 0.5rem;">
                            <strong>Document Type Allowed:</strong> CSV, TXT<br>
                            <strong>Document Size Allowed:</strong> Maximum 5MB
                        </span>
                        <div class="mt-2 p-3 bg-muted/50 rounded border border-input">
                            <p class="text-xs font-semibold mb-2">CSV Format Example:</p>
                            <pre class="text-xs font-mono">service_number,email,name
57616,officer1@example.com,John Doe
57617,officer2@example.com,Jane Smith</pre>
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

    <!-- Onboarding Management Table -->
    <div class="kt-card">
        <div class="kt-card-header flex-col sm:flex-row gap-4">
            <h3 class="kt-card-title">Onboarding Management</h3>
            <div class="relative w-full sm:w-72 sm:ml-auto">
                <input type="text" 
                       id="onboarding-search" 
                       class="kt-input pl-9 w-full"
                       placeholder="Search by name, service no, email...">
            </div>
        </div>
        <div class="kt-card-content">
            <!-- Loading Indicator -->
            <div id="search-loading" class="hidden py-8 text-center">
                <div class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-secondary-foreground">Searching...</span>
                </div>
            </div>

            <!-- Table Container (content loaded via AJAX when searching) -->
            <div id="onboarding-table-container">
                @include('dashboards.hrd.partials.onboarding-table')
            </div>
        </div>
    </div>
</div>

<!-- Edit Email Modal -->
<div id="edit-email-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeEditEmailModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-background rounded-lg shadow-xl w-full max-w-md relative">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Edit Email Address</h3>
                <button type="button" onclick="closeEditEmailModal()" class="text-secondary-foreground hover:text-foreground">
                    <i class="ki-filled ki-cross text-xl"></i>
                </button>
            </div>
            <form id="edit-email-form" method="POST" class="p-4 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-foreground">Officer</label>
                    <p id="edit-email-officer-name" class="text-sm text-secondary-foreground font-medium"></p>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-foreground">Current Email</label>
                    <p id="edit-email-current" class="text-sm text-secondary-foreground font-mono"></p>
                </div>
                
                <div class="space-y-2">
                    <label for="edit-email-new" class="block text-sm font-medium text-foreground">
                        New Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="edit-email-new"
                           class="kt-input w-full"
                           placeholder="newemail@example.com"
                           required>
                    <p class="text-xs text-secondary-foreground">
                        A new onboarding link will be sent to this email address.
                    </p>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <button type="button" onclick="closeEditEmailModal()" class="kt-btn kt-btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Update & Send Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure all asterisks in onboarding forms are red */
    label span.text-danger,
    label .text-danger,
    .block span.text-danger,
    .block .text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush

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
    document.getElementById(tab + '-tab').classList.remove('hidden');
    document.getElementById('tab-' + tab + '-btn').classList.remove('border-transparent', 'text-secondary-foreground');
    document.getElementById('tab-' + tab + '-btn').classList.add('border-primary', 'text-primary');
}

function addBulkEntry() {
    if (bulkEntryCount >= maxEntries) {
        alert('Maximum ' + maxEntries + ' entries allowed');
        return;
    }

    bulkEntryCount++;
    const entryHtml = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-muted/30 rounded border border-input" id="entry-${bulkEntryCount}">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-foreground">
                    Service Number <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="entries[${bulkEntryCount}][service_number]" 
                       class="kt-input"
                       placeholder="e.g., 57616"
                       required>
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-foreground">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email" 
                       name="entries[${bulkEntryCount}][email]" 
                       class="kt-input"
                       placeholder="officer@example.com"
                       required>
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-foreground">
                    Name (Optional)
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" 
                           name="entries[${bulkEntryCount}][name]" 
                           class="kt-input flex-1"
                           placeholder="Officer Name">
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
    initOnboardingSearch();
});

// Live Search for Onboarding Management (Server-side)
function initOnboardingSearch() {
    const searchInput = document.getElementById('onboarding-search');
    if (!searchInput) return;

    let searchTimeout = null;
    const tableContainer = document.getElementById('onboarding-table-container');
    const loadingIndicator = document.getElementById('search-loading');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Debounce the search (wait 300ms after user stops typing)
        searchTimeout = setTimeout(() => {
            performSearch(searchTerm);
        }, 300);
    });

    function performSearch(searchTerm) {
        // Show loading indicator
        loadingIndicator.classList.remove('hidden');
        tableContainer.classList.add('opacity-50');

        // Build the URL with search parameter
        const url = new URL('{{ route("hrd.onboarding") }}');
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        }

        // Perform AJAX request
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Update the table container with the new content
            tableContainer.innerHTML = html;
            
            // Update URL without page reload (for bookmarking/sharing)
            const newUrl = new URL(window.location.href);
            if (searchTerm) {
                newUrl.searchParams.set('search', searchTerm);
            } else {
                newUrl.searchParams.delete('search');
            }
            window.history.replaceState({}, '', newUrl);
        })
        .catch(error => {
            console.error('Search error:', error);
            tableContainer.innerHTML = `
                <div class="py-12 text-center">
                    <i class="ki-filled ki-information text-4xl text-danger mb-4"></i>
                    <p class="text-danger">Error performing search. Please try again.</p>
                </div>
            `;
        })
        .finally(() => {
            // Hide loading indicator
            loadingIndicator.classList.add('hidden');
            tableContainer.classList.remove('opacity-50');
        });
    }

    // Handle pagination clicks within search results
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('#onboarding-table-container a[href*="page="]');
        if (paginationLink) {
            e.preventDefault();
            
            // Show loading
            loadingIndicator.classList.remove('hidden');
            tableContainer.classList.add('opacity-50');

            fetch(paginationLink.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                window.history.replaceState({}, '', paginationLink.href);
            })
            .catch(error => {
                console.error('Pagination error:', error);
            })
            .finally(() => {
                loadingIndicator.classList.add('hidden');
                tableContainer.classList.remove('opacity-50');
            });
        }
    });

    // Initialize search input from URL parameter (if any)
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('search');
    if (initialSearch) {
        searchInput.value = initialSearch;
    }
}

// Edit Email Modal Functions
function openEditEmailModal(officerId, currentEmail, officerName) {
    const modal = document.getElementById('edit-email-modal');
    const form = document.getElementById('edit-email-form');
    const officerNameEl = document.getElementById('edit-email-officer-name');
    const currentEmailEl = document.getElementById('edit-email-current');
    const newEmailInput = document.getElementById('edit-email-new');
    
    // Set form action URL
    form.action = '{{ route("hrd.onboarding.update-email", ":id") }}'.replace(':id', officerId);
    
    // Populate modal fields
    officerNameEl.textContent = officerName || 'N/A';
    currentEmailEl.textContent = currentEmail || 'N/A';
    newEmailInput.value = '';
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Focus on new email input
    setTimeout(() => newEmailInput.focus(), 100);
}

function closeEditEmailModal() {
    const modal = document.getElementById('edit-email-modal');
    modal.classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditEmailModal();
    }
});
</script>
@endsection
