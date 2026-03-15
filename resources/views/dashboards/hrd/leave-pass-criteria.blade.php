@extends('layouts.app')

@section('title', 'Leave & Pass Criteria')
@section('page-title', 'Leave & Pass Criteria by Rank')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Leave & Pass Criteria</span>
@endsection

@php
    $sections = [
        \App\Models\LeavePassCriterion::TYPE_ANNUAL_LEAVE => 'Annual Leave Criteria',
        \App\Models\LeavePassCriterion::TYPE_PASS => 'Pass Criteria',
    ];
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

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Rank-Based Rules for Annual Leave and Pass</h3>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-6">
                Configure per-rank criteria for annual leave and pass. Pass still requires annual leave to be exhausted first.
            </p>

            <form action="{{ route('hrd.leave-pass-criteria.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                @foreach($sections as $type => $sectionTitle)
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-foreground border-b border-border pb-2">{{ $sectionTitle }}</h4>
                        <div class="overflow-x-auto">
                            <table class="kt-table min-w-[920px]">
                                <thead>
                                    <tr>
                                        <th class="w-48">Rank</th>
                                        <th class="w-44">Max Times / Year</th>
                                        <th class="w-52">Duration Type</th>
                                        <th class="w-44">Max Duration (Days)</th>
                                        <th class="w-56">Qualification (Months in Service)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ranks as $rank)
                                        @php $row = $criteria[$type][$rank]; @endphp
                                        <tr>
                                            <td class="font-medium">{{ $rank }}</td>
                                            <td>
                                                <input type="number"
                                                       name="criteria[{{ $type }}][{{ $rank }}][max_times_per_year]"
                                                       value="{{ old("criteria.$type.$rank.max_times_per_year", $row->max_times_per_year) }}"
                                                       min="1"
                                                       max="12"
                                                       class="kt-input w-full @error("criteria.$type.$rank.max_times_per_year") kt-input-error @enderror"
                                                       required>
                                            </td>
                                            <td>
                                                <select name="criteria[{{ $type }}][{{ $rank }}][duration_type]"
                                                        class="kt-input w-full @error("criteria.$type.$rank.duration_type") kt-input-error @enderror"
                                                        required>
                                                    @foreach($durationTypes as $value => $label)
                                                        <option value="{{ $value }}" {{ old("criteria.$type.$rank.duration_type", $row->duration_type) === $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       name="criteria[{{ $type }}][{{ $rank }}][max_duration_days]"
                                                       value="{{ old("criteria.$type.$rank.max_duration_days", $row->max_duration_days) }}"
                                                       min="1"
                                                       max="365"
                                                       class="kt-input w-full @error("criteria.$type.$rank.max_duration_days") kt-input-error @enderror"
                                                       required>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       name="criteria[{{ $type }}][{{ $rank }}][qualification_months]"
                                                       value="{{ old("criteria.$type.$rank.qualification_months", $row->qualification_months) }}"
                                                       min="0"
                                                       max="600"
                                                       class="kt-input w-full @error("criteria.$type.$rank.qualification_months") kt-input-error @enderror"
                                                       required>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="rounded-md border border-warning/30 bg-warning/10 p-4">
                    <p class="text-sm text-warning font-medium">
                        Pass rule stays active: officers must first exhaust their required approved annual leave applications for the year before pass is allowed.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-border">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        Save Criteria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
