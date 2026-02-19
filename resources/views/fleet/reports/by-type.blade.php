@extends('layouts.app')

@section('title', 'Fleet Report by Vehicle Type')
@section('page-title', 'Fleet Report by Vehicle Type')

@section('breadcrumbs')
    @php
        $user = auth()->user();
        $dashboardRoute = null;
        if ($user->hasRole('CD')) {
            $dashboardRoute = route('fleet.cd.dashboard');
        } elseif ($user->hasRole('CC T&L')) {
            $dashboardRoute = route('fleet.cc-tl.dashboard');
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
    <span class="text-primary">Report by Type</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card no-print">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Select Vehicles</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                <form method="GET" action="{{ route('fleet.reports.by-type') }}" id="by-type-report-form" class="grid gap-4 max-w-3xl">
                    @if(!empty($showCommandScope) && $commands->isNotEmpty())
                    <div class="min-w-[220px]">
                        <label class="text-sm font-medium" for="command_scope_select_trigger">Command scope</label>
                        <input type="hidden" name="command_id" id="command_scope" value="{{ request('command_id') }}">
                        <div class="relative">
                            <button type="button" id="command_scope_select_trigger" class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                @php
                                    $selCommandId = request('command_id');
                                    $commandScopeText = $selCommandId ? ($commands->firstWhere('id', (int)$selCommandId)?->name ?? 'All Commands') : 'All Commands';
                                @endphp
                                <span id="command_scope_select_text">{{ $commandScopeText }}</span>
                                <i class="ki-filled ki-down text-muted-foreground"></i>
                            </button>
                            <div id="command_scope_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-input rounded-lg shadow-lg hidden">
                                <div class="p-2 border-b border-input">
                                    <input type="text" id="command_scope_search_input" class="kt-input w-full" placeholder="Search command..." autocomplete="off">
                                </div>
                                <div id="command_scope_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium">Vehicles <span class="text-red-600">*</span></label>
                        <p class="text-xs text-secondary-foreground mb-1">Select specific vehicles (report will be sectioned by vehicle type when printed).</p>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <input type="text" id="vehicles_filter" class="kt-input flex-1 min-w-[180px]" placeholder="Filter vehicles..." autocomplete="off">
                            <div class="flex gap-2">
                                <button type="button" id="vehicles_select_all" class="kt-btn kt-btn-sm kt-btn-secondary">Select All</button>
                                <button type="button" id="vehicles_deselect_all" class="kt-btn kt-btn-sm kt-btn-secondary">Deselect All</button>
                            </div>
                        </div>
                        <div class="border border-input rounded-lg p-3 max-h-60 overflow-y-auto bg-muted/20" id="vehicles_checkbox_list">
                            @php $avail = $availableVehicles ?? collect(); @endphp
                            @forelse($avail as $v)
                                @php
                                    $vLabel = ($v->reg_no ?? $v->chassis_number) . ' — ' . trim(($v->make ?? '') . ' ' . ($v->model ?? '')) . ' (' . (($vehicleTypes[$v->vehicle_type] ?? $v->vehicle_type)) . ')';
                                @endphp
                                <label class="flex items-center gap-2 py-1.5 px-2 hover:bg-muted/50 rounded cursor-pointer vehicle-checkbox-row" data-label="{{ strtolower($vLabel) }}">
                                    <input type="checkbox" name="vehicle_ids[]" value="{{ $v->id }}" class="rounded border-input"
                                        @checked(in_array($v->id, $selectedVehicleIds ?? []))>
                                    <span class="text-sm">{{ $vLabel }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-secondary-foreground py-2">No vehicles in this scope. Change command scope or add vehicles.</p>
                            @endforelse
                        </div>
                        @error('vehicle_ids')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="kt-btn kt-btn-primary w-fit">Generate Report</button>
                </form>
            </div>
        </div>

        @if($vehiclesGroupedByType !== null && $vehiclesGroupedByType->isNotEmpty())
            <div class="kt-card" id="report-card">
                <div class="kt-card-header flex flex-wrap items-center justify-between gap-2">
                    <h3 class="kt-card-title">Report – {{ $scopeLabel }} ({{ $vehiclesGroupedByType->flatten(1)->count() }} vehicle(s))</h3>
                    <a href="{{ route('fleet.reports.by-type', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" rel="noopener noreferrer" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-printer"></i>
                        Print
                    </a>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    @php $showCommandColumn = auth()->user()->hasRole('CC T&L') || auth()->user()->hasRole('CGC'); @endphp
                    @foreach($vehiclesGroupedByType as $typeKey => $vehiclesInType)
                        @php
                            $typeLabel = $vehicleTypes[$typeKey] ?? $typeKey;
                            $sn = 0;
                        @endphp
                        <div class="mb-6 report-section" data-vehicle-type="{{ $typeKey }}">
                            <h4 class="text-sm font-semibold mb-2 border-b pb-1">{{ $typeLabel }} ({{ $vehiclesInType->count() }})</h4>
                            <div class="overflow-x-auto">
                                <table class="kt-table w-full by-type-section-table">
                                    <thead>
                                        <tr>
                                            <th class="text-left">S/N</th>
                                            <th class="text-left">Reg No.</th>
                                            <th class="text-left">Make/Model</th>
                                            <th class="text-left">Chassis No.</th>
                                            <th class="text-left">Engine No.</th>
                                            @if($showCommandColumn)
                                                <th class="text-left">Command</th>
                                            @endif
                                            <th class="text-left">Officer (Allocated To)</th>
                                            <th class="text-left">Service No.</th>
                                            <th class="text-left">Service Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vehiclesInType as $v)
                                            @php $sn++; @endphp
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td>{{ $v->reg_no ?? '-' }}</td>
                                                <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '-' }}</td>
                                                <td>{{ $v->chassis_number ?? '-' }}</td>
                                                <td>{{ $v->engine_number ?? '-' }}</td>
                                                @if($showCommandColumn)
                                                    <td>{{ $v->currentCommand?->name ?? '—' }}</td>
                                                @endif
                                                <td>
                                                    @if($v->currentOfficer)
                                                        {{ $v->currentOfficer->full_name ?? ($v->currentOfficer->surname . ' ' . ($v->currentOfficer->first_name ?? '') . ' ' . ($v->currentOfficer->middle_name ?? '')) }}
                                                    @else
                                                        <span class="text-secondary-foreground">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($v->currentOfficer)
                                                        {{ $v->currentOfficer->service_number ?? '—' }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="px-2 py-0.5 rounded text-xs {{ $v->service_status === 'SERVICEABLE' ? 'bg-green-100 text-green-800' : ($v->service_status === 'UNSERVICEABLE' ? 'bg-red-100 text-red-800' : 'bg-muted') }}">
                                                        {{ $v->service_status ?? '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                    <p class="mt-4 text-sm text-secondary-foreground"><strong>Total:</strong> {{ $vehiclesGroupedByType->flatten(1)->count() }} vehicle(s)</p>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        (function() {
            var commandOptions = @json($commandOptionsForJs ?? []);

            // Vehicles checkbox list filter
            var vFilter = document.getElementById('vehicles_filter');
            var vList = document.getElementById('vehicles_checkbox_list');
            if (vFilter && vList) {
                vFilter.addEventListener('input', function() {
                    var term = (this.value || '').toLowerCase();
                    vList.querySelectorAll('.vehicle-checkbox-row').forEach(function(row) {
                        var label = row.getAttribute('data-label') || '';
                        row.style.display = term === '' || label.indexOf(term) !== -1 ? '' : 'none';
                    });
                });
            }

            // Select All / Deselect All for vehicles
            var selectAllBtn = document.getElementById('vehicles_select_all');
            var deselectAllBtn = document.getElementById('vehicles_deselect_all');
            if (selectAllBtn && vList) {
                selectAllBtn.addEventListener('click', function() {
                    vList.querySelectorAll('.vehicle-checkbox-row input[type="checkbox"]').forEach(function(cb) { cb.checked = true; });
                });
            }
            if (deselectAllBtn && vList) {
                deselectAllBtn.addEventListener('click', function() {
                    vList.querySelectorAll('.vehicle-checkbox-row input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
                });
            }

            // Command scope searchable select (CC T&L / CGC only)
            var cmdTrigger = document.getElementById('command_scope_select_trigger');
            var cmdHidden = document.getElementById('command_scope');
            var cmdDropdown = document.getElementById('command_scope_dropdown');
            var cmdSearchInput = document.getElementById('command_scope_search_input');
            var cmdOptionsContainer = document.getElementById('command_scope_options');
            var cmdDisplayText = document.getElementById('command_scope_select_text');
            if (cmdTrigger && cmdHidden && cmdDropdown && cmdSearchInput && cmdOptionsContainer && cmdDisplayText && typeof commandOptions !== 'undefined') {
                var cmdFiltered = commandOptions.slice();
                function renderCmd(opts) {
                    if (opts.length === 0) {
                        cmdOptionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No commands found</div>';
                        return;
                    }
                    cmdOptionsContainer.innerHTML = opts.map(function(opt) {
                        var name = (opt.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 cmd-select-option" data-id="' + (opt.id || '').replace(/"/g, '&quot;') + '" data-name="' + name + '"><div class="text-sm text-foreground">' + (opt.name || '') + '</div></div>';
                    }).join('');
                    cmdOptionsContainer.querySelectorAll('.cmd-select-option').forEach(function(opt) {
                        opt.addEventListener('click', function() {
                            cmdHidden.value = this.dataset.id || '';
                            cmdDisplayText.textContent = this.dataset.name || 'All Commands';
                            cmdDropdown.classList.add('hidden');
                            cmdSearchInput.value = '';
                            cmdFiltered = commandOptions.slice();
                            renderCmd(cmdFiltered);
                        });
                    });
                }
                function openCmdDropdown() {
                    cmdDropdown.classList.remove('hidden');
                    var rect = cmdTrigger.getBoundingClientRect();
                    cmdDropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (rect.bottom + 4) + 'px;left:' + rect.left + 'px;width:' + Math.max(rect.width, 260) + 'px;';
                    setTimeout(function() { cmdSearchInput.focus(); }, 100);
                }
                function closeCmdDropdown() {
                    cmdDropdown.classList.add('hidden');
                    cmdDropdown.style.cssText = '';
                }
                renderCmd(cmdFiltered);
                cmdSearchInput.addEventListener('input', function() {
                    var term = this.value.toLowerCase();
                    cmdFiltered = commandOptions.filter(function(o) { return (o.name || '').toLowerCase().includes(term); });
                    renderCmd(cmdFiltered);
                });
                cmdTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (cmdDropdown.classList.contains('hidden')) openCmdDropdown(); else closeCmdDropdown();
                });
                document.addEventListener('click', function(e) {
                    setTimeout(function() {
                        if (!cmdTrigger.contains(e.target) && !cmdDropdown.contains(e.target)) closeCmdDropdown();
                    }, 0);
                });
            }
        })();
    </script>
    @endpush

    @push('styles')
    @include('prints._watermark')
    <style media="print">
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body * { visibility: hidden; }
            #report-card, #report-card * { visibility: visible; }
            #report-card { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
    @endpush

@endsection
