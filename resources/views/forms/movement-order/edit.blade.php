@extends('layouts.app')

@section('title', 'Edit Movement Order')
@section('page-title', 'Edit Movement Order')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.movement-orders') }}">Movement Orders</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
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

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Edit Movement Order</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.movement-orders.update', $order->id) }}" method="POST" id="movement-order-form">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-5">
                        <!-- Order Number -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="order_number" 
                                   id="order_number"
                                   class="kt-input" 
                                   value="{{ old('order_number', $order->order_number) }}"
                                   required>
                            @error('order_number')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Criteria Months at Station -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Criteria (Months at Station) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="criteria_months_at_station" 
                                   id="criteria_months_at_station" 
                                   class="kt-input" 
                                   min="1" 
                                   value="{{ old('criteria_months_at_station', $order->criteria_months_at_station) }}"
                                   required>
                            <span class="text-xs text-secondary-foreground">Minimum number of months an officer must have been at their current station to be eligible for movement.</span>
                            @error('criteria_months_at_station')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Manning Request (Searchable) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Manning Request (Optional)</label>
                            @if($manningRequests->isEmpty())
                                <div class="p-3 rounded-lg bg-warning/10 border border-warning/20">
                                    <p class="text-sm text-secondary-foreground">
                                        <i class="ki-filled ki-information text-warning"></i> 
                                        No manning requests available.
                                    </p>
                                </div>
                            @else
                                <div class="relative">
                                    <input type="text" 
                                           id="manning_request_search" 
                                           class="kt-input w-full" 
                                           placeholder="Search manning request by command or ID..."
                                           value="{{ $order->manningRequest ? ($order->manningRequest->command->name ?? 'N/A') : '' }}"
                                           autocomplete="off">
                                    <input type="hidden" 
                                           name="manning_request_id" 
                                           id="manning_request_id"
                                           value="{{ old('manning_request_id', $order->manning_request_id) }}">
                                    <div id="manning_request_dropdown" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                                <div id="selected_manning_request" class="mt-2 p-2 bg-muted/50 rounded-lg {{ $order->manningRequest ? '' : 'hidden' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-medium" id="selected_manning_request_name">{{ $order->manningRequest ? ($order->manningRequest->command->name ?? 'N/A') : '' }}</span>
                                            <span class="text-xs text-secondary-foreground" id="selected_manning_request_details">
                                                @if($order->manningRequest)
                                                    MR-{{ str_pad($order->manningRequest->id, 4, '0', STR_PAD_LEFT) }} - {{ $order->manningRequest->status ?? 'N/A' }} - {{ $order->manningRequest->created_at ? $order->manningRequest->created_at->format('d/m/Y') : 'N/A' }}
                                                @endif
                                            </span>
                                        </div>
                                        <button type="button" 
                                                id="clear_manning_request" 
                                                class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                            <i class="ki-filled ki-cross"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <span class="text-xs text-secondary-foreground">Link this movement order to a manning request if it's fulfilling a specific command's request for officers.</span>
                            @error('manning_request_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="kt-input" required>
                                <option value="DRAFT" {{ old('status', $order->status) == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="PUBLISHED" {{ old('status', $order->status) == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                                <option value="CANCELLED" {{ old('status', $order->status) == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <span class="text-xs text-secondary-foreground">Draft: Order is being prepared. Published: Order is active and postings can be created.</span>
                            @error('status')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('hrd.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Update Movement Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Manning Request Searchable Select
        @php
            $manningRequestsData = $manningRequests->isEmpty() ? [] : $manningRequests->map(function($request) {
                return [
                    'id' => $request->id,
                    'command_name' => $request->command->name ?? 'N/A',
                    'request_id' => 'MR-' . str_pad($request->id, 4, '0', STR_PAD_LEFT),
                    'status' => $request->status ?? 'N/A',
                    'created_at' => $request->created_at ? $request->created_at->format('d/m/Y') : 'N/A',
                ];
            })->toArray();

            $selectedManningRequestData = null;
            if ($order->manningRequest) {
                $selectedManningRequestData = [
                    'id' => $order->manningRequest->id,
                    'name' => $order->manningRequest->command->name ?? 'N/A',
                    'details' => 'MR-' . str_pad($order->manningRequest->id, 4, '0', STR_PAD_LEFT) . ' - ' . ($order->manningRequest->status ?? 'N/A') . ' - ' . ($order->manningRequest->created_at ? $order->manningRequest->created_at->format('d/m/Y') : 'N/A')
                ];
            }
        @endphp

        const manningRequests = @json($manningRequestsData);
        let selectedManningRequest = @json($selectedManningRequestData);

        const manningRequestSearchInput = document.getElementById('manning_request_search');
        const manningRequestHiddenInput = document.getElementById('manning_request_id');
        const manningRequestDropdown = document.getElementById('manning_request_dropdown');
        const selectedManningRequestDiv = document.getElementById('selected_manning_request');
        const selectedManningRequestName = document.getElementById('selected_manning_request_name');
        const selectedManningRequestDetails = document.getElementById('selected_manning_request_details');

        // Create searchable select function
        function createManningRequestSelect(searchInput, hiddenInput, dropdown, selectedDiv, selectedName, selectedDetails, options, onSelect) {
            let selectedOption = selectedManningRequest;

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = options.filter(opt => 
                    opt.command_name.toLowerCase().includes(searchTerm) ||
                    opt.request_id.toLowerCase().includes(searchTerm)
                );

                dropdown.innerHTML = filtered.map(opt => {
                    return '<div class="p-3 hover:bg-muted cursor-pointer border-b border-input last:border-0" data-id="' + opt.id + '" data-name="' + opt.command_name + '" data-details="' + opt.request_id + ' - ' + opt.status + ' - ' + opt.created_at + '">' +
                        '<div class="font-medium text-sm">' + opt.command_name + '</div>' +
                        '<div class="text-xs text-secondary-foreground">' + opt.request_id + ' - ' + opt.status + ' - ' + opt.created_at + '</div>' +
                        '</div>';
                }).join('');
                dropdown.classList.remove('hidden');
            });

            dropdown.addEventListener('click', function(e) {
                const option = e.target.closest('[data-id]');
                if (option) {
                    const foundOption = options.find(o => o.id == option.dataset.id);
                    selectedOption = {
                        id: option.dataset.id,
                        name: option.dataset.name,
                        details: option.dataset.details
                    };
                    hiddenInput.value = selectedOption.id;
                    searchInput.value = selectedOption.name;
                    selectedName.textContent = selectedOption.name;
                    selectedDetails.textContent = selectedOption.details;
                    selectedDiv.classList.remove('hidden');
                    dropdown.classList.add('hidden');
                    if (onSelect) onSelect(selectedOption);
                }
            });

            // Clear selection
            document.getElementById('clear_manning_request')?.addEventListener('click', function() {
                selectedOption = null;
                hiddenInput.value = '';
                searchInput.value = '';
                selectedDiv.classList.add('hidden');
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Initialize manning request select (only if there are manning requests)
        @if(!$manningRequests->isEmpty())
            createManningRequestSelect(
                manningRequestSearchInput,
                manningRequestHiddenInput,
                manningRequestDropdown,
                selectedManningRequestDiv,
                selectedManningRequestName,
                selectedManningRequestDetails,
                manningRequests,
                function(option) {
                    selectedManningRequest = option;
                }
            );
        @endif

        // Form validation before submit
        document.getElementById('movement-order-form')?.addEventListener('submit', function(e) {
            const criteriaMonths = document.getElementById('criteria_months_at_station').value;
            
            if (!criteriaMonths || criteriaMonths < 1) {
                e.preventDefault();
                alert('Please enter a valid criteria (minimum 1 month)');
                return false;
            }
        });
    </script>
    @endpush
@endsection

