@extends('layouts.app')

@section('title', 'TRADOC Dashboard')
@section('page-title', 'TRADOC Dashboard')

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
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Results</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-file text-2xl text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Service Number</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['pending_service_number'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-time text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tradoc.download-template') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-file-down"></i> Download Template
                    </a>
                    <a href="{{ route('tradoc.upload') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-file-up"></i> Upload Results
                    </a>
                    <button type="button" onclick="showDownloadModal()" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-file-down"></i> Download CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Training Results Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Training Results</h3>
                <div class="kt-card-toolbar flex items-center gap-3">
                    <form method="GET" action="{{ route('tradoc.dashboard') }}" id="rank-filter-form" class="flex items-center gap-2">
                        <div class="relative">
                            <input type="hidden" name="rank" id="rank-filter" value="{{ $selectedRank ?? '' }}">
                            <button type="button" 
                                    id="rank_filter_select_trigger" 
                                    class="kt-input kt-input-sm text-left flex items-center justify-between cursor-pointer" style="min-width: 150px;">
                                <span id="rank_filter_select_text">{{ $selectedRank ? $selectedRank : 'All Ranks' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="rank_filter_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="rank_filter_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search rank..."
                                           autocomplete="off">
                                </div>
                                <div id="rank_filter_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </form>
                    <button type="button" onclick="showDownloadModal()" class="kt-btn kt-btn-sm kt-btn-success">
                        <i class="ki-filled ki-file-down"></i> Download CSV
                    </button>
                    <span class="text-sm text-secondary-foreground">
                        Sorted by rank
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($results->count() > 0)
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        #
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Appointment Number
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Officer Name
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Rank
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Score
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Service Number
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $index => $result)
                                    @php
                                        $avatarInitials = strtoupper(substr($result->officer_name, 0, 2));
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm text-secondary-foreground">{{ ($results->currentPage() - 1) * $results->perPage() + $index + 1 }}</span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-mono text-foreground">{{ $result->appointment_number }}</span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm" style="flex-shrink: 0;">
                                                    {{ $avatarInitials }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-foreground">{{ $result->officer_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $result->officer->substantive_rank ?? $result->rank ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-semibold text-foreground">{{ number_format($result->training_score, 2) }}%</span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-mono text-foreground">{{ $result->service_number ?? 'Pending' }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" 
                                                        onclick="showResultModal({{ $result->id }})"
                                                        class="kt-btn kt-btn-sm kt-btn-ghost"
                                                        title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </button>
                                                @if(!$result->service_number)
                                                    <button type="button" 
                                                            onclick="showDeleteModal({{ $result->id }}, '{{ $result->appointment_number }}', '{{ $result->officer_name }}')"
                                                            class="kt-btn kt-btn-sm kt-btn-danger"
                                                            title="Delete">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($results->hasPages())
                        <div class="mt-6 pt-4 border-t border-border px-5">
                            {{ $results->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12 px-5">
                        <i class="ki-filled ki-file text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No training results found</p>
                        @if($selectedRank)
                            <p class="text-sm text-secondary-foreground mt-1">Try selecting a different rank or clear the filter</p>
                        @else
                        <a href="{{ route('tradoc.upload') }}" class="kt-btn kt-btn-primary mt-4">
                            <i class="ki-filled ki-file-up"></i> Upload Training Results
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
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

    <!-- View Result Modal -->
    <div class="kt-modal" data-kt-modal="true" id="view-result-modal">
        <div class="kt-modal-content max-w-[600px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Training Result Details</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5" id="result-modal-content">
                <!-- Content will be loaded here -->
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Download CSV Modal -->
    <div class="kt-modal" data-kt-modal="true" id="download-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Download Training Results</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form action="{{ route('tradoc.export-sorted') }}" method="GET" id="downloadForm">
                <div class="kt-modal-body py-5 px-5">
                    <div class="mb-4">
                        <label for="download-rank" class="block text-sm font-medium text-foreground mb-2">
                            Select Rank to Download <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="rank" id="download-rank" value="{{ $selectedRank ?? '' }}" required>
                            <button type="button" 
                                    id="download_rank_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="download_rank_select_text">{{ $selectedRank ? $selectedRank : 'All Ranks' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="download_rank_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="download_rank_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search rank..."
                                           autocomplete="off">
                                </div>
                                <div id="download_rank_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1">
                            Select a rank to download results for that rank only, or leave as "All Ranks" to download everything.
                        </p>
                    </div>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <button type="submit" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-file-down"></i> Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="delete-confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                        <i class="ki-filled ki-information text-danger text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Deletion</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to delete the training result for <strong id="delete-recruit-name"></strong> (Appointment: <strong id="delete-appointment-number"></strong>)? This action cannot be undone.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form action="" method="POST" id="deleteResultForm" class="inline">
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

    @push('scripts')
    <script>
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Rank options
            const rankOptions = [
                {id: '', name: 'All Ranks'},
                @if(isset($availableRanks))
                @foreach($availableRanks as $rank)
                {id: '{{ $rank }}', name: '{{ $rank }}'},
                @endforeach
                @endif
            ];

            // Initialize rank filter select
            createSearchableSelect({
                triggerId: 'rank_filter_select_trigger',
                hiddenInputId: 'rank-filter',
                dropdownId: 'rank_filter_dropdown',
                searchInputId: 'rank_filter_search_input',
                optionsContainerId: 'rank_filter_options',
                displayTextId: 'rank_filter_select_text',
                options: rankOptions,
                placeholder: 'All Ranks',
                searchPlaceholder: 'Search rank...',
                onSelect: function() {
                    document.getElementById('rank-filter-form').submit();
                }
            });

            // Initialize download rank select
            createSearchableSelect({
                triggerId: 'download_rank_select_trigger',
                hiddenInputId: 'download-rank',
                dropdownId: 'download_rank_dropdown',
                searchInputId: 'download_rank_search_input',
                optionsContainerId: 'download_rank_options',
                displayTextId: 'download_rank_select_text',
                options: rankOptions,
                placeholder: 'All Ranks',
                searchPlaceholder: 'Search rank...'
            });
        });

        function showResultModal(resultId) {
            // Fetch result details via AJAX
            fetch(`{{ url('tradoc/results') }}/${resultId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    document.getElementById('result-modal-content').innerHTML = html;
                    const modal = document.getElementById('view-result-modal');
                    if (typeof KTModal !== 'undefined') {
                        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                        modalInstance.show();
                    } else {
                        modal.style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error loading result:', error);
                    alert('Failed to load result details. Please try again.');
                });
        }

        function showDeleteModal(resultId, appointmentNumber, officerName) {
            document.getElementById('delete-recruit-name').textContent = officerName;
            document.getElementById('delete-appointment-number').textContent = appointmentNumber;
            document.getElementById('deleteResultForm').action = `{{ url('tradoc/results') }}/${resultId}`;
            
            const modal = document.getElementById('delete-confirm-modal');
            if (typeof KTModal !== 'undefined') {
                const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                modalInstance.show();
            } else {
                modal.style.display = 'flex';
            }
        }

        function showDownloadModal() {
            const modal = document.getElementById('download-modal');
            if (typeof KTModal !== 'undefined') {
                const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                modalInstance.show();
            } else {
                modal.style.display = 'flex';
            }
        }
    </script>
    @endpush
@endsection
