@extends('layouts.app')

@section('title', 'Issue Vehicle')
@section('page-title', 'Issue Vehicle')

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
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.show', $vehicle) }}">View</a>
    <span>/</span>
    <span class="text-primary">Issue to Officer</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Issue Vehicle to Officer</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="text-sm text-secondary-foreground mb-4">
                <strong>Vehicle:</strong>
                {{ $vehicle->vehicle_type }} — {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: '-' }}
                (Chassis: {{ $vehicle->chassis_number }})
            </div>

            <form method="POST" action="{{ route('fleet.vehicles.issue.store', $vehicle) }}" class="grid gap-4 max-w-2xl">
                @csrf

                <div>
                    <label class="text-sm font-medium" for="officer_id_trigger">Officer <span class="text-red-600">*</span></label>
                    <input type="hidden" name="officer_id" id="officer_id" value="{{ old('officer_id') }}" required>
                    <div class="relative">
                        <button type="button" id="officer_id_trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                            @php
                                $selOfficer = $officers->firstWhere('id', old('officer_id'));
                                $officerLabel = $selOfficer ? $selOfficer->full_name . ' (' . ($selOfficer->service_number ?? '') . ')' : 'Search officer...';
                            @endphp
                            <span id="officer_id_text">{{ $officerLabel }}</span>
                            <i class="ki-filled ki-down text-muted-foreground"></i>
                        </button>
                        <div id="officer_id_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                            <div class="p-2 border-b border-input">
                                <input type="text" id="officer_id_search" class="kt-input w-full" placeholder="Search officer..." autocomplete="off">
                            </div>
                            <div id="officer_id_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                    @error('officer_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Notes (optional)</label>
                    <textarea class="kt-textarea w-full" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button class="kt-btn kt-btn-primary" type="submit">Issue Vehicle</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.show', $vehicle) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            var officers = @json($officersForJs ?? []);
            var trigger = document.getElementById('officer_id_trigger');
            var hidden = document.getElementById('officer_id');
            var dropdown = document.getElementById('officer_id_dropdown');
            var searchInput = document.getElementById('officer_id_search');
            var optionsEl = document.getElementById('officer_id_options');
            var displayText = document.getElementById('officer_id_text');
            if (!trigger || !hidden || !dropdown || !searchInput || !optionsEl || !displayText) return;
            var filtered = officers.slice();
            function render(opts) {
                if (opts.length === 0) {
                    optionsEl.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                    return;
                }
                optionsEl.innerHTML = opts.map(function(o) {
                    var label = (o.label || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                    return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 issue-officer-opt" data-id="' + (o.id || '') + '" data-label="' + label + '"><div class="text-sm text-foreground">' + (o.label || '') + '</div></div>';
                }).join('');
                optionsEl.querySelectorAll('.issue-officer-opt').forEach(function(opt) {
                    opt.addEventListener('click', function() {
                        hidden.value = this.dataset.id || '';
                        displayText.textContent = this.dataset.label || 'Search officer...';
                        dropdown.classList.add('hidden');
                        searchInput.value = '';
                        filtered = officers.slice();
                        render(filtered);
                    });
                });
            }
            function openDropdown() {
                dropdown.classList.remove('hidden');
                var rect = trigger.getBoundingClientRect();
                dropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 4) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 260) + 'px;';
                setTimeout(function() { searchInput.focus(); }, 100);
            }
            function closeDropdown() {
                dropdown.classList.add('hidden');
                dropdown.style.cssText = '';
            }
            render(filtered);
            searchInput.addEventListener('input', function() {
                var term = this.value.toLowerCase();
                filtered = officers.filter(function(o) { return (o.label || '').toLowerCase().includes(term); });
                render(filtered);
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
@endsection

