@extends('layouts.app')

@section('title', 'Serviceability Report')
@section('page-title', 'Serviceability Report')

@section('breadcrumbs')
    @php
        $user = auth()->user();
        $dashboardRoute = null;
        if ($user->hasRole('CD')) {
            $dashboardRoute = route('fleet.cd.dashboard');
        } elseif ($user->hasRole('CC T&L')) {
            $dashboardRoute = route('fleet.cc-tl.dashboard');
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">Fleet</a>
        <span>/</span>
    @endif
    <span class="text-primary">Serviceability Report</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Vehicle Serviceability Report</h3>
            <div class="kt-card-toolbar">
                <button onclick="window.print()" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-printer"></i> Print Report
                </button>
            </div>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="GET" action="{{ route('fleet.reports.serviceability') }}" class="mb-6 flex gap-4 items-end">
                <div>
                    <label class="text-sm font-medium">Month</label>
                    <select name="month" class="kt-select">
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $monthDate = \Carbon\Carbon::create($year, $m, 1);
                            @endphp
                            <option value="{{ $m }}" @selected($month == $m)>{{ $monthDate->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Year</label>
                    <input type="number" name="year" value="{{ $year }}" min="2020" max="{{ now()->year + 1 }}" class="kt-input" />
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Generate Report</button>
            </form>

            <!-- Print View -->
            <div id="print-view" class="print-view">
                <!-- Header -->
                <div class="print-header">
                    <div class="text-center mb-6">
                        <h1 class="text-xl font-bold uppercase mb-2">NIGERIA CUSTOMS SERVICE OF {{ strtoupper($commandName ?? 'FOU ZONE "A", IKEJA – LAGOS') }}</h1>
                        <h2 class="text-lg font-bold uppercase mb-2">UPDATE OF TRANSPORT LOGISTICS UNIT</h2>
                        <h3 class="text-base font-bold uppercase">
                            THE COMPREHENSIVE LIST OF SERVICABLE AND<br>
                            UNSERVICABLE VEHICLES AND MOTORCYCLES FOR THE<br>
                            MONTH OF {{ strtoupper($monthName) }}, {{ $year }}
                        </h3>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="print-summary mb-8">
                    <div class="text-right">
                        <div class="text-lg font-bold uppercase mb-2">SUMMARY</div>
                        <div class="space-y-1 text-base">
                            <div class="flex justify-end items-center gap-4">
                                <span class="font-semibold uppercase">SERVICABLE</span>
                                <span class="font-bold">=</span>
                                <span class="font-bold">{{ $serviceable }}</span>
                            </div>
                            <div class="flex justify-end items-center gap-4">
                                <span class="font-semibold uppercase">UNSERVICEABLE</span>
                                <span class="font-bold">=</span>
                                <span class="font-bold">{{ $unserviceable }}</span>
                            </div>
                            <div class="flex justify-end items-center gap-4">
                                <span class="font-semibold uppercase">TOTAL</span>
                                <span class="font-bold">=</span>
                                <span class="font-bold">{{ $total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Serviceable Vehicles Detail Table -->
                @if($serviceableVehicles->count() > 0)
                <div class="print-detail mb-8">
                    <h3 class="text-lg font-bold uppercase mb-4 text-center">SERVICEABLE VEHICLES</h3>
                    <table class="w-full border-collapse border border-gray-800">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-800 px-3 py-2 text-left font-bold uppercase">S/NO</th>
                                <th class="border border-gray-800 px-3 py-2 text-left font-bold uppercase">VEHICLE TYPE</th>
                                <th class="border border-gray-800 px-3 py-2 text-left font-bold uppercase">CHASSIS NUMBER</th>
                                <th class="border border-gray-800 px-3 py-2 text-left font-bold uppercase">REG. NUMBER</th>
                                <th class="border border-gray-800 px-3 py-2 text-left font-bold uppercase">LOCATION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceableVehicles as $index => $vehicle)
                            <tr>
                                <td class="border border-gray-800 px-3 py-2">{{ $index + 1 }}</td>
                                <td class="border border-gray-800 px-3 py-2 uppercase">
                                    @if($vehicle->vehicleModel)
                                        {{ strtoupper($vehicle->vehicleModel->display_name) }}
                                    @else
                                        {{ strtoupper($vehicle->make . ($vehicle->model ? ' ' . $vehicle->model : '')) }}
                                    @endif
                                </td>
                                <td class="border border-gray-800 px-3 py-2">{{ $vehicle->chassis_number }}</td>
                                <td class="border border-gray-800 px-3 py-2">{{ $vehicle->reg_no ?? 'N/A' }}</td>
                                <td class="border border-gray-800 px-3 py-2">{{ $vehicle->currentCommand?->location ?? ($vehicle->currentCommand?->name ?? 'N/A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Prepared By Section -->
                <div class="print-footer mt-12">
                    <div class="text-left">
                        <div class="mb-2">
                            <span class="font-semibold">Prepared By:</span>
                            <span class="ml-2">{{ $preparedBy['rank'] ?? 'CD' }}</span>
                        </div>
                        @if($preparedBy)
                        <div>
                            <span>{{ $preparedBy['service_number'] ?? '' }}</span>
                            @if($preparedBy['service_number'] && $preparedBy['name'])
                                <span>, </span>
                            @endif
                            <span>{{ strtoupper($preparedBy['name'] ?? '') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            
            #print-view,
            #print-view * {
                visibility: visible;
            }
            
            #print-view {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
            
            .print-header h1,
            .print-header h2,
            .print-header h3 {
                margin-bottom: 10px;
                line-height: 1.4;
            }
            
            .print-summary {
                margin-top: 30px;
                margin-bottom: 30px;
            }
            
            .print-summary > div {
                max-width: 300px;
                margin-left: auto;
            }
            
            .print-summary .space-y-1 > div {
                margin-bottom: 8px;
            }
            
            .print-detail table {
                width: 100%;
                margin-top: 20px;
                font-size: 12px;
            }
            
            .print-detail th,
            .print-detail td {
                padding: 8px;
                text-align: left;
            }
            
            .print-footer {
                margin-top: 50px;
                page-break-inside: avoid;
            }
            
            .kt-card-header,
            .kt-card-toolbar,
            form {
                display: none !important;
            }
        }
        
        @media screen {
            .print-view {
                background: white;
                padding: 40px;
                max-width: 8.5in;
                margin: 0 auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            
            .print-header {
                border-bottom: 2px solid #000;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            
            .print-summary {
                border: 1px solid #ddd;
                padding: 20px;
                background: #f9f9f9;
            }
            
            .print-detail table {
                border-collapse: collapse;
            }
        }
    </style>
    @endpush
@endsection
