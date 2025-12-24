@extends('layouts.app')

@section('title', 'Eligible Officers')
@section('page-title', 'Eligible Officers')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.movement-orders') }}">Movement Orders</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.movement-orders.show', $order->id) }}">Order #{{ $order->order_number }}</a>
    <span>/</span>
    <span class="text-primary">Eligible Officers</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Movement Order
            </a>
        </div>

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

        <!-- Order Info Card -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex flex-col gap-2">
                    <h3 class="text-lg font-semibold">Movement Order #{{ $order->order_number }}</h3>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Criteria: <span class="font-semibold">{{ $criteriaMonths }} months at station</span>
                        </span>
                        @if($order->manningRequest)
                            <span class="text-secondary-foreground">
                                Manning Request: <span class="font-semibold">#{{ $order->manningRequest->id }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Eligible Officers Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Eligible Officers</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        {{ $officers->count() }} officer(s) found
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($officers->count() > 0)
                    <form action="{{ route('hrd.movement-orders.post-officers', $order->id) }}" method="POST" id="post-officers-form">
                        @csrf
                        
                        <!-- Commands Selection -->
                        <div class="p-4 bg-muted/50 border-b border-border">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex-1">
                                    <label class="kt-form-label mb-2">Select Destination Commands</label>
                                    <p class="text-xs text-secondary-foreground mb-3">
                                        Assign each selected officer to a destination command. Officers will be posted to these commands.
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <a href="{{ route('hrd.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="kt-btn kt-btn-primary" id="post-officers-btn-top" disabled>
                                        <i class="ki-filled ki-check"></i> Post Selected Officers
                                    </button>
                                </div>
                            </div>
                            @php
                                $commands = \App\Models\Command::where('is_active', true)->orderBy('name')->get();
                            @endphp
                            <select name="default_command_id" id="default_command_id" class="kt-input">
                                <option value="">Select default command...</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}">{{ $command->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="apply-default-command" class="kt-btn kt-btn-sm kt-btn-secondary mt-2">
                                Apply to All Selected
                            </button>
                        </div>

                        <!-- Posting Date -->
                        <div class="p-4 bg-muted/50 border-b border-border">
                            <label class="kt-form-label mb-2">Posting Date</label>
                            <input type="date" name="posting_date" id="posting_date" class="kt-input" value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Officers Table -->
                        <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                            <table class="kt-table" style="min-width: 1000px; width: 100%;">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                            <input type="checkbox" id="select-all-officers" class="rounded">
                                        </th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Station</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Months at Station</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Destination Command</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($officers as $index => $officer)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" 
                                                       name="officer_ids[]" 
                                                       value="{{ $officer->id }}" 
                                                       class="officer-checkbox rounded"
                                                       data-index="{{ $index }}">
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-foreground">
                                                {{ $officer->service_number ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->initials }} {{ $officer->surname }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->substantive_rank ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->presentStation->name ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                <span class="font-semibold">{{ $officer->months_at_station ?? 0 }}</span> months
                                            </td>
                                            <td class="py-3 px-4">
                                                <select name="to_command_ids[]" 
                                                        class="kt-input kt-input-sm command-select"
                                                        data-index="{{ $index }}"
                                                        disabled>
                                                    <option value="">Select command...</option>
                                                    @foreach($commands as $command)
                                                        <option value="{{ $command->id }}">{{ $command->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 p-4 border-t border-border">
                            <a href="{{ route('hrd.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary" id="post-officers-btn" disabled>
                                <i class="ki-filled ki-check"></i> Post Selected Officers
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No eligible officers found matching the criteria.</p>
                        <p class="text-xs text-secondary-foreground">
                            Criteria: Officers who have been at their current station for {{ $criteriaMonths }} months or more.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize: Ensure all command selects start disabled
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.command-select').forEach(select => {
                select.disabled = true;
            });
            // Initial button state update
            updatePostButton();
            
            // Also check on page load if any checkboxes are already checked (e.g., from browser back button)
            document.querySelectorAll('.officer-checkbox').forEach(checkbox => {
                if (checkbox.checked) {
                    toggleCommandSelect(checkbox);
                }
            });
            updatePostButton();
        });

        // Select all checkbox
        document.getElementById('select-all-officers')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.officer-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                toggleCommandSelect(cb);
            });
            updatePostButton();
            updateSelectAll();
        });

        // Individual officer checkbox
        document.querySelectorAll('.officer-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleCommandSelect(this);
                updatePostButton();
                updateSelectAll();
            });
        });

        // Toggle command select enabled/disabled based on checkbox
        function toggleCommandSelect(checkbox) {
            const index = checkbox.dataset.index;
            const commandSelect = document.querySelector(`.command-select[data-index="${index}"]`);
            if (commandSelect) {
                commandSelect.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    commandSelect.value = '';
                }
            }
        }

        // Update post button state (syncs both top and bottom buttons)
        function updatePostButton() {
            const checkedBoxes = document.querySelectorAll('.officer-checkbox:checked');
            const postBtn = document.getElementById('post-officers-btn');
            const postBtnTop = document.getElementById('post-officers-btn-top');
            
            // If no officers are selected, disable buttons
            if (checkedBoxes.length === 0) {
                if (postBtn) postBtn.disabled = true;
                if (postBtnTop) postBtnTop.disabled = true;
                return;
            }

            // Check if at least one selected officer has a command assigned
            // This allows batch posting - you can post officers that have commands assigned
            let atLeastOneHasCommand = false;
            checkedBoxes.forEach(checkbox => {
                const index = checkbox.dataset.index;
                const commandSelect = document.querySelector(`.command-select[data-index="${index}"]`);
                // Check if select exists, is enabled (not disabled), and has a value
                if (commandSelect && !commandSelect.disabled && commandSelect.value && commandSelect.value !== '') {
                    atLeastOneHasCommand = true;
                }
            });

            // Enable buttons if at least one selected officer has a command
            const shouldEnable = atLeastOneHasCommand;
            if (postBtn) {
                postBtn.disabled = !shouldEnable;
                // Update button styling
                if (shouldEnable) {
                    postBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    postBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
            if (postBtnTop) {
                postBtnTop.disabled = !shouldEnable;
                // Update button styling
                if (shouldEnable) {
                    postBtnTop.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    postBtnTop.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Update select all checkbox state
        function updateSelectAll() {
            const checkboxes = document.querySelectorAll('.officer-checkbox');
            const selectAll = document.getElementById('select-all-officers');
            const checkedCount = document.querySelectorAll('.officer-checkbox:checked').length;
            
            if (selectAll) {
                selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        }

        // Command select change handler
        document.querySelectorAll('.command-select').forEach(select => {
            select.addEventListener('change', function() {
                updatePostButton();
            });
        });

        // Make top button trigger form submission
        document.getElementById('post-officers-btn-top')?.addEventListener('click', function(e) {
            // Let the form handle submission naturally
            // The form's submit handler will validate
        });

        // Also trigger update when default command is applied
        document.getElementById('apply-default-command')?.addEventListener('click', function() {
            const defaultCommandId = document.getElementById('default_command_id').value;
            if (!defaultCommandId) {
                alert('Please select a default command first.');
                return;
            }

            let appliedCount = 0;
            document.querySelectorAll('.officer-checkbox:checked').forEach(checkbox => {
                const index = checkbox.dataset.index;
                const commandSelect = document.querySelector(`.command-select[data-index="${index}"]`);
                if (commandSelect && !commandSelect.disabled) {
                    commandSelect.value = defaultCommandId;
                    appliedCount++;
                }
            });

            if (appliedCount === 0) {
                alert('Please select at least one officer first.');
                return;
            }

            updatePostButton();
        });


        // Form validation
        document.getElementById('post-officers-form')?.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.officer-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one officer to post.');
                return false;
            }

            // Collect officers with commands (only post those that have commands assigned)
            let officersWithCommands = [];
            let officersWithoutCommands = [];
            
            checkedBoxes.forEach(checkbox => {
                const index = checkbox.dataset.index;
                const commandSelect = document.querySelector(`.command-select[data-index="${index}"]`);
                const row = checkbox.closest('tr');
                const name = row.querySelector('td:nth-child(3)').textContent.trim();
                
                if (commandSelect && commandSelect.value) {
                    officersWithCommands.push(name);
                } else {
                    officersWithoutCommands.push(name);
                    // Uncheck officers without commands so they won't be posted
                    checkbox.checked = false;
                }
            });

            if (officersWithCommands.length === 0) {
                e.preventDefault();
                alert('Please assign destination commands to at least one selected officer.');
                return false;
            }

            // Show warning if some officers won't be posted
            if (officersWithoutCommands.length > 0) {
                const proceed = confirm(
                    `Only officers with assigned commands will be posted (${officersWithCommands.length} officer(s)).\n\n` +
                    `The following officers will NOT be posted (no command assigned):\n` +
                    officersWithoutCommands.join(', ') +
                    `\n\nDo you want to continue?`
                );
                
                if (!proceed) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Update button state after unchecking
            updatePostButton();
        });
    </script>
    @endpush

    <style>
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

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
@endsection


