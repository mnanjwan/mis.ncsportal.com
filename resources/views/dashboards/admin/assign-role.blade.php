@extends('layouts.app')

@section('title', 'Assign Role')
@section('page-title', 'Assign Role to Officer')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.dashboard') }}">Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.role-assignments') }}">Role Assignments</a>
    <span>/</span>
    <span class="text-primary">Assign Role</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Assign Role to Officer</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('admin.role-assignments.store') }}" method="POST" class="flex flex-col gap-5">
                    @csrf

                    <!-- Information Banner -->
                    <div class="bg-info/10 border border-info/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-information-2 text-info text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-info mb-2">Role Assignment Guidelines</h4>
                                <ul class="text-sm text-secondary-foreground space-y-1 list-disc list-inside">
                                    <li>You can assign roles to officers in your command: <strong>{{ $adminCommand->name }}</strong></li>
                                    <li>You can assign the following roles: <strong>Staff Officer</strong>, <strong>Area Controller</strong>, and <strong>DC Admin</strong></li>
                                    <li>All role assignments are specific to your command</li>
                                    <li>The officer will receive email and app notifications when assigned</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Command Display (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Command</label>
                        <input type="text" 
                               value="{{ $adminCommand->name }}" 
                               class="kt-input w-full bg-muted/50" 
                               readonly>
                        <p class="text-xs text-secondary-foreground mt-1">You can only assign roles within your assigned command</p>
                    </div>

                    <!-- Officer Selection (Searchable Select) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Officer <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="officer_id" id="officer_id" required>
                            <button type="button" 
                                    id="officer_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="officer_select_text">Select Officer</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="officer_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                        <input type="text" 
                                               id="officer_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search officers..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="officer_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1" id="officer_info"></p>
                        @error('officer_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select name="role_id" id="role_id" class="kt-input w-full" required>
                            <option value="">Select Role</option>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">Select a role to assign</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.role-assignments') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Assign Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Officers data
        @php
            $officersData = $officers->map(function($officer) {
                // Match HRD version name structure
                $name = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '') . ' ' . ($officer->first_name ?? ''));
                return [
                    'id' => $officer->id,
                    'name' => $name,
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A'
                ];
            })->values();
        @endphp
        const officers = @json($officersData);
        
        // Initialize officer options
        function renderOfficerOptions(officersList) {
            const officerOptions = document.getElementById('officer_options');
            
            if (officersList.length === 0) {
                officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                return;
            }
            
            officerOptions.innerHTML = officersList.map(officer => {
                const details = officer.service_number !== 'N/A' ? officer.service_number : '';
                const rank = officer.rank !== 'N/A' && officer.rank ? ' - ' + officer.rank : '';
                const displayText = officer.name + (details ? ' (' + details + rank + ')' : '');
                
                return `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                         data-id="${officer.id}" 
                         data-name="${officer.name}"
                         data-service="${details}"
                         data-rank="${officer.rank}">
                        <div class="text-sm text-foreground">${officer.name}</div>
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
                    document.getElementById('officer_id').value = id;
                    
                    // Update display text
                    const displayText = name + (service !== 'N/A' ? ' (' + service + (rank !== 'N/A' && rank ? ' - ' + rank : '') + ')' : '');
                    document.getElementById('officer_select_text').textContent = displayText;
                    
                    // Close dropdown
                    document.getElementById('officer_dropdown').classList.add('hidden');
                    
                    // Clear search
                    document.getElementById('officer_search_input').value = '';
                });
            });
        }

        // Setup officer search
        function setupOfficerSearch() {
            const searchInput = document.getElementById('officer_search_input');
            const officerOptions = document.getElementById('officer_options');
            
            searchInput.addEventListener('input', function() {
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Render initial options
            renderOfficerOptions(officers);
            
            // Setup search
            setupOfficerSearch();
            
            // Update info text
            const officerInfo = document.getElementById('officer_info');
            if (officers.length > 0) {
                officerInfo.textContent = `${officers.length} officer${officers.length !== 1 ? 's' : ''} available`;
            } else {
                officerInfo.textContent = 'No officers found in this command.';
                officerInfo.classList.add('text-danger');
            }
        });

        // Toggle dropdown
        document.getElementById('officer_select_trigger')?.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('officer_dropdown');
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                // Focus search input when dropdown opens
                setTimeout(() => {
                    document.getElementById('officer_search_input').focus();
                }, 100);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('officer_dropdown');
            const trigger = document.getElementById('officer_select_trigger');
            if (dropdown && !dropdown.contains(e.target) && !trigger.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    @endpush
@endsection

