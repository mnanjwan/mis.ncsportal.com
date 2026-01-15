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
        <div class="kt-card-header">
            <h3 class="kt-card-title">Onboarding Management</h3>
        </div>
        <div class="kt-card-content">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service No</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Email Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Onboarding Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Initiated</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($onboardingOfficers as $officer)
                                @php
                                    $emailDelivered = $officer->user->email_verified_at !== null;
                                    $onboardingCompleted = $officer->user && 
                                        $officer->date_of_birth && 
                                        $officer->date_of_first_appointment && 
                                        $officer->bank_name && 
                                        $officer->pfa_name;
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-mono text-foreground">
                                        {{ $officer->service_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ ($officer->initials ?? '') . ' ' . ($officer->surname ?? '') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->user->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($emailDelivered)
                                            <span class="kt-badge kt-badge-success">
                                                <i class="ki-filled ki-check-circle"></i> Delivered
                                            </span>
                                        @else
                                            <span class="kt-badge kt-badge-warning">
                                                <i class="ki-filled ki-information"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($onboardingCompleted)
                                            <span class="kt-badge kt-badge-success">
                                                <i class="ki-filled ki-check-circle"></i> Completed
                                            </span>
                                        @else
                                            <span class="kt-badge kt-badge-warning">
                                                <i class="ki-filled ki-clock"></i> In Progress
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->user->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('hrd.onboarding.resend-link', $officer->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost" title="Resend Email">
                                                <i class="ki-filled ki-arrows-circle"></i> Resend
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground">No onboarding initiated yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden">
                <div class="flex flex-col gap-4">
                    @forelse($onboardingOfficers as $officer)
                        @php
                            $emailDelivered = $officer->user->email_verified_at !== null;
                            $onboardingCompleted = $officer->user && 
                                $officer->date_of_birth && 
                                $officer->date_of_first_appointment && 
                                $officer->bank_name && 
                                $officer->pfa_name;
                        @endphp
                        <div class="flex flex-col gap-3 p-4 rounded-lg bg-muted/50 border border-input">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ ($officer->initials ?? '') . ' ' . ($officer->surname ?? '') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground font-mono">
                                        {{ $officer->service_number ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $officer->user->email ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($emailDelivered)
                                    <span class="kt-badge kt-badge-success text-xs">Email Delivered</span>
                                @else
                                    <span class="kt-badge kt-badge-warning text-xs">Email Pending</span>
                                @endif
                                @if($onboardingCompleted)
                                    <span class="kt-badge kt-badge-success text-xs">Completed</span>
                                @else
                                    <span class="kt-badge kt-badge-warning text-xs">In Progress</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-end pt-2 border-t border-input">
                                <form action="{{ route('hrd.onboarding.resend-link', $officer->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost">
                                        <i class="ki-filled ki-arrows-circle"></i> Resend Email
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">No onboarding initiated yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($onboardingOfficers->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $onboardingOfficers->links() }}
                </div>
            @endif
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
});
</script>
@endsection
