@extends('layouts.app')

@section('title', 'Create Command')
@section('page-title', 'Create Command')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Commands</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Create New Command</h3>
            </div>
            <div class="kt-card-content">
                <form method="POST" action="{{ route('hrd.commands.store') }}" class="flex flex-col gap-5">
                    @csrf

                    @if($errors->any())
                        <div class="kt-card bg-danger/10 border border-danger/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">
                                Command Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   class="kt-input w-full @error('code') border-danger @enderror" 
                                   placeholder="e.g., APAPA, MMIA"
                                   required>
                            @error('code')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">
                                Command Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   class="kt-input w-full @error('name') border-danger @enderror" 
                                   placeholder="e.g., Apapa Command"
                                   required>
                            @error('name')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Zone <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="zone_id" id="zone_id" value="{{ old('zone_id', request('zone_id')) ?? '' }}" required>
                            <button type="button" 
                                    id="zone_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('zone_id') border-danger @enderror">
                                <span id="zone_select_text">
                                    @php
                                        $selectedZone = $zones->firstWhere('id', old('zone_id', request('zone_id')));
                                    @endphp
                                    {{ $selectedZone ? $selectedZone->name . ' (' . $selectedZone->code . ')' : 'Select Zone' }}
                                </span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="zone_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="zone_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search zones..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="zone_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        @error('zone_id')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">All commands must belong to a zone</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Location
                        </label>
                        <input type="text" 
                               name="location" 
                               value="{{ old('location') }}"
                               class="kt-input w-full @error('location') border-danger @enderror" 
                               placeholder="e.g., Lagos, Abuja">
                        @error('location')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="kt-checkbox">
                            <span class="text-sm text-secondary-foreground">Active</span>
                        </label>
                        <p class="text-xs text-secondary-foreground mt-1">Inactive commands cannot be assigned to officers</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-border">
                        <a href="{{ route('hrd.commands.index') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Create Command
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Data for searchable select
        @php
            $zonesData = $zones->map(function($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'code' => $zone->code ?? ''
                ];
            })->values();
        @endphp
        const zones = @json($zonesData);

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

        // Initialize zone select
        document.addEventListener('DOMContentLoaded', function() {
            createSearchableSelect({
                triggerId: 'zone_select_trigger',
                hiddenInputId: 'zone_id',
                dropdownId: 'zone_dropdown',
                searchInputId: 'zone_search_input',
                optionsContainerId: 'zone_options',
                displayTextId: 'zone_select_text',
                options: zones,
                displayFn: (zone) => zone.name + (zone.code ? ' (' + zone.code + ')' : ''),
                placeholder: 'Select Zone',
                searchPlaceholder: 'Search zones...'
            });
        });
    </script>
@endsection

