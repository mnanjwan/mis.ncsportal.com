@extends('layouts.app')

@section('title', $title ?? 'Fleet Dashboard')
@section('page-title', $title ?? 'Fleet Dashboard')

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">{{ $title ?? 'Fleet Dashboard' }}</h3>
        </div>
        <div class="kt-card-content p-6">
            <p class="text-sm text-secondary-foreground">
                Fleet module is enabled for your role: <strong>{{ $roleName ?? 'N/A' }}</strong>.
            </p>
            <p class="text-sm text-secondary-foreground mt-2">
                Next: requisitions, approvals, releases, issuance, returns reports, and Reg/Engine audit trail screens.
            </p>
        </div>
    </div>
@endsection

