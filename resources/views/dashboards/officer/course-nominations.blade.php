@extends('layouts.app')

@section('title', 'Course Nominations')
@section('page-title', 'Course Nominations')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Home</a>
    <span>/</span>
    <span class="text-primary">Course Nominations</span>
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
                <h3 class="kt-card-title">Filter Course Nominations</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('officer.course-nominations') }}" class="flex flex-col gap-4">
                    <!-- Filter Controls -->
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <div class="relative">
                                <input type="hidden" name="status" id="filter_status_id" value="{{ request('status') ?? '' }}">
                                <button type="button" 
                                        id="filter_status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_status_select_text">{{ request('status') ? ucfirst(request('status')) : 'All Statuses' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_status_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_status_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search status..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_status_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Year Select -->
                        <div class="w-full md:w-36">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <div class="relative">
                                <input type="hidden" name="year" id="filter_year_id" value="{{ request('year') ?? '' }}">
                                <button type="button" 
                                        id="filter_year_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_year_select_text">{{ request('year') ? request('year') : 'All Years' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_year_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_year_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search years..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_year_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sort By -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Sort By</label>
                            <div class="relative">
                                <input type="hidden" name="sort_by" id="filter_sort_by_id" value="{{ request('sort_by') ?? 'start_date' }}">
                                <button type="button" 
                                        id="filter_sort_by_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_sort_by_select_text">{{ request('sort_by') ? (request('sort_by') === 'start_date' ? 'Start Date' : (request('sort_by') === 'course_name' ? 'Course Name' : (request('sort_by') === 'completion_date' ? 'Completion Date' : 'Nominated Date'))) : 'Start Date' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_sort_by_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_sort_by_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search sort options..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_sort_by_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="w-full md:w-36">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                            <div class="relative">
                                <input type="hidden" name="sort_order" id="filter_sort_order_id" value="{{ request('sort_order') ?? 'desc' }}">
                                <button type="button" 
                                        id="filter_sort_order_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_sort_order_select_text">{{ request('sort_order') ? (request('sort_order') === 'desc' ? 'Descending' : 'Ascending') : 'Descending' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_sort_order_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_sort_order_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search order..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_sort_order_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['status', 'year', 'sort_by', 'sort_order']))
                                <a href="{{ route('officer.course-nominations') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Course Nominations List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Course Nominations</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $courses->total() }} records
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 1000px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Course Name
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Type
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Start Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        End Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Completion Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Status
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Nominated By
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $course->course_name }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $course->course_type ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $course->start_date->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $course->end_date ? $course->end_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $course->completion_date ? $course->completion_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            @if($course->is_completed)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">Completed</span>
                                            @elseif($course->completion_submitted_at)
                                                <span class="kt-badge kt-badge-info kt-badge-sm">Pending review</span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                            @endif
                                            @if($course->completionDocuments->isNotEmpty())
                                                <div class="text-xs text-secondary-foreground mt-1">
                                                    {{ $course->completionDocuments->count() }} file(s) uploaded
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            @if($course->nominatedBy)
                                                @if($course->nominatedBy->officer)
                                                    {{ $course->nominatedBy->officer->initials }} {{ $course->nominatedBy->officer->surname }}
                                                @else
                                                    {{ $course->nominatedBy->email }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            @if(!$course->is_completed)
                                                <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost" data-kt-modal-toggle="#upload-modal-{{ $course->id }}">
                                                    <i class="ki-filled ki-file-up"></i> Upload certificate
                                                </button>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No course nominations found</p>
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
                        @forelse($courses as $course)
                            <div class="flex flex-col gap-3 p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex items-center justify-center size-12 rounded-full {{ $course->is_completed ? 'bg-success/10' : 'bg-warning/10' }}">
                                            <i class="ki-filled ki-book {{ $course->is_completed ? 'text-success' : 'text-warning' }} text-xl"></i>
                                        </div>
                                        <div class="flex flex-col gap-1 flex-1">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ $course->course_name }}
                                            </span>
                                            @if($course->course_type)
                                            <span class="text-xs text-secondary-foreground">
                                                Type: {{ $course->course_type }}
                                            </span>
                                            @endif
                                            <span class="text-xs text-secondary-foreground">
                                                Start: {{ $course->start_date->format('d/m/Y') }}
                                            </span>
                                            @if($course->end_date)
                                            <span class="text-xs text-secondary-foreground">
                                                End: {{ $course->end_date->format('d/m/Y') }}
                                            </span>
                                            @endif
                                            @if($course->is_completed && $course->completion_date)
                                            <span class="text-xs text-secondary-foreground">
                                                Completed: {{ $course->completion_date->format('d/m/Y') }}
                                            </span>
                                            @endif
                                            @if($course->nominatedBy)
                                            <span class="text-xs text-secondary-foreground">
                                                Nominated by: 
                                                @if($course->nominatedBy->officer)
                                                    {{ $course->nominatedBy->officer->initials }} {{ $course->nominatedBy->officer->surname }}
                                                @else
                                                    {{ $course->nominatedBy->email }}
                                                @endif
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($course->is_completed)
                                        <span class="kt-badge kt-badge-success kt-badge-sm">Completed</span>
                                    @elseif($course->completion_submitted_at)
                                        <span class="kt-badge kt-badge-info kt-badge-sm">Pending review</span>
                                    @else
                                        <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                    @endif
                                </div>
                                @if(!$course->is_completed)
                                    <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" data-kt-modal-toggle="#upload-modal-{{ $course->id }}">
                                        <i class="ki-filled ki-file-up"></i> Upload certificate
                                    </button>
                                @endif
                                @if($course->completionDocuments->isNotEmpty())
                                    <p class="text-xs text-secondary-foreground">{{ $course->completionDocuments->count() }} file(s) uploaded for review</p>
                                @endif
                                @if($course->notes)
                                <div class="pt-2 border-t border-border">
                                    <p class="text-xs text-secondary-foreground">
                                        <span class="font-medium">Notes:</span> {{ $course->notes }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No course nominations found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($courses->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $courses->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Upload certificate modals (one per in-progress course on this page) -->
        @foreach($courses as $course)
            @if(!$course->is_completed)
                <div class="kt-modal" data-kt-modal="true" id="upload-modal-{{ $course->id }}">
                    <div class="kt-modal-content max-w-md">
                        <div class="kt-modal-header py-4 px-5">
                            <h3 class="kt-modal-title">Upload certificate of completion</h3>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                                <i class="ki-filled ki-cross"></i>
                            </button>
                        </div>
                        <form action="{{ route('officer.course-nominations.upload-completion', $course->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="kt-modal-body py-5 px-5 space-y-4">
                                <p class="text-sm text-secondary-foreground">
                                    Upload your certificate or proof of completion for <strong>{{ $course->course_name }}</strong>. Once submitted, your nomination will go to HRD/Staff Officer for review and formal completion.
                                </p>
                                <div>
                                    <label for="document-{{ $course->id }}" class="block text-sm font-medium text-foreground mb-1">Certificate / document <span class="text-danger">*</span></label>
                                    <input type="file" name="document" id="document-{{ $course->id }}" accept=".pdf,.jpg,.jpeg,.png" class="kt-input" required>
                                    <p class="text-xs text-secondary-foreground mt-1">PDF or image (JPG, PNG). Max 10 MB.</p>
                                    @error('document')
                                        <p class="text-sm text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="kt-modal-footer py-4 px-5 flex justify-end gap-2">
                                <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-file-up"></i> Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
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

<script>
    // Filter options data
    @php
        $statusOptions = [
            ['id' => '', 'name' => 'All Statuses'],
            ['id' => 'pending', 'name' => 'Pending'],
            ['id' => 'completed', 'name' => 'Completed']
        ];
        $yearOptions = collect($years)->map(function($year) {
            return ['id' => $year, 'name' => $year];
        })->values();
        $yearOptions->prepend(['id' => '', 'name' => 'All Years']);
        $sortByOptions = [
            ['id' => 'start_date', 'name' => 'Start Date'],
            ['id' => 'course_name', 'name' => 'Course Name'],
            ['id' => 'completion_date', 'name' => 'Completion Date'],
            ['id' => 'created_at', 'name' => 'Nominated Date']
        ];
        $sortOrderOptions = [
            ['id' => 'desc', 'name' => 'Descending'],
            ['id' => 'asc', 'name' => 'Ascending']
        ];
    @endphp
    const statusOptions = @json($statusOptions);
    const yearOptions = @json($yearOptions);
    const sortByOptions = @json($sortByOptions);
    const sortOrderOptions = @json($sortOrderOptions);

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

    // Initialize filter selects
    document.addEventListener('DOMContentLoaded', function() {
        createSearchableSelect({
            triggerId: 'filter_status_select_trigger',
            hiddenInputId: 'filter_status_id',
            dropdownId: 'filter_status_dropdown',
            searchInputId: 'filter_status_search_input',
            optionsContainerId: 'filter_status_options',
            displayTextId: 'filter_status_select_text',
            options: statusOptions,
            placeholder: 'All Statuses',
            searchPlaceholder: 'Search status...'
        });

        createSearchableSelect({
            triggerId: 'filter_year_select_trigger',
            hiddenInputId: 'filter_year_id',
            dropdownId: 'filter_year_dropdown',
            searchInputId: 'filter_year_search_input',
            optionsContainerId: 'filter_year_options',
            displayTextId: 'filter_year_select_text',
            options: yearOptions,
            placeholder: 'All Years',
            searchPlaceholder: 'Search years...'
        });

        createSearchableSelect({
            triggerId: 'filter_sort_by_select_trigger',
            hiddenInputId: 'filter_sort_by_id',
            dropdownId: 'filter_sort_by_dropdown',
            searchInputId: 'filter_sort_by_search_input',
            optionsContainerId: 'filter_sort_by_options',
            displayTextId: 'filter_sort_by_select_text',
            options: sortByOptions,
            placeholder: 'Start Date',
            searchPlaceholder: 'Search sort options...'
        });

        createSearchableSelect({
            triggerId: 'filter_sort_order_select_trigger',
            hiddenInputId: 'filter_sort_order_id',
            dropdownId: 'filter_sort_order_dropdown',
            searchInputId: 'filter_sort_order_search_input',
            optionsContainerId: 'filter_sort_order_options',
            displayTextId: 'filter_sort_order_select_text',
            options: sortOrderOptions,
            placeholder: 'Descending',
            searchPlaceholder: 'Search order...'
        });
    });
</script>
@endsection

