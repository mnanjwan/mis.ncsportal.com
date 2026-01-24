@extends('layouts.app')

@section('title', 'Fleet Requests')
@section('page-title', 'Fleet Requests')

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
        } elseif ($user->hasRole('CGC')) {
            $dashboardRoute = route('cgc.dashboard');
        } elseif ($user->hasRole('Area Controller')) {
            $dashboardRoute = route('area-controller.dashboard');
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">@if($user->hasRole('CGC') || $user->hasRole('Area Controller'))Dashboard @else Fleet @endif</a>
        <span>/</span>
    @endif
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.requests.index') }}">Requests</a>
    <span>/</span>
    <span class="text-primary">List</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header flex items-center justify-between">
                <h3 class="kt-card-title">Inbox</h3>
                @if(auth()->user()->hasRole('CD'))
                    <a class="kt-btn kt-btn-primary" href="{{ route('fleet.requests.create') }}">
                        <i class="ki-filled ki-plus"></i>
                        New Request
                    </a>
                @endif
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                @if($inbox->isEmpty())
                    <p class="text-sm text-secondary-foreground">No pending requests awaiting your action.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Command</th>
                                    <th class="text-left">Type</th>
                                    <th class="text-left">Make/Model</th>
                                    <th class="text-left">Qty</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inbox as $req)
                                    <tr>
                                        <td>#{{ $req->id }}</td>
                                        <td>{{ $req->originCommand->name ?? 'N/A' }}</td>
                                        <td>{{ $req->requested_vehicle_type }}</td>
                                        <td>{{ trim(($req->requested_make ?? '') . ' ' . ($req->requested_model ?? '')) ?: '-' }}</td>
                                        <td>{{ $req->requested_quantity }}</td>
                                        <td>{{ $req->status }}</td>
                                        <td>
                                            <a class="kt-btn kt-btn-sm" href="{{ route('fleet.requests.show', $req) }}">Open</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">My Requests</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                @if($myRequests->isEmpty())
                    <p class="text-sm text-secondary-foreground">You have not created any requests yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Type</th>
                                    <th class="text-left">Make/Model</th>
                                    <th class="text-left">Qty</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Submit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myRequests as $req)
                                    <tr>
                                        <td>#{{ $req->id }}</td>
                                        <td>{{ $req->requested_vehicle_type }}</td>
                                        <td>{{ trim(($req->requested_make ?? '') . ' ' . ($req->requested_model ?? '')) ?: '-' }}</td>
                                        <td>{{ $req->requested_quantity }}</td>
                                        <td>{{ $req->status }}</td>
                                        <td>
                                            @if($req->status === 'DRAFT')
                                                <form method="POST" action="{{ route('fleet.requests.submit', $req) }}">
                                                    @csrf
                                                    <button class="kt-btn kt-btn-sm kt-btn-primary">Submit</button>
                                                </form>
                                            @else
                                                <a class="kt-btn kt-btn-sm" href="{{ route('fleet.requests.show', $req) }}">Open</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

