@extends('layouts.app')

@section('title', 'Interdicted Officers')
@section('page-title', 'Interdicted Officers')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <span class="text-primary">Interdicted Officers</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Interdicted Officers</h3>
            <div class="kt-card-toolbar">
                <form method="GET" action="{{ route('accounts.interdicted-officers') }}" class="flex items-center gap-3">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by service number, name, or email..."
                           class="kt-input">
                    <button type="submit" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-magnifier"></i> Search
                    </button>
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($officers->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Command</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Bank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Account Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">PFA</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">RSA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($officers as $officer)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <span class="font-medium">{{ $officer->service_number }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $officer->initials }} {{ $officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $officer->email }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->substantive_rank }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->presentStation->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->bank_name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->bank_account_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->pfa_name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $officer->rsa_number ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-border">
                    {{ $officers->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-shield-cross text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">
                        @if(request('search'))
                            No interdicted officers found matching "{{ request('search') }}"
                        @else
                            No interdicted officers found
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

