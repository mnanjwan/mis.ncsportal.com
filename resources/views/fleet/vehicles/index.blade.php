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
                    <div class="w-40">
                        <label for="fleet-type" class="mb-1 block text-xs font-medium text-secondary-foreground">Type</label>
                        <select id="fleet-type" class="kt-input w-full">
                            <option value="">All</option>
                            @foreach($vehicleTypes as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-44">
                        <label for="fleet-status" class="mb-1 block text-xs font-medium text-secondary-foreground">Status</label>
                        <select id="fleet-status" class="kt-input w-full">
                            <option value="">All</option>
                            @foreach($serviceStatuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($filterCommands->isNotEmpty())
                        <div class="w-52">
                            <label for="fleet-command" class="mb-1 block text-xs font-medium text-secondary-foreground">Command</label>
                            <select id="fleet-command" class="kt-input w-full">
                                <option value="">All</option>
                                @foreach($filterCommands as $cmd)
                                    <option value="{{ $cmd }}">{{ $cmd }}</option>
                                @endforeach
                            </select>
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

                    searchEl?.addEventListener('input', onFilterChange);
                    searchEl?.addEventListener('keyup', onFilterChange);
                    typeEl?.addEventListener('change', onFilterChange);
                    statusEl?.addEventListener('change', onFilterChange);
                    if (commandEl) commandEl.addEventListener('change', onFilterChange);

                    resetEl?.addEventListener('click', function () {
                        if (searchEl) searchEl.value = '';
                        if (typeEl) typeEl.value = '';
                        if (statusEl) statusEl.value = '';
                        if (commandEl) commandEl.value = '';
                        onFilterChange();
                    });

                    applyFilters();
                })();
            </script>
        @endpush
    @endif
@endsection

