@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Reports</span>
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
    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card cursor-pointer hover:shadow-lg transition-shadow" onclick="generateReport('officers')">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Officers Report</span>
                        <span class="text-2xl font-semibold text-mono">All Officers</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-people text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card cursor-pointer hover:shadow-lg transition-shadow" onclick="generateReport('emoluments')">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Emoluments Report</span>
                        <span class="text-2xl font-semibold text-mono">All Emoluments</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-wallet text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card cursor-pointer hover:shadow-lg transition-shadow" onclick="generateReport('leave')">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Leave Report</span>
                        <span class="text-2xl font-semibold text-mono">Leave Statistics</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Options -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Generate Custom Report</h3>
        </div>
        <div class="kt-card-content">
            <form id="custom-report-form" action="{{ route('hrd.reports.generate') }}" method="POST" class="flex flex-col gap-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Report Type</label>
                        <div class="relative">
                            <input type="hidden" name="report_type" id="report_type" required>
                            <button type="button" 
                                    id="report_type_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="report_type_select_text">Select report type...</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="report_type_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="report_type_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search report types..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="report_type_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Format</label>
                        <div class="relative">
                            <input type="hidden" name="format" id="format" value="pdf" required>
                            <button type="button" 
                                    id="format_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="format_select_text">PDF</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="format_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="format_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search formats..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="format_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Start Date</label>
                        <input type="date" name="start_date" class="kt-input"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">End Date</label>
                        <input type="date" name="end_date" class="kt-input"/>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Data for searchable selects
    const reportTypeOptions = [
        {id: '', name: 'Select report type...'},
        {id: 'officers', name: 'Officers'},
        {id: 'emoluments', name: 'Emoluments'},
        {id: 'leave', name: 'Leave Applications'},
        {id: 'pass', name: 'Pass Applications'},
        {id: 'promotions', name: 'Promotions'},
        {id: 'retirements', name: 'Retirements'}
    ];

    const formatOptions = [
        {id: 'pdf', name: 'PDF'},
        {id: 'excel', name: 'Excel'},
        {id: 'csv', name: 'CSV'}
    ];

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

        let selectedOption = null;
        let filteredOptions = [...options];

        // Render options
        function renderOptions(opts) {
            if (opts.length === 0) {
                optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                return;
            }

            optionsContainer.innerHTML = opts.map(opt => {
                const display = displayFn ? displayFn(opt) : (opt.name || opt.id);
                const value = opt.id || opt.value || '';
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
                    selectedOption = options.find(o => (o.id || o.value || '') == id);
                    
                    if (selectedOption || id === '') {
                        hiddenInput.value = id;
                        displayText.textContent = name || placeholder;
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
                const display = displayFn ? displayFn(opt) : (opt.name || opt.id || '');
                return display.toLowerCase().includes(searchTerm);
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

    // Initialize searchable selects
    document.addEventListener('DOMContentLoaded', function() {
        // Report Type Select
        createSearchableSelect({
            triggerId: 'report_type_select_trigger',
            hiddenInputId: 'report_type',
            dropdownId: 'report_type_dropdown',
            searchInputId: 'report_type_search_input',
            optionsContainerId: 'report_type_options',
            displayTextId: 'report_type_select_text',
            options: reportTypeOptions,
            placeholder: 'Select report type...',
            searchPlaceholder: 'Search report types...'
        });

        // Format Select
        createSearchableSelect({
            triggerId: 'format_select_trigger',
            hiddenInputId: 'format',
            dropdownId: 'format_dropdown',
            searchInputId: 'format_search_input',
            optionsContainerId: 'format_options',
            displayTextId: 'format_select_text',
            options: formatOptions,
            placeholder: 'PDF',
            searchPlaceholder: 'Search formats...'
        });
    });

    function generateReport(type) {
        const token = window.API_CONFIG.token;
        const url = `/api/v1/reports/${type}?format=pdf`;
        
        // Open in new window to download
        window.open(url + '&token=' + token, '_blank');
    }

    // Form will submit normally to backend route
</script>
@endpush
@endsection


