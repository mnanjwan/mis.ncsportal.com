@extends('layouts.app')

@section('title', 'Print Emoluments')
@section('page-title', 'Print Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('ict.dashboard') }}">ICT</a>
    <span>/</span>
    <span class="text-primary">Print Emoluments</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Print Emoluments Report</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('ict.emoluments.print.view') }}" target="_blank" class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <select name="status" class="kt-input w-full">
                                <option value="ALL" {{ request('status', 'ALL') == 'ALL' ? 'selected' : '' }}>All Statuses</option>
                                <option value="RAISED" {{ request('status') == 'RAISED' ? 'selected' : '' }}>Raised</option>
                                <option value="ASSESSED" {{ request('status') == 'ASSESSED' ? 'selected' : '' }}>Assessed</option>
                                <option value="VALIDATED" {{ request('status') == 'VALIDATED' ? 'selected' : '' }}>Validated</option>
                                <option value="AUDITED" {{ request('status') == 'AUDITED' ? 'selected' : '' }}>Audited</option>
                                <option value="PROCESSED" {{ request('status') == 'PROCESSED' ? 'selected' : '' }}>Processed</option>
                                <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <select name="year" class="kt-input w-full">
                                <option value="">All Years</option>
                                @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Zone Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                            <select name="zone_id" class="kt-input w-full">
                                <option value="">All Zones</option>
                                @foreach(\App\Models\Zone::where('is_active', true)->orderBy('name')->get() as $zone)
                                    <option value="{{ $zone->id }}" {{ (string)request('zone_id') === (string)$zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Command Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <select name="command_id" class="kt-input w-full">
                                <option value="">All Commands</option>
                                @foreach(\App\Models\Command::where('is_active', true)->orderBy('name')->get() as $command)
                                    <option value="{{ $command->id }}" {{ (string)request('command_id') === (string)$command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="kt-input w-full">
                            <p class="text-xs text-secondary-foreground mt-1">Leave empty to use year filter</p>
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="kt-input w-full">
                            <p class="text-xs text-secondary-foreground mt-1">Leave empty to use year filter</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-printer"></i> Print Report
                        </button>
                        <a href="{{ route('ict.emoluments.print') }}" class="kt-btn kt-btn-outline">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

