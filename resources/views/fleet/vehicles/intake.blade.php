@extends('layouts.app')

@section('title', 'Receive Vehicle')
@section('page-title', 'Receive Vehicle')

@section('breadcrumbs')
    @php
        $user = auth()->user();
        $dashboardRoute = null;
        if ($user->hasRole('CD')) {
            $dashboardRoute = route('fleet.cd.dashboard');
        } elseif ($user->hasRole('O/C T&L')) {
            $dashboardRoute = route('fleet.oc-tl.dashboard');
        } elseif ($user->hasRole('Transport Store/Receiver')) {
            $dashboardRoute = route('fleet.store-receiver.dashboard');
        } elseif ($user->hasRole('CC T&L')) {
            $dashboardRoute = route('fleet.cc-tl.dashboard');
        } elseif ($user->hasRole('DCG FATS')) {
            $dashboardRoute = route('fleet.dcg-fats.dashboard');
        } elseif ($user->hasRole('ACG TS')) {
            $dashboardRoute = route('fleet.acg-ts.dashboard');
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">Fleet</a>
        <span>/</span>
    @endif
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.index') }}">Vehicles</a>
    <span>/</span>
    <span class="text-primary">Receive Vehicle</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Store/Transport Vehicle Intake</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="POST" action="{{ route('fleet.vehicles.intake.store') }}" class="grid gap-4 max-w-2xl" id="vehicleIntakeForm">
                @csrf

                <!-- Vehicle Model Selection (searchable) -->
                <div>
                    <label class="text-sm font-medium" for="vehicle_model_id_trigger">Vehicle Model <span class="text-red-600">*</span></label>
                    <input type="hidden" name="vehicle_model_id" id="vehicle_model_id" value="{{ old('vehicle_model_id') }}" required>
                    <div class="relative">
                        <button type="button" id="vehicle_model_id_trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                            @php
                                $oldModel = old('vehicle_model_id');
                                $modelLabel = 'Search vehicle model or create new...';
                                if ($oldModel === 'new') {
                                    $modelLabel = '+ Create New Model';
                                } elseif ($oldModel && $vehicleModels->contains('id', $oldModel)) {
                                    $m = $vehicleModels->firstWhere('id', $oldModel);
                                    $modelLabel = $m ? $m->display_name : $modelLabel;
                                }
                            @endphp
                            <span id="vehicle_model_id_text">{{ $modelLabel }}</span>
                            <i class="ki-filled ki-down text-muted-foreground"></i>
                        </button>
                        <div id="vehicle_model_id_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                            <div class="p-2 border-b border-input">
                                <input type="text" id="vehicle_model_id_search" class="kt-input w-full" placeholder="Search vehicle model..." autocomplete="off">
                            </div>
                            <div id="vehicle_model_id_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                    @error('vehicle_model_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-secondary-foreground mt-1">Select an existing vehicle model (e.g., Toyota PickUp 2018) or create a new one</p>
                </div>

                <!-- New Model Fields (hidden by default) -->
                <div id="newModelFields" style="display: none;" class="grid gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-sm">Create New Vehicle Model</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium">Make <span class="text-red-600">*</span></label>
                            <input class="kt-input w-full" name="make" id="make" value="{{ old('make') }}" placeholder="e.g., Toyota, Honda" />
                            @error('make')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium" for="vehicle_type_trigger">Vehicle Type <span class="text-red-600">*</span></label>
                            <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type') }}">
                            <div class="relative">
                                <button type="button" id="vehicle_type_trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    @php
                                        $ot = old('vehicle_type');
                                        $typeDisplay = ($ot && isset($vehicleTypes[$ot])) ? $vehicleTypes[$ot] : ($ot ?: 'Search vehicle type...');
                                    @endphp
                                    <span id="vehicle_type_text">{{ $typeDisplay }}</span>
                                    <i class="ki-filled ki-down text-muted-foreground"></i>
                                </button>
                                <div id="vehicle_type_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-2 border-b border-input">
                                        <input type="text" id="vehicle_type_search" class="kt-input w-full" placeholder="Search vehicle type..." autocomplete="off">
                                    </div>
                                    <div id="vehicle_type_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                            <div id="new_vehicle_type_fields" style="display: none;" class="mt-2">
                                <label class="text-sm font-medium" for="new_vehicle_type_input">New vehicle type name <span class="text-red-600">*</span></label>
                                <input type="text" class="kt-input w-full mt-1" id="new_vehicle_type_input" value="{{ old('vehicle_type') }}" placeholder="e.g., Amphibian, Tricycle" maxlength="100" autocomplete="off">
                                <p class="text-xs text-secondary-foreground mt-1">Enter a type not in the list above</p>
                            </div>
                            @error('vehicle_type')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium">Year of Manufacture <span class="text-red-600">*</span></label>
                            <input class="kt-input w-full" type="number" name="year_of_manufacture" id="year_of_manufacture" 
                                   value="{{ old('year_of_manufacture') }}" 
                                   min="1950" max="{{ date('Y') }}" 
                                   placeholder="e.g., 2018" />
                            @error('year_of_manufacture')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p class="text-xs text-secondary-foreground">This will create a new vehicle model: <strong id="modelPreview">Make VehicleType Year</strong></p>
                </div>

                <!-- Vehicle-Specific Fields -->
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-sm mb-4">Vehicle Details</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="text-sm font-medium">Chassis Number <span class="text-red-600">*</span></label>
                            <input class="kt-input w-full" name="chassis_number" value="{{ old('chassis_number') }}" required />
                            @error('chassis_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-secondary-foreground mt-1">Primary identifier - must be unique</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Engine Number</label>
                            <input class="kt-input w-full" name="engine_number" value="{{ old('engine_number') }}" />
                            @error('engine_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-secondary-foreground mt-1">Primary identifier - can be changed later</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium">Reg No</label>
                            <input class="kt-input w-full" name="reg_no" value="{{ old('reg_no') }}" />
                            @error('reg_no')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium">Received Date</label>
                            <input class="kt-input w-full" type="date" name="received_at" value="{{ old('received_at') }}" />
                            @error('received_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="text-sm font-medium">Date of Allocation (optional)</label>
                            <input class="kt-input w-full" type="date" name="date_of_allocation" value="{{ old('date_of_allocation') }}" />
                            @error('date_of_allocation')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-medium">Notes</label>
                        <textarea class="kt-textarea w-full" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button class="kt-btn kt-btn-primary" type="submit">Receive Vehicle</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            var vehicleModels = @json($vehicleModels->map(fn($m) => ['id' => (string)$m->id, 'label' => $m->display_name])->values()->all());
            var modelOptions = vehicleModels.slice();
            modelOptions.unshift({ id: '', label: 'Select existing model or create new...' });
            modelOptions.push({ id: 'new', label: '+ Create New Model' });

            var vehicleTypes = @json($vehicleTypesForJs ?? []);

            function toggleModelFields() {
                var hidden = document.getElementById('vehicle_model_id');
                var newModelFields = document.getElementById('newModelFields');
                var makeInput = document.getElementById('make');
                var typeInput = document.getElementById('vehicle_type');
                var yearInput = document.getElementById('year_of_manufacture');
                if (!hidden || !newModelFields) return;
                if (hidden.value === 'new') {
                    newModelFields.style.display = 'block';
                    if (makeInput) makeInput.required = true;
                    if (typeInput) typeInput.required = true;
                    if (yearInput) yearInput.required = true;
                } else {
                    newModelFields.style.display = 'none';
                    if (makeInput) makeInput.required = false;
                    if (typeInput) typeInput.required = false;
                    if (yearInput) yearInput.required = false;
                }
                updateModelPreview();
            }

            function updateModelPreview() {
                var makeEl = document.getElementById('make');
                var typeEl = document.getElementById('vehicle_type');
                var yearEl = document.getElementById('year_of_manufacture');
                var make = makeEl ? makeEl.value : '';
                var typeVal = typeEl ? typeEl.value : '';
                var typeLabel = (vehicleTypes.find(function(t){ return t.id === typeVal; }) || {}).name || typeVal || 'VehicleType';
                var year = yearEl ? yearEl.value : '';
                var preview = document.getElementById('modelPreview');
                if (preview) preview.textContent = (make || 'Make') + ' ' + (typeLabel || 'VehicleType') + ' ' + (year || 'Year');
            }

            // Vehicle Model searchable select
            var modelTrigger = document.getElementById('vehicle_model_id_trigger');
            var modelHidden = document.getElementById('vehicle_model_id');
            var modelDropdown = document.getElementById('vehicle_model_id_dropdown');
            var modelSearch = document.getElementById('vehicle_model_id_search');
            var modelOptionsEl = document.getElementById('vehicle_model_id_options');
            var modelText = document.getElementById('vehicle_model_id_text');
            if (modelTrigger && modelHidden && modelDropdown && modelSearch && modelOptionsEl && modelText) {
                var modelFiltered = modelOptions.slice();
                function renderModelOpts(opts) {
                    if (opts.length === 0) {
                        modelOptionsEl.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                        return;
                    }
                    modelOptionsEl.innerHTML = opts.map(function(o) {
                        var label = (o.label || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 intake-model-opt" data-id="' + (o.id || '').replace(/"/g, '&quot;') + '" data-label="' + label + '"><div class="text-sm text-foreground">' + (o.label || '') + '</div></div>';
                    }).join('');
                    modelOptionsEl.querySelectorAll('.intake-model-opt').forEach(function(opt) {
                        opt.addEventListener('click', function() {
                            modelHidden.value = this.dataset.id || '';
                            modelText.textContent = this.dataset.label || 'Search vehicle model or create new...';
                            modelDropdown.classList.add('hidden');
                            modelSearch.value = '';
                            modelFiltered = modelOptions.slice();
                            renderModelOpts(modelFiltered);
                            toggleModelFields();
                        });
                    });
                }
                function openModelDropdown() {
                    modelDropdown.classList.remove('hidden');
                    var rect = modelTrigger.getBoundingClientRect();
                    modelDropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 4) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 260) + 'px;';
                    setTimeout(function() { modelSearch.focus(); }, 100);
                }
                function closeModelDropdown() {
                    modelDropdown.classList.add('hidden');
                    modelDropdown.style.cssText = '';
                }
                renderModelOpts(modelFiltered);
                modelSearch.addEventListener('input', function() {
                    var term = this.value.toLowerCase();
                    modelFiltered = modelOptions.filter(function(o) { return (o.label || '').toLowerCase().includes(term); });
                    renderModelOpts(modelFiltered);
                });
                modelTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (modelDropdown.classList.contains('hidden')) openModelDropdown(); else closeModelDropdown();
                });
                document.addEventListener('click', function(e) {
                    if (!modelTrigger.contains(e.target) && !modelDropdown.contains(e.target)) closeModelDropdown();
                });
            }

            // Vehicle Type searchable select (in Create New Model) + Add new vehicle type
            var typeTrigger = document.getElementById('vehicle_type_trigger');
            var typeHidden = document.getElementById('vehicle_type');
            var typeDropdown = document.getElementById('vehicle_type_dropdown');
            var typeSearch = document.getElementById('vehicle_type_search');
            var typeOptionsEl = document.getElementById('vehicle_type_options');
            var typeText = document.getElementById('vehicle_type_text');
            var newTypeFields = document.getElementById('new_vehicle_type_fields');
            var newTypeInput = document.getElementById('new_vehicle_type_input');
            var typeOptionsWithNew = (vehicleTypes || []).slice();
            typeOptionsWithNew.push({ id: '__new__', name: 'Add new vehicle type' });
            if (typeTrigger && typeHidden && typeDropdown && typeSearch && typeOptionsEl && typeText) {
                var typeFiltered = typeOptionsWithNew.slice();
                function showNewTypeFields(show) {
                    if (newTypeFields) newTypeFields.style.display = show ? 'block' : 'none';
                    if (show && newTypeInput) {
                        newTypeInput.required = true;
                        if (newTypeInput.value.trim()) {
                            typeHidden.value = newTypeInput.value.trim();
                            typeText.textContent = newTypeInput.value.trim();
                        } else {
                            typeHidden.value = '';
                            typeText.textContent = 'Add new vehicle type';
                        }
                        setTimeout(function() { newTypeInput.focus(); }, 100);
                    } else {
                        if (newTypeInput) { newTypeInput.value = ''; newTypeInput.required = false; }
                    }
                }
                function renderTypeOpts(opts) {
                    if (opts.length === 0) {
                        typeOptionsEl.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No types found</div>';
                        return;
                    }
                    typeOptionsEl.innerHTML = opts.map(function(o) {
                        var name = (o.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 intake-type-opt" data-id="' + (o.id || '').replace(/"/g, '&quot;') + '" data-name="' + name + '"><div class="text-sm text-foreground">' + (o.name || '') + '</div></div>';
                    }).join('');
                    typeOptionsEl.querySelectorAll('.intake-type-opt').forEach(function(opt) {
                        opt.addEventListener('click', function() {
                            var id = this.dataset.id || '';
                            var name = this.dataset.name || 'Search vehicle type...';
                            typeDropdown.classList.add('hidden');
                            typeSearch.value = '';
                            typeFiltered = typeOptionsWithNew.slice();
                            renderTypeOpts(typeFiltered);
                            if (id === '__new__') {
                                typeHidden.value = (newTypeInput && newTypeInput.value.trim()) ? newTypeInput.value.trim() : '';
                                typeText.textContent = typeHidden.value || 'Add new vehicle type';
                                showNewTypeFields(true);
                            } else {
                                typeHidden.value = id;
                                typeText.textContent = name;
                                showNewTypeFields(false);
                            }
                            updateModelPreview();
                        });
                    });
                }
                function openTypeDropdown() {
                    typeDropdown.classList.remove('hidden');
                    var rect = typeTrigger.getBoundingClientRect();
                    typeDropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 4) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 260) + 'px;';
                    setTimeout(function() { typeSearch.focus(); }, 100);
                }
                function closeTypeDropdown() {
                    typeDropdown.classList.add('hidden');
                    typeDropdown.style.cssText = '';
                }
                renderTypeOpts(typeFiltered);
                typeSearch.addEventListener('input', function() {
                    var term = this.value.toLowerCase();
                    typeFiltered = typeOptionsWithNew.filter(function(o) { return (o.name || '').toLowerCase().includes(term); });
                    renderTypeOpts(typeFiltered);
                });
                typeTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (typeDropdown.classList.contains('hidden')) openTypeDropdown(); else closeTypeDropdown();
                });
                document.addEventListener('click', function(e) {
                    if (!typeTrigger.contains(e.target) && !typeDropdown.contains(e.target)) closeTypeDropdown();
                });
                if (newTypeInput) {
                    newTypeInput.addEventListener('input', function() {
                        typeHidden.value = this.value.trim();
                        typeText.textContent = this.value.trim() || 'Add new vehicle type';
                        updateModelPreview();
                    });
                    newTypeInput.addEventListener('change', function() {
                        typeHidden.value = this.value.trim();
                        typeText.textContent = this.value.trim() || 'Add new vehicle type';
                    });
                }
                // On load: if current value is custom (not in config), show new type fields
                var configIds = (vehicleTypes || []).map(function(t) { return t.id; });
                var isCustomType = typeHidden.value && typeHidden.value !== '__new__' && configIds.indexOf(typeHidden.value) === -1;
                if (isCustomType && newTypeFields && newTypeInput) {
                    newTypeInput.value = typeHidden.value;
                    newTypeFields.style.display = 'block';
                    newTypeInput.required = true;
                }
            }

            document.getElementById('make')?.addEventListener('input', updateModelPreview);
            document.getElementById('year_of_manufacture')?.addEventListener('input', updateModelPreview);

            document.addEventListener('DOMContentLoaded', function() {
                toggleModelFields();
            });
        })();
    </script>
    @endpush
@endsection
