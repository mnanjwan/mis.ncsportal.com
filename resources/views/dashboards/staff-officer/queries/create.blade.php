@extends('layouts.app')

@section('title', 'Issue Query')
@section('page-title', 'Issue Query')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.queries.index') }}">Queries</a>
    <span>/</span>
    <span class="text-primary">Issue Query</span>
@endsection

@section('content')
@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Issue Query to Officer</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ route('staff-officer.queries.store') }}" method="POST">
                @csrf
                <div class="grid gap-5">
                    <!-- Officer Selection (Searchable Select) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Select Officer <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="officer_id" id="officer_id" value="{{ old('officer_id') }}" required>
                            <button type="button" 
                                    id="officer_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('officer_id') border-danger @enderror">
                                <span id="officer_select_text">{{ old('officer_id') ? ($officers->find(old('officer_id')) ? $officers->find(old('officer_id'))->initials . ' ' . $officers->find(old('officer_id'))->surname . ' - ' . $officers->find(old('officer_id'))->service_number : 'Select an officer') : 'Select an officer' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="officer_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="officer_search_input" 
                                               class="kt-input w-full" 
                                               placeholder="Search officers..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="officer_options" class="max-h-60 overflow-y-auto">
                                    @foreach($officers as $officer)
                                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                                             data-id="{{ $officer->id }}" 
                                             data-name="{{ $officer->initials }} {{ $officer->surname }}"
                                             data-service="{{ $officer->service_number }}"
                                             data-rank="{{ $officer->substantive_rank }}">
                                            <div class="text-sm text-foreground font-medium">{{ $officer->initials }} {{ $officer->surname }}</div>
                                            <div class="text-xs text-secondary-foreground">{{ $officer->service_number }} - {{ $officer->substantive_rank }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1" id="officer_info">{{ $officers->count() }} officer{{ $officers->count() !== 1 ? 's' : '' }} available</p>
                        @error('officer_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="kt-label">Reason(s) for Query <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="6" class="kt-input @error('reason') border-danger @enderror" placeholder="Provide detailed reason(s) for querying this officer..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-muted-foreground mt-1">Minimum 10 characters required</p>
                    </div>

                    <div>
                        <label class="kt-label">Response Deadline <span class="text-danger">*</span></label>
                        <input type="datetime-local" 
                               name="response_deadline" 
                               value="{{ old('response_deadline') }}"
                               class="kt-input @error('response_deadline') border-danger @enderror" 
                               required>
                        @error('response_deadline')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-muted-foreground mt-1">The officer must respond before this date and time. If no response is received by this deadline, the query will automatically be added to the officer's disciplinary record.</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Issue Query
                        </button>
                        <a href="{{ route('staff-officer.queries.index') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Officers data
        @php
            $officersData = $officers->map(function($officer) {
                return [
                    'id' => $officer->id,
                    'name' => $officer->initials . ' ' . $officer->surname,
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A'
                ];
            })->values();
        @endphp
        const officers = @json($officersData);

        const officerSelectTrigger = document.getElementById('officer_select_trigger');
        const officerSelectText = document.getElementById('officer_select_text');
        const officerHiddenInput = document.getElementById('officer_id');
        const officerDropdown = document.getElementById('officer_dropdown');
        const officerOptions = document.getElementById('officer_options');
        const officerSearchInput = document.getElementById('officer_search_input');
        const officerInfo = document.getElementById('officer_info');

        // Render officer options
        function renderOfficerOptions(officersList) {
            if (officersList.length === 0) {
                officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                return;
            }
            
            officerOptions.innerHTML = officersList.map(officer => {
                const details = officer.service_number !== 'N/A' ? officer.service_number : '';
                const rank = officer.rank !== 'N/A' ? ' - ' + officer.rank : '';
                const displayText = officer.name + (details ? ' (' + details + rank + ')' : '');
                
                return `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                         data-id="${officer.id}" 
                         data-name="${officer.name}"
                         data-service="${officer.service_number}"
                         data-rank="${officer.rank}">
                        <div class="text-sm text-foreground font-medium">${officer.name}</div>
                        <div class="text-xs text-secondary-foreground">${details}${rank}</div>
                    </div>
                `;
            }).join('');
            
            // Add click handlers
            officerOptions.querySelectorAll('.officer-option').forEach(option => {
                option.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const service = this.dataset.service;
                    const rank = this.dataset.rank;
                    
                    // Update hidden input
                    officerHiddenInput.value = id;
                    
                    // Update display text
                    const displayText = name + (service !== 'N/A' ? ' (' + service + (rank !== 'N/A' ? ' - ' + rank : '') + ')' : '');
                    officerSelectText.textContent = displayText;
                    
                    // Close dropdown
                    officerDropdown.classList.add('hidden');
                    
                    // Clear search
                    officerSearchInput.value = '';
                    
                    // Re-render all options
                    renderOfficerOptions(officers);
                });
            });
        }

        // Setup search functionality
        function setupOfficerSearch() {
            officerSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = officers.filter(officer => {
                    const nameMatch = officer.name.toLowerCase().includes(searchTerm);
                    const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
                    const rankMatch = officer.rank && officer.rank.toLowerCase().includes(searchTerm);
                    return nameMatch || serviceMatch || rankMatch;
                });
                
                renderOfficerOptions(filtered);
            });
        }

        // Initialize
        renderOfficerOptions(officers);
        setupOfficerSearch();

        // Toggle dropdown
        officerSelectTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            officerDropdown.classList.toggle('hidden');
            
            if (!officerDropdown.classList.contains('hidden')) {
                // Focus search input when dropdown opens
                setTimeout(() => {
                    officerSearchInput.focus();
                }, 100);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (officerDropdown && !officerDropdown.contains(e.target) && !officerSelectTrigger.contains(e.target)) {
                officerDropdown.classList.add('hidden');
            }
        });

        // Set initial selected officer if old input exists
        @if(old('officer_id'))
            const selectedOfficer = officers.find(o => o.id == {{ old('officer_id') }});
            if (selectedOfficer) {
                const displayText = selectedOfficer.name + (selectedOfficer.service_number !== 'N/A' ? ' (' + selectedOfficer.service_number + (selectedOfficer.rank !== 'N/A' ? ' - ' + selectedOfficer.rank : '') + ')' : '');
                officerSelectText.textContent = displayText;
            }
        @endif
    });
</script>
@endpush
@endsection
