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
                <h3 class="kt-card-title">Select Vehicle Type</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                <form method="GET" action="{{ route('fleet.reports.by-type') }}" class="flex flex-wrap items-end gap-4">
                    <div class="min-w-[200px]">
                        <label class="text-sm font-medium" for="vehicle_type">Vehicle Type <span class="text-red-600">*</span></label>
                        <x-fleet-vehicle-type-select name="vehicle_type" id="vehicle_type" :selected="request('vehicle_type')" class="kt-input w-full" />
                    </div>
                    <button type="submit" class="kt-btn kt-btn-primary">Generate Report</button>
                </form>
            </div>
        </div>

        @if($vehicleType !== null)
            <div class="kt-card" id="report-card">
                <div class="kt-card-header flex flex-wrap items-center justify-between gap-2 no-print">
                    <h3 class="kt-card-title">Vehicles – {{ $vehicleTypeLabel }} ({{ $scopeLabel }})</h3>
                    <button type="button" onclick="window.print()" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-printer"></i>
                        Print
                    </button>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <div class="print-only mb-4" style="display: none;">
                        <h2 class="text-lg font-semibold">Fleet Report by Vehicle Type</h2>
                        <p class="text-sm text-secondary-foreground">Type: {{ $vehicleTypeLabel }} | Scope: {{ $scopeLabel }}</p>
                        <p class="text-sm text-secondary-foreground">Generated: {{ now()->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($vehicles->isEmpty())
                        <p class="text-sm text-secondary-foreground">No vehicles found for the selected type in this scope.</p>
                    @else
                        @php $showCommandColumn = auth()->user()->hasRole('CC T&L') || auth()->user()->hasRole('CGC'); @endphp
                        <div class="overflow-x-auto">
                            <table class="kt-table w-full" id="by-type-report-table">
                                <thead>
                                    <tr>
                                        <th class="text-left">S/N</th>
                                        <th class="text-left">Reg No.</th>
                                        <th class="text-left">Type</th>
                                        <th class="text-left">Make/Model</th>
                                        <th class="text-left">Chassis No.</th>
                                        <th class="text-left">Engine No.</th>
                                        @if($showCommandColumn)
                                            <th class="text-left cursor-pointer select-none hover:bg-muted/50" id="sort-by-command" title="Click to sort by Command">
                                                Command <span id="command-sort-icon" class="text-secondary-foreground">↕</span>
                                            </th>
                                        @endif
                                        <th class="text-left">Officer (Allocated To)</th>
                                        <th class="text-left">Service No.</th>
                                        <th class="text-left">Service Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicles as $i => $v)
                                        <tr data-command="{{ $v->currentCommand?->name ?? '' }}">
                                            <td class="report-sn">{{ $i + 1 }}</td>
                                            <td>{{ $v->reg_no ?? '-' }}</td>
                                            <td>{{ $vehicleTypeLabel ?? $v->vehicle_type }}</td>
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
                        <p class="mt-4 text-sm text-secondary-foreground"><strong>Total:</strong> {{ $vehicles->count() }} vehicle(s)</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @push('styles')
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

    @if($vehicleType !== null && !$vehicles->isEmpty() && (auth()->user()->hasRole('CC T&L') || auth()->user()->hasRole('CGC')))
        @push('scripts')
        <script>
            (function() {
                var btn = document.getElementById('sort-by-command');
                var icon = document.getElementById('command-sort-icon');
                var tbody = document.querySelector('#by-type-report-table tbody');
                if (!btn || !tbody) return;
                var order = 1; // 1 = asc, -1 = desc
                btn.addEventListener('click', function() {
                    var rows = Array.from(tbody.querySelectorAll('tr'));
                    rows.sort(function(a, b) {
                        var cmdA = (a.getAttribute('data-command') || '').toLowerCase();
                        var cmdB = (b.getAttribute('data-command') || '').toLowerCase();
                        var cmp = cmdA.localeCompare(cmdB);
                        return order * cmp;
                    });
                    order = -order;
                    icon.textContent = order === 1 ? '↑' : '↓';
                    rows.forEach(function(r) { tbody.appendChild(r); });
                    tbody.querySelectorAll('.report-sn').forEach(function(cell, i) { cell.textContent = i + 1; });
                });
            })();
        </script>
        @endpush
    @endif
@endsection
