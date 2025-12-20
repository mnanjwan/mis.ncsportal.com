@extends('layouts.app')

@section('title', 'Sorted Training Results')
@section('page-title', 'Sorted Training Results')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('tradoc.dashboard') }}">TRADOC</a>
    <span>/</span>
    <span class="text-primary">Sorted Results</span>
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
                        <p class="text-sm font-medium text-info mb-1">Sorted Training Results</p>
                        <p class="text-xs text-secondary-foreground">
                            Results are sorted by performance (highest to lowest). These results are ready to be sent to Establishment for service number assignment. You can export this list as CSV for Establishment.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('tradoc.export-sorted') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-file-down"></i> Export CSV for Establishment
                    </a>
                    <a href="{{ route('tradoc.dashboard') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Sorted Results Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Training Results (Sorted by Performance - Highest to Lowest)</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $results->count() }} result(s)
                    </span>
                </div>
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
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Uploaded At</th>
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
                                            <span class="text-sm font-mono text-foreground">{{ $result->service_number ?? 'Pending Assignment' }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">{{ $result->uploaded_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <button type="button" 
                                                    onclick="showResultModal({{ $result->id }})"
                                                    class="kt-btn kt-btn-sm kt-btn-ghost"
                                                    title="View Details">
                                                <i class="ki-filled ki-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-file text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No sorted results available</p>
                        <p class="text-sm text-secondary-foreground mt-1">Upload training results first to see sorted list</p>
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

    @push('scripts')
    <script>
        function showResultModal(resultId) {
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
    </script>
    @endpush
@endsection
