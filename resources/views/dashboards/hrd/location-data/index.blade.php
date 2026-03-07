@extends('layouts.app')

@section('title', 'Location Data')
@section('page-title', 'Zones, States & LGAs')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Location Data</span>
@endsection

@php
    $selectedZoneId = request('zone_id', session('selected_zone_id', $zones->first()?->id));
    $selectedStateId = request('state_id', session('selected_state_id'));
    $selectedZone = $zones->firstWhere('id', $selectedZoneId);
    $statesInZone = $selectedZone ? $selectedZone->states : collect();
    $selectedState = $statesInZone->firstWhere('id', $selectedStateId) ?? $statesInZone->first();
    $selectedStateId = $selectedState?->id;
    $lgasInState = $selectedState ? $selectedState->lgas : collect();
    $totalZones = $zones->count();
    $totalStates = $zones->sum(fn($z) => $z->states->count());
    $totalLgas = $zones->sum(fn($z) => $z->states->sum(fn($s) => $s->lgas->count()));
@endphp

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('success'))
            <div class="kt-card bg-success/10 border border-success/20">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-check-circle text-success text-xl"></i>
                        <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="kt-card bg-danger/10 border border-danger/20">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-danger text-xl"></i>
                        <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <p class="text-sm text-secondary-foreground">
            Manage geopolitical zones, states, and LGAs in one place. Select a zone to see its states; select a state to see its LGAs.
        </p>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="kt-card">
                <div class="kt-card-content p-4 flex items-center gap-4">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                        <i class="ki-filled ki-map text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-foreground">{{ $totalZones }}</p>
                        <p class="text-sm text-secondary-foreground">Geopolitical Zones</p>
                    </div>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-4 flex items-center gap-4">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                        <i class="ki-filled ki-geolocation text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-foreground">{{ $totalStates }}</p>
                        <p class="text-sm text-secondary-foreground">States</p>
                    </div>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-4 flex items-center gap-4">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                        <i class="ki-filled ki-home-2 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-foreground">{{ $totalLgas }}</p>
                        <p class="text-sm text-secondary-foreground">LGAs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5 lg:items-start">
        <!-- Geopolitical Zones -->
        <div class="kt-card flex flex-col lg:max-h-[calc(100vh-14rem)] min-h-0">
            <div class="kt-card-header flex-shrink-0 flex flex-wrap items-center justify-between gap-3 min-h-[3.25rem] px-4 py-3 sm:px-5 sm:py-4">
                <h3 class="kt-card-title flex-shrink-0 m-0">Geopolitical Zones</h3>
                <div class="kt-card-toolbar flex items-center gap-2">
                    <button type="button" onclick="toggleZoneForm()" class="kt-btn kt-btn-sm kt-btn-primary" id="btn-add-zone">
                        <i class="ki-filled ki-plus"></i> Add Zone
                    </button>
                </div>
            </div>
            <div class="kt-card-content flex-1 min-h-0 overflow-auto p-4 sm:p-5">
                <div id="zone-form-wrap" class="hidden mb-4 p-4 bg-muted/30 rounded-lg border border-border">
                    <form method="POST" action="{{ route('hrd.location-data.zones.store') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="min-w-[200px]">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="kt-input w-full" placeholder="e.g. North Central" required>
                            @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="w-24">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="kt-input w-full" min="0">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Save</button>
                            <button type="button" onclick="toggleZoneForm()" class="kt-btn kt-btn-sm kt-btn-ghost">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">States</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zones as $zone)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">{{ $zone->name }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $zone->states->count() }}</td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $zone->is_active ? 'success' : 'danger' }} kt-badge-sm">{{ $zone->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" onclick="editZone({{ $zone->id }}, '{{ addslashes($zone->name) }}', {{ $zone->sort_order }}, {{ $zone->is_active ? '1' : '0' }})" class="kt-btn kt-btn-sm kt-btn-ghost" title="Edit"><i class="ki-filled ki-pencil"></i></button>
                                            <form method="POST" action="{{ route('hrd.location-data.zones.destroy', $zone) }}" class="inline" onsubmit="return confirm('Delete this zone? States under it must be removed first.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-danger" title="Delete"><i class="ki-filled ki-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-secondary-foreground">No zones yet. Add one above.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- States (for selected zone) -->
        <div class="kt-card flex flex-col lg:max-h-[calc(100vh-14rem)] min-h-0">
            <div class="kt-card-header flex-shrink-0 flex flex-wrap items-center justify-between gap-3 min-h-[3.25rem] px-4 py-3 sm:px-5 sm:py-4">
                <h3 class="kt-card-title flex-shrink-0 m-0">States{{ $selectedZone ? ' — ' . e($selectedZone->name) : '' }}</h3>
                <div class="kt-card-toolbar flex items-center gap-2 flex-shrink-0">
                    <form method="GET" action="{{ route('hrd.location-data.index') }}" id="form-select-zone" class="flex items-center gap-2">
                        <label class="text-sm text-secondary-foreground whitespace-nowrap">Zone:</label>
                        <select name="zone_id" id="select-zone" class="kt-input px-3 py-2 h-9 min-w-[140px] text-sm" onchange="this.form.submit()">
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}" {{ (int)$selectedZoneId === (int)$z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                            @endforeach
                        </select>
                        @if($selectedStateId)<input type="hidden" name="state_id" value="{{ $selectedStateId }}">@endif
                    </form>
                    <button type="button" onclick="toggleStateForm()" class="kt-btn kt-btn-sm kt-btn-primary" id="btn-add-state" {{ !$selectedZoneId ? 'disabled' : '' }}>
                        <i class="ki-filled ki-plus"></i> Add State
                    </button>
                </div>
            </div>
            <div class="kt-card-content flex-1 min-h-0 overflow-auto p-4 sm:p-5">
                <div id="state-form-wrap" class="hidden mb-4 p-4 bg-muted/30 rounded-lg border border-border">
                    <form method="POST" action="{{ route('hrd.location-data.states.store') }}">
                        @csrf
                        <input type="hidden" name="geopolitical_zone_id" value="{{ $selectedZoneId }}">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-[200px]">
                                <label class="block text-sm font-medium text-secondary-foreground mb-1">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="kt-input w-full" placeholder="e.g. FCT" required>
                                @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="w-24">
                                <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="kt-input w-full" min="0">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Save</button>
                                <button type="button" onclick="toggleStateForm()" class="kt-btn kt-btn-sm kt-btn-ghost">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">LGAs</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statesInZone as $state)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-foreground">{{ $state->name }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $state->lgas->count() }}</td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $state->is_active ? 'success' : 'danger' }} kt-badge-sm">{{ $state->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" onclick="editState({{ $state->id }}, '{{ addslashes($state->name) }}', {{ $state->geopolitical_zone_id }}, {{ $state->sort_order }}, {{ $state->is_active ? '1' : '0' }})" class="kt-btn kt-btn-sm kt-btn-ghost" title="Edit"><i class="ki-filled ki-pencil"></i></button>
                                            <form method="POST" action="{{ route('hrd.location-data.states.destroy', $state) }}" class="inline" onsubmit="return confirm('Delete this state? LGAs under it must be removed first.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-danger" title="Delete"><i class="ki-filled ki-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-secondary-foreground">
                                        @if($selectedZoneId) No states in this zone. Add one above. @else Select a zone first. @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LGAs (for selected state) -->
        <div class="kt-card flex flex-col lg:max-h-[calc(100vh-14rem)] min-h-0">
            <div class="kt-card-header flex-shrink-0 flex flex-wrap items-center justify-between gap-3 min-h-[3.25rem] px-4 py-3 sm:px-5 sm:py-4">
                <h3 class="kt-card-title flex-shrink-0 m-0">LGAs{{ $selectedState ? ' — ' . e($selectedState->name) : '' }}</h3>
                <div class="kt-card-toolbar flex items-center gap-2 flex-shrink-0">
                    <form method="GET" action="{{ route('hrd.location-data.index') }}" id="form-select-state" class="flex items-center gap-2">
                        <input type="hidden" name="zone_id" value="{{ $selectedZoneId }}">
                        <label class="text-sm text-secondary-foreground whitespace-nowrap">State:</label>
                        <select name="state_id" id="select-state" class="kt-input px-3 py-2 h-9 min-w-[140px] text-sm" onchange="document.getElementById('form-select-state').submit()">
                            @foreach($statesInZone as $s)
                                <option value="{{ $s->id }}" {{ (int)$selectedStateId === (int)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline">View LGAs</button>
                    </form>
                    <button type="button" onclick="toggleLgaForm()" class="kt-btn kt-btn-sm kt-btn-primary" id="btn-add-lga" {{ !$selectedStateId ? 'disabled' : '' }}>
                        <i class="ki-filled ki-plus"></i> Add LGA
                    </button>
                </div>
            </div>
            <div class="kt-card-content flex-1 min-h-0 overflow-auto p-4 sm:p-5">
                <div id="lga-form-wrap" class="hidden mb-4 p-4 bg-muted/30 rounded-lg border border-border">
                    <form method="POST" action="{{ route('hrd.location-data.lgas.store') }}">
                        @csrf
                        <input type="hidden" name="state_id" value="{{ $selectedStateId }}">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-[200px]">
                                <label class="block text-sm font-medium text-secondary-foreground mb-1">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="kt-input w-full" placeholder="e.g. Abuja Municipal" required>
                                @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="w-24">
                                <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="kt-input w-full" min="0">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Save</button>
                                <button type="button" onclick="toggleLgaForm()" class="kt-btn kt-btn-sm kt-btn-ghost">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lgasInState as $lga)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-foreground">{{ $lga->name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $lga->is_active ? 'success' : 'danger' }} kt-badge-sm">{{ $lga->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" onclick="editLga({{ $lga->id }}, '{{ addslashes($lga->name) }}', {{ $lga->state_id }}, {{ $lga->sort_order }}, {{ $lga->is_active ? '1' : '0' }})" class="kt-btn kt-btn-sm kt-btn-ghost" title="Edit"><i class="ki-filled ki-pencil"></i></button>
                                            <form method="POST" action="{{ route('hrd.location-data.lgas.destroy', $lga) }}" class="inline" onsubmit="return confirm('Delete this LGA?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-danger" title="Delete"><i class="ki-filled ki-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-secondary-foreground">
                                        @if($selectedStateId) No LGAs in this state. Add one above. @else Select a state first. @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        </div>
    </div>

    <!-- Zone edit modal -->
    <div id="modal-zone" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="kt-card max-w-md w-full">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Edit Zone</h3>
                <button type="button" onclick="closeModal('modal-zone')" class="kt-btn kt-btn-sm kt-btn-ghost"><i class="ki-filled ki-cross"></i></button>
            </div>
            <form method="POST" id="form-edit-zone" class="kt-card-content">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-zone-name" class="kt-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                        <input type="number" name="sort_order" id="edit-zone-sort" class="kt-input w-full" min="0">
                    </div>
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="edit-zone-active" value="1" class="rounded border-input">
                            <span class="text-sm text-secondary-foreground">Active</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" onclick="closeModal('modal-zone')" class="kt-btn kt-btn-ghost">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- State edit modal -->
    <div id="modal-state" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="kt-card max-w-md w-full">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Edit State</h3>
                <button type="button" onclick="closeModal('modal-state')" class="kt-btn kt-btn-sm kt-btn-ghost"><i class="ki-filled ki-cross"></i></button>
            </div>
            <form method="POST" id="form-edit-state" class="kt-card-content">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                        <select name="geopolitical_zone_id" id="edit-state-zone" class="kt-input w-full">
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-state-name" class="kt-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                        <input type="number" name="sort_order" id="edit-state-sort" class="kt-input w-full" min="0">
                    </div>
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="edit-state-active" value="1" class="rounded border-input">
                            <span class="text-sm text-secondary-foreground">Active</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" onclick="closeModal('modal-state')" class="kt-btn kt-btn-ghost">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LGA edit modal -->
    <div id="modal-lga" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="kt-card max-w-md w-full">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Edit LGA</h3>
                <button type="button" onclick="closeModal('modal-lga')" class="kt-btn kt-btn-sm kt-btn-ghost"><i class="ki-filled ki-cross"></i></button>
            </div>
            <form method="POST" id="form-edit-lga" class="kt-card-content">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">State</label>
                        <select name="state_id" id="edit-lga-state" class="kt-input w-full">
                            @foreach($zones as $z)
                                @foreach($z->states as $s)
                                    <option value="{{ $s->id }}" data-zone="{{ $z->id }}">{{ $z->name }} → {{ $s->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-lga-name" class="kt-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                        <input type="number" name="sort_order" id="edit-lga-sort" class="kt-input w-full" min="0">
                    </div>
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="edit-lga-active" value="1" class="rounded border-input">
                            <span class="text-sm text-secondary-foreground">Active</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" onclick="closeModal('modal-lga')" class="kt-btn kt-btn-ghost">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleZoneForm() {
            document.getElementById('zone-form-wrap').classList.toggle('hidden');
        }
        function toggleStateForm() {
            document.getElementById('state-form-wrap').classList.toggle('hidden');
        }
        function toggleLgaForm() {
            document.getElementById('lga-form-wrap').classList.toggle('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.getElementById(id).classList.remove('flex');
        }
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.getElementById(id).classList.add('flex');
        }

        function editZone(id, name, sortOrder, isActive) {
            document.getElementById('form-edit-zone').action = '{{ url("hrd/location-data/zones") }}/' + id;
            document.getElementById('edit-zone-name').value = name;
            document.getElementById('edit-zone-sort').value = sortOrder;
            document.getElementById('edit-zone-active').checked = !!isActive;
            openModal('modal-zone');
        }

        function editState(id, name, zoneId, sortOrder, isActive) {
            document.getElementById('form-edit-state').action = '{{ url("hrd/location-data/states") }}/' + id;
            document.getElementById('edit-state-name').value = name;
            document.getElementById('edit-state-zone').value = zoneId;
            document.getElementById('edit-state-sort').value = sortOrder;
            document.getElementById('edit-state-active').checked = !!isActive;
            openModal('modal-state');
        }

        function editLga(id, name, stateId, sortOrder, isActive) {
            document.getElementById('form-edit-lga').action = '{{ url("hrd/location-data/lgas") }}/' + id;
            document.getElementById('edit-lga-name').value = name;
            document.getElementById('edit-lga-state').value = stateId;
            document.getElementById('edit-lga-sort').value = sortOrder;
            document.getElementById('edit-lga-active').checked = !!isActive;
            openModal('modal-lga');
        }

    </script>
@endsection
