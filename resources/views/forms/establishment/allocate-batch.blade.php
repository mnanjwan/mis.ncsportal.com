@extends('layouts.app')

@section('title', 'Allocate New Batch')
@section('page-title', 'Allocate New Batch')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.service-numbers') }}">Service Numbers</a>
    <span>/</span>
    <span class="text-primary">Allocate Batch</span>
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
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Allocate Service Numbers - New Batch</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('establishment.service-numbers.process-batch') }}" method="POST" id="allocateForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Allocation Type -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Allocation Type <span class="text-danger">*</span>
                            </label>
                            <div class="flex flex-col gap-3">
                                <label class="flex items-center gap-3 p-4 rounded-lg border border-input cursor-pointer hover:bg-muted/50">
                                    <input type="radio" name="allocation_type" value="performance" class="w-4 h-4 text-primary" checked>
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-foreground block">Performance-Based (Recommended)</span>
                                        <span class="text-xs text-secondary-foreground">Assign based on training results (highest scorer gets first number)</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-4 rounded-lg border border-input cursor-pointer hover:bg-muted/50">
                                    <input type="radio" name="allocation_type" value="sequential" class="w-4 h-4 text-primary">
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-foreground block">Sequential</span>
                                        <span class="text-xs text-secondary-foreground">Assign sequentially to officers with appointment numbers</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Last Service Number -->
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

                        <!-- Available Officers -->
                        @if(isset($officers) && $officers->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Officers Available for Allocation ({{ $officers->count() }})
                                </label>
                                <div class="kt-card bg-muted/50 border border-input">
                                    <div class="kt-card-content p-4 max-h-96 overflow-y-auto">
                                        <div class="space-y-2">
                                            @foreach($officers as $officer)
                                                <div class="flex items-center justify-between p-2 rounded bg-background">
                                                    <div>
                                                        <span class="text-sm font-medium text-foreground">{{ $officer->initials }} {{ $officer->surname }}</span>
                                                        <span class="text-xs text-secondary-foreground ml-2">({{ $officer->appointment_number }})</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="kt-card bg-warning/10 border border-warning/20">
                                <div class="kt-card-content p-4">
                                    <div class="flex items-center gap-3">
                                        <i class="ki-filled ki-information text-warning text-xl"></i>
                                        <p class="text-sm text-secondary-foreground">
                                            No officers available for allocation. Officers need appointment numbers first.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Allocation Process</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li><strong>Performance-Based:</strong> Uses TRADOC training results sorted by performance</li>
                                            <li><strong>Sequential:</strong> Assigns to officers with appointment numbers in order</li>
                                            <li>Service numbers will be generated starting from last number + 1</li>
                                            <li>Only officers who passed training (for performance-based) will receive service numbers</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('establishment.service-numbers') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="button" onclick="showAllocateModal()" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Allocate Batch
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Allocate Batch Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="allocate-confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                        <i class="ki-filled ki-information text-primary text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Batch Allocation</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to allocate service numbers? This action cannot be undone.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <button type="submit" form="allocateForm" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check"></i>
                    <span>Allocate</span>
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showAllocateModal() {
            const modal = document.getElementById('allocate-confirm-modal');
            if (typeof KTModal !== 'undefined') {
                const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                modalInstance.show();
            } else {
                modal.style.display = 'flex';
            }
        }
    </script>
    @endpush
@endsection
