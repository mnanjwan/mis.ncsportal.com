@extends('layouts.app')

@section('title', 'Validated Emoluments for Processing')
@section('page-title', 'Validated Emoluments for Processing')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <span class="text-primary">Validated Emoluments</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Emoluments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('accounts.validated-officers') }}" class="flex flex-col gap-4">
                    <!-- Filter Controls -->
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Command Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <select name="command_id" class="kt-input w-full">
                                <option value="">All Commands</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ request('command_id') == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Select -->
                        <div class="w-full md:w-36">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <select name="year" class="kt-input w-full">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['command_id', 'year']))
                                <a href="{{ route('accounts.validated-officers') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Emoluments List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Validated Emoluments for Processing</h3>
                <div class="kt-card-toolbar flex items-center gap-3">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $emoluments->count() }} records
                    </span>
                    <div id="bulk-actions" class="hidden flex items-center gap-2">
                        <span class="text-sm text-secondary-foreground" id="selected-count">0 selected</span>
                        <button type="button" 
                                id="bulk-process-btn"
                                class="kt-btn kt-btn-sm kt-btn-primary"
                                data-kt-modal-toggle="#bulk-process-modal">
                            <i class="ki-filled ki-check"></i> Process Selected
                        </button>
                    </div>
                </div>
            </div>
            <div class="kt-card-content">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground w-12">
                                        <input type="checkbox" 
                                               id="select-all" 
                                               class="rounded border-input text-primary focus:ring-primary">
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Officer
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Service No
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Year
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Command
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Status
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Validated
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($emoluments as $emolument)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <input type="checkbox" 
                                                   name="emolument_ids[]" 
                                                   value="{{ $emolument->id }}"
                                                   class="emolument-checkbox rounded border-input text-primary focus:ring-primary">
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $emolument->officer->initials ?? '' }}
                                                {{ $emolument->officer->surname ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-mono">
                                                {{ $emolument->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            {{ $emolument->year }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-success kt-badge-sm">
                                                {{ $emolument->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('accounts.emoluments.show', $emolument->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                                <button type="button" 
                                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                                        data-kt-modal-toggle="#process-emolument-modal-{{ $emolument->id }}">
                                                    Process
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No validated emoluments found</p>
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
                        @forelse($emoluments as $emolument)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                        <i class="ki-filled ki-wallet text-success text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="kt-badge kt-badge-success kt-badge-sm">
                                                {{ $emolument->status }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $emolument->year }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-secondary-foreground">
                                            Command: {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Validated: {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a href="{{ route('accounts.emoluments.show', $emolument->id) }}"
                                       class="kt-btn kt-btn-ghost kt-btn-sm">
                                        View
                                    </a>
                                    <button type="button" 
                                            class="kt-btn kt-btn-primary kt-btn-sm"
                                            data-kt-modal-toggle="#process-emolument-modal-{{ $emolument->id }}">
                                        Process
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No validated emoluments found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Confirmation Modals -->
    @foreach($emoluments as $emolument)
    <div class="kt-modal" data-kt-modal="true" id="process-emolument-modal-{{ $emolument->id }}">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                        <i class="ki-filled ki-information text-warning text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Processing</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to process this emolument? This action will mark the emolument as PROCESSED and cannot be undone.
                </p>
                <div class="mt-4 p-3 bg-muted rounded-lg">
                    <p class="text-xs text-secondary-foreground mb-1">Officer:</p>
                    <p class="text-sm font-medium text-foreground">
                        {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                    </p>
                    <p class="text-xs text-secondary-foreground mt-2 mb-1">Service No:</p>
                    <p class="text-sm font-medium text-foreground">{{ $emolument->officer->service_number ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form method="POST" action="{{ route('accounts.emoluments.process', $emolument->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <span class="kt-menu-icon"><i class="ki-filled ki-check"></i></span>
                        <span>Process</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Bulk Process Modal -->
    <div class="kt-modal" data-kt-modal="true" id="bulk-process-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                        <i class="ki-filled ki-information text-warning text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Bulk Processing</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to process <span id="bulk-count-text" class="font-semibold">0</span> selected emolument(s)? This action will mark them as PROCESSED and cannot be undone.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form method="POST" action="{{ route('accounts.emoluments.bulk-process') }}" id="bulk-process-form">
                    @csrf
                    <div id="bulk-process-inputs"></div>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <span class="kt-menu-icon"><i class="ki-filled ki-check"></i></span>
                        <span>Process Selected</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.emolument-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');
            const bulkCountText = document.getElementById('bulk-count-text');
            const bulkProcessForm = document.getElementById('bulk-process-form');
            const bulkProcessInputs = document.getElementById('bulk-process-inputs');

            function updateBulkActions() {
                const checked = document.querySelectorAll('.emolument-checkbox:checked');
                const count = checked.length;
                
                if (count > 0) {
                    bulkActions.classList.remove('hidden');
                    selectedCount.textContent = count + ' selected';
                    bulkCountText.textContent = count;
                } else {
                    bulkActions.classList.add('hidden');
                }
            }

            // Select all functionality
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkActions();
                });
            }

            // Individual checkbox functionality
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActions();
                    // Update select all state
                    if (selectAll) {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                        selectAll.checked = allChecked;
                        selectAll.indeterminate = someChecked && !allChecked;
                    }
                });
            });

            // Bulk process form submission
            if (bulkProcessForm) {
                bulkProcessForm.addEventListener('submit', function(e) {
                    const checked = document.querySelectorAll('.emolument-checkbox:checked');
                    if (checked.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one emolument to process');
                        return false;
                    }

                    // Add hidden inputs for selected IDs
                    bulkProcessInputs.innerHTML = '';
                    checked.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'emolument_ids[]';
                        input.value = checkbox.value;
                        bulkProcessInputs.appendChild(input);
                    });
                });
            }
        });
    </script>
    @endpush
@endsection

