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
                        <p class="text-sm font-medium text-info mb-1">Service Number Assignment</p>
                        <p class="text-xs text-secondary-foreground">
                            These training results are sorted by performance (highest to lowest). Service numbers will be assigned based on this order, with the highest scorer receiving the first available service number.
                        </p>
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
                    <form action="{{ route('establishment.assign-service-numbers') }}" method="POST">
                        @csrf
                        <div class="flex flex-col gap-4">
                            <div>
                                <label for="last_service_number" class="block text-sm font-medium text-foreground mb-2">
                                    Last Service Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="last_service_number" 
                                       name="last_service_number" 
                                       value="{{ $lastServiceNumber ?? '' }}"
                                       class="kt-input w-full" 
                                       required
                                       placeholder="e.g., NCS50001">
                                <p class="text-xs text-secondary-foreground mt-1">
                                    Enter the last assigned service number. New numbers will start from +1.
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                                <button type="button" 
                                        onclick="showAssignModal()"
                                        class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-check"></i>
                                    Assign Service Numbers
                                </button>
                            </div>
                        </form>
                </div>
            </div>
        @endif

        <!-- Training Results Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Training Results (Sorted by Performance)</h3>
            </div>
            <div class="kt-card-content">
                @if($results->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Appointment Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer Name</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Score</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-primary kt-badge-sm">{{ $result->rank ?? 'N/A' }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-foreground">{{ $result->appointment_number }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                                    {{ strtoupper(substr($result->officer_name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-foreground">{{ $result->officer_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-semibold text-foreground">{{ number_format($result->training_score, 2) }}%</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $result->status === 'PASS' ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $result->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-foreground">{{ $result->service_number ?? 'Pending' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-file text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No training results available</p>
                        <p class="text-sm text-secondary-foreground mt-1">TRADOC needs to upload training results first</p>
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
                <p class="text-sm text-secondary-foreground">
                    Assign service numbers based on training performance? This will assign numbers starting from the next available number. Highest scorer will get the first number.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" type="button">
                    Cancel
                </button>
                <form action="{{ route('establishment.assign-service-numbers') }}" method="POST" class="inline" id="assignServiceNumbersForm">
                    @csrf
                    <input type="hidden" name="last_service_number" id="modal-last-service-number" value="{{ $lastServiceNumber ?? '' }}">
                    <button type="submit" class="kt-btn kt-btn-primary" id="confirmAssignBtn">
                        <i class="ki-filled ki-check"></i>
                        <span>Assign</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showAssignModal() {
            const lastServiceNumberInput = document.querySelector('input[name="last_service_number"]');
            if (!lastServiceNumberInput || !lastServiceNumberInput.value) {
                alert('Please enter the last service number first.');
                return;
            }
            
            const lastServiceNumber = lastServiceNumberInput.value;
            const modalInput = document.getElementById('modal-last-service-number');
            if (modalInput) {
                modalInput.value = lastServiceNumber;
            }
            
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
        
        // Handle modal form submission
        document.addEventListener('DOMContentLoaded', function() {
            const assignForm = document.getElementById('assignServiceNumbersForm');
            if (assignForm) {
                assignForm.addEventListener('submit', function(e) {
                    const input = document.getElementById('modal-last-service-number');
                    if (!input || !input.value || input.value.trim() === '') {
                        e.preventDefault();
                        alert('Please enter the last service number.');
                        return false;
                    }
                    
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
@endsection
