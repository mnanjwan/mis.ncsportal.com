@extends('layouts.app')

@section('title', 'Training Results')
@section('page-title', 'Training Results')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <span class="text-primary">Training Results</span>
@endsection

@section('content')
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
        <!-- Info Card -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-info mb-1">Service Number Assignment (Rank-Based)</p>
                        <p class="text-xs text-secondary-foreground mb-2">
                            Service numbers are assigned by rank, with each rank maintaining its own sequence. Within each rank, numbers are assigned based on performance (highest to lowest).
                        </p>
                        @if(isset($resultsByRank) && $resultsByRank->count() > 0)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($resultsByRank as $rank => $rankResults)
                                    <span class="kt-badge kt-badge-primary kt-badge-sm">
                                        {{ $rank }}: {{ $rankResults->count() }} officer(s)
                                        @if(isset($lastServiceNumbersByRank[$rank]))
                                            (Last: {{ $lastServiceNumbersByRank[$rank] }})
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Form -->
        @if($results->count() > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Assign Service Numbers</h3>
                </div>
                <div class="kt-card-content">
                    <form action="{{ route('establishment.assign-service-numbers') }}" method="POST" id="assignServiceNumbersForm">
                        @csrf
                        <div class="flex flex-col gap-4">
                            <div class="kt-card bg-primary/10 border border-primary/20 p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-primary text-lg mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-primary mb-1">Rank-Based Assignment</p>
                                        <p class="text-xs text-secondary-foreground">
                                            Service numbers will be assigned automatically to all officers by rank. Each rank will continue from its last assigned number, and within each rank, officers will be assigned based on their training performance (highest score to lowest score).
                                </p>
                                    </div>
                                </div>
                            </div>

                            @if(isset($resultsByRank) && $resultsByRank->count() > 0)
                                <div>
                                    <p class="text-sm font-medium text-foreground mb-2">Assignment Preview:</p>
                                    <div class="space-y-2">
                                        @foreach($resultsByRank as $rank => $rankResults)
                                            <div class="flex items-center justify-between p-2 bg-muted/50 rounded">
                                                <span class="text-sm text-foreground">
                                                    <strong>{{ $rank }}</strong>: {{ $rankResults->count() }} officer(s)
                                                </span>
                                                <span class="text-xs text-secondary-foreground">
                                                    @if(isset($lastServiceNumbersByRank[$rank]))
                                                        Will start from: NCS{{ str_pad((int)substr($lastServiceNumbersByRank[$rank], 3) + 1, 5, '0', STR_PAD_LEFT) }}
                                                    @else
                                                        Will start from: NCS00001
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <input type="hidden" name="rank_based" value="1">

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                                <button type="button" 
                                        onclick="showAssignModal()"
                                        class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-check"></i>
                                    Assign Service Numbers by Rank
                                </button>
                            </div>
                            </div>
                        </form>
                </div>
            </div>
        @endif

        <!-- Training Results Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Training Results (Sorted by Performance)</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($results->count() > 0)
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Rank
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Appointment Number
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Officer Name
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Score
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Service Number
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-primary kt-badge-sm">{{ $result->rank ?? 'N/A' }}</span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-mono text-foreground">{{ $result->appointment_number }}</span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm" style="flex-shrink: 0;">
                                                    {{ strtoupper(substr($result->officer_name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-foreground">{{ $result->officer_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-semibold text-foreground">{{ number_format($result->training_score, 2) }}%</span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-mono text-foreground">{{ $result->service_number ?? 'Pending' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 px-4">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No training results available</p>
                        <p class="text-sm text-muted-foreground mt-1">TRADOC needs to upload training results first</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assign Service Numbers Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="assign-confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                        <i class="ki-filled ki-information text-primary text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Service Number Assignment</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground mb-3">
                    Assign service numbers grouped by rank? Each rank will maintain its own sequence, and within each rank, officers will be assigned based on training performance (highest to lowest).
                </p>
                @if(isset($resultsByRank) && $resultsByRank->count() > 0)
                    <div class="bg-muted/50 p-3 rounded mb-3">
                        <p class="text-xs font-medium text-foreground mb-2">Summary:</p>
                        <ul class="text-xs text-secondary-foreground space-y-1">
                            @foreach($resultsByRank as $rank => $rankResults)
                                <li>
                                    <strong>{{ $rank }}</strong>: {{ $rankResults->count() }} officer(s)
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" type="button">
                    Cancel
                </button>
                <button type="button" onclick="document.getElementById('assignServiceNumbersForm').submit()" class="kt-btn kt-btn-primary" id="confirmAssignBtn">
                        <i class="ki-filled ki-check"></i>
                    <span>Assign by Rank</span>
                    </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showAssignModal() {
            const modal = document.getElementById('assign-confirm-modal');
            if (!modal) {
                alert('Modal not found');
                return;
            }
            
            // Try to show modal using KTModal if available
            if (typeof KTModal !== 'undefined') {
                try {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } catch (e) {
                    console.error('KTModal error:', e);
                    modal.style.display = 'flex';
                }
            } else {
                modal.style.display = 'flex';
            }
        }
        
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const assignForm = document.getElementById('assignServiceNumbersForm');
            if (assignForm) {
                assignForm.addEventListener('submit', function(e) {
                    // Disable submit button to prevent double submission
                    const submitBtn = document.getElementById('confirmAssignBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Processing...';
                    }
                });
            }
        });
    </script>
    @endpush

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
