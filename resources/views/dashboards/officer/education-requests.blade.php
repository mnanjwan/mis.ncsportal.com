@extends('layouts.app')

@section('title', 'Education Requests')
@section('page-title', 'Education Qualification Requests')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Education Requests</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">My Education Qualification Requests</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('officer.education-requests.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Request Approval
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-secondary-foreground">
                    Submit an additional qualification for HRD approval. Once approved, it will be added to your Educational History.
                </p>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($requests->count() > 0)
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Request Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Institution</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Qualification</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Discipline</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Year</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Verified At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4 text-sm text-foreground" style="white-space: nowrap;">
                                            {{ $request->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-foreground" style="white-space: nowrap;">
                                            {{ $request->university }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-foreground" style="white-space: nowrap;">
                                            {{ $request->qualification }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $request->discipline ?? '—' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $request->year_obtained }}
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'danger' : 'warning') }} kt-badge-sm">
                                                {{ $request->status }}
                                            </span>
                                            @if($request->status === 'REJECTED' && $request->rejection_reason)
                                                <div class="text-xs text-danger mt-1">
                                                    {{ \Illuminate\Support\Str::limit($request->rejection_reason, 70) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $request->verified_at ? $request->verified_at->format('d/m/Y H:i') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($requests->hasPages())
                        <div class="mt-6 pt-4 border-t border-border px-4">
                            {{ $requests->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12 px-4">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No education qualification requests found</p>
                        <a href="{{ route('officer.education-requests.create') }}" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Request Approval
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 768px) {
            body { overflow-x: hidden; }
            .kt-card { max-width: 100vw; }
        }
        .table-scroll-wrapper { position: relative; max-width: 100%; }
        .scrollbar-thin::-webkit-scrollbar { height: 8px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
@endsection

