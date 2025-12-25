@extends('layouts.app')

@section('title', 'My Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">My Emoluments</span>
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

    @if(session('info'))
        <div class="kt-card bg-info/10 border border-info/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-info text-xl"></i>
                    <p class="text-sm font-medium text-info">{{ session('info') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-foreground">Total Raised</p>
                            <p class="text-2xl font-bold text-foreground">{{ $stats['raised'] }}</p>
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
                            <p class="text-sm text-secondary-foreground">Assessed</p>
                            <p class="text-2xl font-bold text-foreground">{{ $stats['assessed'] }}</p>
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
                            <p class="text-sm text-secondary-foreground">Validated</p>
                            <p class="text-2xl font-bold text-foreground">{{ $stats['validated'] }}</p>
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
                            <p class="text-sm text-secondary-foreground">Processed</p>
                            <p class="text-2xl font-bold text-foreground">{{ $stats['processed'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-[#088a56] bg-opacity-10 rounded-full flex items-center justify-center">
                            <i class="ki-filled ki-verify text-[#088a56] text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emoluments Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emolument History</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('emolument.raise') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Raise Emolument
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 1000px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Year</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Timeline</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Bank Details</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">PFA Details</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Submitted</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($emoluments as $emolument)
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
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4 text-sm font-medium text-foreground" style="white-space: nowrap;">{{ $emolument->year }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $emolument->timeline ? $emolument->timeline->year : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            <div>{{ $emolument->bank_name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ $emolument->bank_account_number }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            <div>{{ $emolument->pfa_name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ $emolument->rsa_pin }}</div>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $class }}">
                                                {{ ucfirst(strtolower($emolument->status)) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $emolument->submitted_at ? $emolument->submitted_at->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <a href="{{ route('officer.emoluments') }}/{{ $emolument->id }}"
                                                class="kt-btn kt-btn-sm kt-btn-ghost"
                                                title="View Details">
                                                <i class="ki-filled ki-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground mb-4">No emoluments found</p>
                                            <a href="{{ route('emolument.raise') }}"
                                                class="kt-btn kt-btn-primary">
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

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        @forelse($emoluments as $emolument)
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
                            <div class="p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                            <i class="ki-filled ki-document text-info text-xl"></i>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-semibold text-foreground">
                                                Year {{ $emolument->year }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                Timeline: {{ $emolument->timeline ? $emolument->timeline->year : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $class }}">
                                        {{ ucfirst(strtolower($emolument->status)) }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-3 mb-3">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium text-secondary-foreground">Bank Details</span>
                                        <span class="text-sm text-foreground">{{ $emolument->bank_name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $emolument->bank_account_number }}</span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium text-secondary-foreground">PFA Details</span>
                                        <span class="text-sm text-foreground">{{ $emolument->pfa_name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $emolument->rsa_pin }}</span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium text-secondary-foreground">Submitted</span>
                                        <span class="text-sm text-foreground">
                                            {{ $emolument->submitted_at ? $emolument->submitted_at->format('d M Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end pt-3 border-t border-border">
                                    <a href="{{ route('officer.emoluments') }}/{{ $emolument->id }}"
                                        class="kt-btn kt-btn-sm kt-btn-ghost">
                                        <i class="ki-filled ki-eye"></i>
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground mb-4">No emoluments found</p>
                                <a href="{{ route('emolument.raise') }}"
                                    class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-plus"></i>
                                    Raise Your First Emolument
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection