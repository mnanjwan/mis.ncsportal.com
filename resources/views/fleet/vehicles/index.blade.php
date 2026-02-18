@extends('layouts.app')

@section('title', 'Fleet Vehicles')
@section('page-title', 'Fleet Vehicles')

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
        } elseif ($user->hasRole('Area Controller')) {
            $dashboardRoute = route('area-controller.dashboard');
        } elseif ($user->hasRole('CGC')) {
            $dashboardRoute = route('cgc.dashboard');
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">Fleet</a>
        <span>/</span>
    @endif
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.index') }}">Vehicles</a>
    <span>/</span>
    <span class="text-primary">List</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="kt-card-title">Vehicles</h3>
                @if(auth()->user()->hasRole('Transport Store/Receiver'))
                    <a class="kt-btn kt-btn-primary" href="{{ route('fleet.vehicles.intake.create') }}">
                        <i class="ki-filled ki-plus"></i>
                        Receive Vehicle
                    </a>
                @endif
            </div>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            @if($vehicles->isEmpty())
                <p class="text-sm text-secondary-foreground">No vehicles found.</p>
            @else
                @php
                    $vehicleTypes = $vehicles->pluck('vehicle_type')->unique()->sort()->values();
                    $serviceStatuses = $vehicles->pluck('service_status')->unique()->sort()->values();
                    $filterCommands = auth()->user()->hasRole('CGC')
                        ? $vehicles->map(fn ($v) => $v->currentCommand?->name)->filter()->unique()->sort()->values()
                        : collect();
                @endphp

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-border bg-muted/30 p-3">
                    <div class="min-w-[180px] flex-1">
                        <label for="fleet-search" class="mb-1 block text-xs font-medium text-secondary-foreground">Search</label>
                        <input type="text" id="fleet-search" placeholder="Reg no, make, chassis, officer…"
                            class="kt-input w-full"
                            autocomplete="off">
                    </div>
                    <div class="w-44">
                        <label for="fleet-type-trigger" class="mb-1 block text-xs font-medium text-secondary-foreground">Type</label>
                        <input type="hidden" id="fleet-type" value="">
                        <div class="relative">
                            <button type="button" id="fleet-type-trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="fleet-type-text">All</span>
                                <i class="ki-filled ki-down text-muted-foreground text-xs"></i>
                            </button>
                            <div id="fleet-type-dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                <input type="text" id="fleet-type-search" class="kt-input m-2 w-[calc(100%-1rem)]" placeholder="Search type..." autocomplete="off">
                                <div id="fleet-type-options" class="max-h-48 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="w-48">
                        <label for="fleet-status-trigger" class="mb-1 block text-xs font-medium text-secondary-foreground">Status</label>
                        <input type="hidden" id="fleet-status" value="">
                        <div class="relative">
                            <button type="button" id="fleet-status-trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="fleet-status-text">All</span>
                                <i class="ki-filled ki-down text-muted-foreground text-xs"></i>
                            </button>
                            <div id="fleet-status-dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                <input type="text" id="fleet-status-search" class="kt-input m-2 w-[calc(100%-1rem)]" placeholder="Search status..." autocomplete="off">
                                <div id="fleet-status-options" class="max-h-48 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    @if($filterCommands->isNotEmpty())
                        <div class="w-56">
                            <label for="fleet-command-trigger" class="mb-1 block text-xs font-medium text-secondary-foreground">Command</label>
                            <input type="hidden" id="fleet-command" value="">
                            <div class="relative">
                                <button type="button" id="fleet-command-trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="fleet-command-text">All</span>
                                    <i class="ki-filled ki-down text-muted-foreground text-xs"></i>
                                </button>
                                <div id="fleet-command-dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                    <input type="text" id="fleet-command-search" class="kt-input m-2 w-[calc(100%-1rem)]" placeholder="Search command..." autocomplete="off">
                                    <div id="fleet-command-options" class="max-h-48 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <button type="button" id="fleet-filters-reset" class="kt-btn kt-btn-secondary whitespace-nowrap">Clear</button>
                </div>

                <p id="fleet-filter-summary" class="mb-2 text-sm text-secondary-foreground"></p>

                <div class="overflow-x-auto">
                    <table class="kt-table w-full" id="fleet-vehicles-table">
                        <thead>
                            <tr>
                                <th class="text-left">S/N</th>
                                <th class="text-left">Reg No</th>
                                <th class="text-left">Type</th>
                                <th class="text-left">Make/Model</th>
                                <th class="text-left">Chassis No</th>
                                <th class="text-left">Engine No</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Officer</th>
                                @if(auth()->user()->hasRole('CGC'))
                                    <th class="text-left">Command</th>
                                @endif
                                <th class="text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $i => $v)
                                @php
                                    $officerText = $v->currentOfficer ? $v->currentOfficer->service_number . ' ' . $v->currentOfficer->full_name : '';
                                    $searchText = strtolower(implode(' ', array_filter([
                                        $v->reg_no,
                                        $v->make,
                                        $v->model,
                                        $v->chassis_number,
                                        $v->engine_number,
                                        $officerText,
                                        $v->currentCommand?->name ?? '',
                                    ])));
                                @endphp
                                <tr class="fleet-row"
                                    data-type="{{ $v->vehicle_type }}"
                                    data-status="{{ $v->service_status }}"
                                    data-command="{{ $v->currentCommand?->name ?? '' }}"
                                    data-search="{{ $searchText }}">
                                    <td class="fleet-sn">{{ $i + 1 }}</td>
                                    <td>{{ $v->reg_no ?? '-' }}</td>
                                    <td>{{ $v->vehicle_type }}</td>
                                    <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '-' }}</td>
                                    <td>{{ $v->chassis_number }}</td>
                                    <td>{{ $v->engine_number ?? '-' }}</td>
                                    <td>{{ $v->service_status }}</td>
                                    <td>{{ $v->currentOfficer ? $v->currentOfficer->service_number . ' – ' . $v->currentOfficer->full_name : '—' }}</td>
                                    @if(auth()->user()->hasRole('CGC'))
                                        <td>{{ $v->currentCommand?->name ?? '—' }}</td>
                                    @endif
                                    <td>
                                        <a class="kt-btn kt-btn-sm" href="{{ route('fleet.vehicles.show', $v) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if(!$vehicles->isEmpty())
        @push('scripts')
            <script>
                (function () {
                    var fleetFilterTypes = @json($vehicleTypes->values());
                    var fleetFilterStatuses = @json($serviceStatuses->values());
                    var fleetFilterCommands = @json($filterCommands->values());

                    const table = document.getElementById('fleet-vehicles-table');
                    const rows = table ? Array.from(table.querySelectorAll('tbody tr.fleet-row')) : [];
                    const total = rows.length;
                    const searchEl = document.getElementById('fleet-search');
                    const typeEl = document.getElementById('fleet-type');
                    const statusEl = document.getElementById('fleet-status');
                    const commandEl = document.getElementById('fleet-command');
                    const resetEl = document.getElementById('fleet-filters-reset');
                    const summaryEl = document.getElementById('fleet-filter-summary');

                    function applyFilters() {
                        const search = (searchEl?.value || '').trim().toLowerCase();
                        const type = (typeEl?.value || '').trim();
                        const status = (statusEl?.value || '').trim();
                        const command = commandEl ? (commandEl.value || '').trim() : '';

                        let visible = 0;
                        rows.forEach((row) => {
                            const matchSearch = !search || (row.dataset.search || '').includes(search);
                            const matchType = !type || (row.dataset.type || '') === type;
                            const matchStatus = !status || (row.dataset.status || '') === status;
                            const matchCommand = !command || (row.dataset.command || '') === command;
                            const show = matchSearch && matchType && matchStatus && matchCommand;
                            row.style.display = show ? '' : 'none';
                            if (show) visible++;
                        });

                        let n = 0;
                        rows.forEach((row) => {
                            if (row.style.display !== 'none') {
                                n++;
                                const sn = row.querySelector('.fleet-sn');
                                if (sn) sn.textContent = n;
                            }
                        });

                        if (summaryEl) {
                            summaryEl.textContent = visible === total
                                ? 'Showing all ' + total + ' vehicles.'
                                : 'Showing ' + visible + ' of ' + total + ' vehicles.';
                        }
                    }

                    function onFilterChange() {
                        applyFilters();
                    }

                    function initSearchableFilter(name, options, triggerId, textId, hiddenId, dropdownId, searchId, optionsId) {
                        var trigger = document.getElementById(triggerId);
                        var textEl = document.getElementById(textId);
                        var hidden = document.getElementById(hiddenId);
                        var dropdown = document.getElementById(dropdownId);
                        var searchInput = document.getElementById(searchId);
                        var optionsContainer = document.getElementById(optionsId);
                        if (!trigger || !textEl || !hidden || !dropdown || !searchInput || !optionsContainer) return;
                        var allOptions = [{ id: '', name: 'All' }].concat(options.map(function(o) { return { id: o, name: o }; }));
                        var filtered = allOptions.slice();

                        function render(opts) {
                            optionsContainer.innerHTML = opts.map(function(opt) {
                                var n = (opt.name || '').replace(/</g, '&lt;').replace(/"/g, '&quot;');
                                return '<div class="p-2 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 text-sm" data-id="' + (opt.id || '').replace(/"/g, '&quot;') + '" data-name="' + n + '">' + (opt.name || '') + '</div>';
                            }).join('');
                            optionsContainer.querySelectorAll('[data-id]').forEach(function(opt) {
                                opt.addEventListener('click', function() {
                                    hidden.value = this.dataset.id || '';
                                    textEl.textContent = this.dataset.name || 'All';
                                    dropdown.classList.add('hidden');
                                    searchInput.value = '';
                                    filtered = allOptions.slice();
                                    render(filtered);
                                    onFilterChange();
                                });
                            });
                        }
                        function open() {
                            dropdown.classList.remove('hidden');
                            var rect = trigger.getBoundingClientRect();
                            dropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 2) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 160) + 'px;';
                            setTimeout(function() { searchInput.focus(); }, 50);
                        }
                        function close() {
                            dropdown.classList.add('hidden');
                            dropdown.style.cssText = '';
                        }
                        render(filtered);
                        searchInput.addEventListener('input', function() {
                            var term = (this.value || '').toLowerCase();
                            filtered = allOptions.filter(function(o) { return (o.name || '').toLowerCase().includes(term); });
                            render(filtered);
                        });
                        trigger.addEventListener('click', function(e) {
                            e.stopPropagation();
                            if (dropdown.classList.contains('hidden')) open(); else close();
                        });
                        document.addEventListener('click', function(e) {
                            setTimeout(function() {
                                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) close();
                            }, 0);
                        });
                    }

                    searchEl?.addEventListener('input', onFilterChange);
                    searchEl?.addEventListener('keyup', onFilterChange);

                    initSearchableFilter('Type', fleetFilterTypes, 'fleet-type-trigger', 'fleet-type-text', 'fleet-type', 'fleet-type-dropdown', 'fleet-type-search', 'fleet-type-options');
                    initSearchableFilter('Status', fleetFilterStatuses, 'fleet-status-trigger', 'fleet-status-text', 'fleet-status', 'fleet-status-dropdown', 'fleet-status-search', 'fleet-status-options');
                    if (document.getElementById('fleet-command-trigger')) {
                        initSearchableFilter('Command', fleetFilterCommands, 'fleet-command-trigger', 'fleet-command-text', 'fleet-command', 'fleet-command-dropdown', 'fleet-command-search', 'fleet-command-options');
                    }

                    resetEl?.addEventListener('click', function () {
                        if (searchEl) searchEl.value = '';
                        if (typeEl) typeEl.value = '';
                        if (statusEl) statusEl.value = '';
                        if (commandEl) commandEl.value = '';
                        var t = document.getElementById('fleet-type-text');
                        var s = document.getElementById('fleet-status-text');
                        var c = document.getElementById('fleet-command-text');
                        if (t) t.textContent = 'All';
                        if (s) s.textContent = 'All';
                        if (c) c.textContent = 'All';
                        onFilterChange();
                    });

                    applyFilters();
                })();
            </script>
        @endpush
    @endif
@endsection

