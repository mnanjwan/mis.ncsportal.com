@extends('layouts.app')

@section('title', 'TRADOC Dashboard')
@section('page-title', 'TRADOC Dashboard')

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
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Results</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-file text-2xl text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Passed</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['passed'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-check text-2xl text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Failed</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['failed'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-danger/10">
                            <i class="ki-filled ki-cross text-2xl text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Service Number</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['pending_service_number'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-time text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tradoc.download-template') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-file-down"></i> Download New Recruits Template
                    </a>
                    <a href="{{ route('tradoc.upload') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-file-up"></i> Upload Training Results
                    </a>
                    <a href="{{ route('tradoc.sorted-results') }}" class="kt-btn kt-btn-info">
                        <i class="ki-filled ki-file"></i> View Sorted Results
                    </a>
                    <a href="{{ route('tradoc.export-sorted') }}" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-file-down"></i> Export for Establishment
                    </a>
                </div>
                <div class="mt-4 p-3 bg-info/10 border border-info/20 rounded">
                    <div class="flex items-start gap-2">
                        <i class="ki-filled ki-information text-info text-sm mt-0.5"></i>
                        <div class="text-xs text-secondary-foreground">
                            <p class="font-medium text-info mb-1">Download Template:</p>
                            <p>Download the new recruits template CSV file with pre-filled scores (0). Update the scores and upload it back using the "Upload Training Results" button.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Results Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Training Results</h3>
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
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Uploaded</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
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
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">{{ $result->uploaded_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" 
                                                        onclick="showResultModal({{ $result->id }})"
                                                        class="kt-btn kt-btn-sm kt-btn-ghost"
                                                        title="View Details">
                                                    <i class="ki-filled ki-eye"></i> View
                                                </button>
                                                @if(!$result->service_number)
                                                    <button type="button" 
                                                            onclick="showDeleteModal({{ $result->id }}, '{{ $result->appointment_number }}', '{{ $result->officer_name }}')"
                                                            class="kt-btn kt-btn-sm kt-btn-danger"
                                                            title="Delete">
                                                        <i class="ki-filled ki-trash"></i> Delete
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($results->hasPages())
                        <div class="mt-6 pt-4 border-t border-border">
                            {{ $results->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-file text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No training results found</p>
                        <a href="{{ route('tradoc.upload') }}" class="kt-btn kt-btn-primary mt-4">
                            <i class="ki-filled ki-file-up"></i> Upload Training Results
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- View Result Modal -->
    <div class="kt-modal" data-kt-modal="true" id="view-result-modal">
        <div class="kt-modal-content max-w-[600px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Training Result Details</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5" id="result-modal-content">
                <!-- Content will be loaded here -->
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="delete-confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                        <i class="ki-filled ki-information text-danger text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Deletion</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to delete the training result for <strong id="delete-recruit-name"></strong> (Appointment: <strong id="delete-appointment-number"></strong>)? This action cannot be undone.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form action="" method="POST" id="deleteResultForm" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-trash"></i>
                        <span>Delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showResultModal(resultId) {
            // Fetch result details via AJAX
            fetch(`{{ url('tradoc/results') }}/${resultId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    document.getElementById('result-modal-content').innerHTML = html;
                    const modal = document.getElementById('view-result-modal');
                    if (typeof KTModal !== 'undefined') {
                        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                        modalInstance.show();
                    } else {
                        modal.style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error loading result:', error);
                    alert('Failed to load result details. Please try again.');
                });
        }

        function showDeleteModal(resultId, appointmentNumber, officerName) {
            document.getElementById('delete-recruit-name').textContent = officerName;
            document.getElementById('delete-appointment-number').textContent = appointmentNumber;
            document.getElementById('deleteResultForm').action = `{{ url('tradoc/results') }}/${resultId}`;
            
            const modal = document.getElementById('delete-confirm-modal');
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
