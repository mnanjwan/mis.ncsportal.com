@extends('layouts.app')

@section('title', 'Allocate Vehicle to Command')
@section('page-title', 'Allocate Vehicle to Command')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.cc-tl.dashboard') }}">Fleet</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.index') }}">Vehicles</a>
    <span>/</span>
    <span class="text-primary">Allocate to Command</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Allocate Vehicle Directly to Command</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <p class="text-sm text-secondary-foreground mb-4">
                Select a vehicle in stock and the command to receive it. The command (Area Controller) will acknowledge receipt on the vehicle page.
            </p>

            @if($vehicles->isEmpty())
                <p class="text-sm text-secondary-foreground">No vehicles in stock available for allocation. Only vehicles with lifecycle status <strong>IN_STOCK</strong> and not reserved for a request can be allocated.</p>
                <a class="kt-btn kt-btn-secondary mt-4" href="{{ route('fleet.vehicles.index') }}">Back to Vehicles</a>
            @else
                <form method="POST" action="{{ route('fleet.allocate-to-command.store') }}" class="grid gap-4 max-w-2xl">
                    @csrf

                    <div>
                        <label class="text-sm font-medium" for="fleet_vehicle_id_select_trigger">Vehicle <span class="text-red-600">*</span></label>
                        <input type="hidden" name="fleet_vehicle_id" id="fleet_vehicle_id" value="{{ old('fleet_vehicle_id') }}" required>
                        <div class="relative">
                            <button type="button" id="fleet_vehicle_id_select_trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                @php
                                $selVehicle = $vehicles->firstWhere('id', old('fleet_vehicle_id'));
                                $vehicleLabel = $selVehicle ? ($selVehicle->reg_no ?? $selVehicle->chassis_number) . ' — ' . trim(($selVehicle->make ?? '') . ' ' . ($selVehicle->model ?? '')) . ' (' . $selVehicle->vehicle_type . ')' : 'Search for vehicle...';
                            @endphp
                                <span id="fleet_vehicle_id_select_text">{{ $vehicleLabel }}</span>
                                <i class="ki-filled ki-down text-muted-foreground"></i>
                            </button>
                            <div id="fleet_vehicle_id_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                <div class="p-2 border-b border-input">
                                    <input type="text" id="fleet_vehicle_id_search_input" class="kt-input w-full" placeholder="Search vehicles..." autocomplete="off">
                                </div>
                                <div id="fleet_vehicle_id_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        @error('fleet_vehicle_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium" for="command_id_select_trigger">Command to receive <span class="text-red-600">*</span></label>
                        <input type="hidden" name="command_id" id="command_id" value="{{ old('command_id') }}" required>
                        <div class="relative">
                            <button type="button" id="command_id_select_trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="command_id_select_text">{{ $commands->firstWhere('id', old('command_id'))?->name ?? 'Search for command...' }}</span>
                                <i class="ki-filled ki-down text-muted-foreground"></i>
                            </button>
                            <div id="command_id_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                <div class="p-2 border-b border-input">
                                    <input type="text" id="command_id_search_input" class="kt-input w-full" placeholder="Search commands..." autocomplete="off">
                                </div>
                                <div id="command_id_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        @error('command_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium" for="notes">Notes (optional)</label>
                        <textarea class="kt-textarea w-full" name="notes" id="notes" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">Allocate to Command</button>
                        <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.index') }}">Cancel</a>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if(!$vehicles->isEmpty())
        @push('scripts')
        <script>
            (function() {
                var vehicles = @json($vehicles->map(fn($v) => ['id' => $v->id, 'label' => ($v->reg_no ?? $v->chassis_number) . ' — ' . trim(($v->make ?? '') . ' ' . ($v->model ?? '')) . ' (' . $v->vehicle_type . ')'])->values());
                var vTrigger = document.getElementById('fleet_vehicle_id_select_trigger');
                var vHidden = document.getElementById('fleet_vehicle_id');
                var vDropdown = document.getElementById('fleet_vehicle_id_dropdown');
                var vSearch = document.getElementById('fleet_vehicle_id_search_input');
                var vOptions = document.getElementById('fleet_vehicle_id_options');
                var vDisplay = document.getElementById('fleet_vehicle_id_select_text');
                if (vTrigger && vHidden && vDropdown && vSearch && vOptions && vDisplay) {
                    var vFiltered = vehicles.slice();
                    function renderVehicleOptions(opts) {
                        if (opts.length === 0) {
                            vOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No vehicles found</div>';
                            return;
                        }
                        vOptions.innerHTML = opts.map(function(opt) {
                            var label = (opt.label || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                            return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" data-id="' + opt.id + '" data-name="' + label + '"><div class="text-sm text-foreground">' + (opt.label || '') + '</div></div>';
                        }).join('');
                        vOptions.querySelectorAll('.select-option').forEach(function(opt) {
                            opt.addEventListener('click', function() {
                                vHidden.value = this.dataset.id;
                                vDisplay.textContent = this.dataset.name || 'Search for vehicle...';
                                vDropdown.classList.add('hidden');
                                vSearch.value = '';
                                vFiltered = vehicles.slice();
                                renderVehicleOptions(vFiltered);
                            });
                        });
                    }
                    function openVehicle() {
                        vDropdown.classList.remove('hidden');
                        var rect = vTrigger.getBoundingClientRect();
                        vDropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 4) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 320) + 'px;';
                        setTimeout(function() { vSearch.focus(); }, 100);
                    }
                    function closeVehicle() {
                        vDropdown.classList.add('hidden');
                        vDropdown.style.cssText = '';
                    }
                    renderVehicleOptions(vFiltered);
                    vSearch.addEventListener('input', function() {
                        var term = this.value.toLowerCase();
                        vFiltered = vehicles.filter(function(v) { return (v.label || '').toLowerCase().includes(term); });
                        renderVehicleOptions(vFiltered);
                    });
                    vTrigger.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (vDropdown.classList.contains('hidden')) openVehicle(); else closeVehicle();
                    });
                    document.addEventListener('click', function(e) {
                        setTimeout(function() {
                            if (!vTrigger.contains(e.target) && !vDropdown.contains(e.target)) closeVehicle();
                        }, 0);
                    });
                }
            })();
            (function() {
                var commands = @json($commands->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());
                var trigger = document.getElementById('command_id_select_trigger');
                var hiddenInput = document.getElementById('command_id');
                var dropdown = document.getElementById('command_id_dropdown');
                var searchInput = document.getElementById('command_id_search_input');
                var optionsContainer = document.getElementById('command_id_options');
                var displayText = document.getElementById('command_id_select_text');
                if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) return;

                var filteredOptions = commands.slice();

                function renderOptions(opts) {
                    if (opts.length === 0) {
                        optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No commands found</div>';
                        return;
                    }
                    optionsContainer.innerHTML = opts.map(function(opt) {
                        return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" data-id="' + opt.id + '" data-name="' + (opt.name || '').replace(/"/g, '&quot;') + '">' +
                            '<div class="text-sm text-foreground">' + (opt.name || '') + '</div></div>';
                    }).join('');
                    optionsContainer.querySelectorAll('.select-option').forEach(function(opt) {
                        opt.addEventListener('click', function() {
                            hiddenInput.value = this.dataset.id;
                            displayText.textContent = this.dataset.name || 'Select command...';
                            dropdown.classList.add('hidden');
                            searchInput.value = '';
                            filteredOptions = commands.slice();
                            renderOptions(filteredOptions);
                        });
                    });
                }

                function openDropdown() {
                    dropdown.classList.remove('hidden');
                    var rect = trigger.getBoundingClientRect();
                    dropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 4) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 280) + 'px;';
                    setTimeout(function() { searchInput.focus(); }, 100);
                }

                function closeDropdown() {
                    dropdown.classList.add('hidden');
                    dropdown.style.cssText = '';
                }

                renderOptions(filteredOptions);
                searchInput.addEventListener('input', function() {
                    var term = this.value.toLowerCase();
                    filteredOptions = commands.filter(function(c) { return (c.name || '').toLowerCase().includes(term); });
                    renderOptions(filteredOptions);
                });
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (dropdown.classList.contains('hidden')) openDropdown(); else closeDropdown();
                });
                document.addEventListener('click', function(e) {
                    setTimeout(function() {
                        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) closeDropdown();
                    }, 0);
                });
            })();
        </script>
        @endpush
    @endif
@endsection
