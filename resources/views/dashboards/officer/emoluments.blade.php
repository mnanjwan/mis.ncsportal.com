@extends('layouts.app')

@section('title', 'My Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">My Emoluments</span>
@endsection

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Emoluments</h1>
                <p class="text-sm text-gray-600 mt-1">View and manage your emolument submissions</p>
            </div>
            <a href="{{ route('emolument.raise') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i>
                Raise Emolument
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                {{ session('info') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Raised</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['raised'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="ki-filled ki-document text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Assessed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['assessed'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="ki-filled ki-eye text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Validated</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['validated'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="ki-filled ki-check text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Processed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['processed'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-[#088a56] bg-opacity-10 rounded-full flex items-center justify-center">
                            <i class="ki-filled ki-verify text-[#088a56] text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emoluments Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emolument History</h3>
            </div>
            <div class="kt-card-content">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Year</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Timeline</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Bank Details</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">PFA Details</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Submitted</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emoluments as $emolument)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-900">{{ $emolument->year }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        {{ $emolument->timeline ? $emolument->timeline->year : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <div>{{ $emolument->bank_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $emolument->bank_account_number }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <div>{{ $emolument->pfa_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $emolument->rsa_pin }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $statusClasses = [
                                                'RAISED' => 'bg-blue-100 text-blue-800',
                                                'ASSESSED' => 'bg-yellow-100 text-yellow-800',
                                                'VALIDATED' => 'bg-green-100 text-green-800',
                                                'PROCESSED' => 'bg-[#088a56] bg-opacity-10 text-[#088a56]',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                            ];
                                            $class = $statusClasses[$emolument->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $class }}">
                                            {{ ucfirst(strtolower($emolument->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        {{ $emolument->submitted_at ? $emolument->submitted_at->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('officer.emoluments') }}/{{ $emolument->id }}"
                                            class="text-[#088a56] hover:text-[#076d45] text-sm font-medium">
                                            <i class="ki-filled ki-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <i class="ki-filled ki-document text-6xl text-gray-300"></i>
                                        <p class="text-gray-500 mt-4">No emoluments found</p>
                                        <a href="{{ route('emolument.raise') }}"
                                            class="kt-btn kt-btn-primary mt-4 inline-flex items-center">
                                            <i class="ki-filled ki-plus"></i>
                                            Raise Your First Emolument
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection