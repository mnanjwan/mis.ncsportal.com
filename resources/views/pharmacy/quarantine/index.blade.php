@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-white">Expired & Quarantine Stock</h1>
                <p class="text-white-50 mb-0">Manage expired drugs and record actions (Destroy, Quarantine, NAFDAC)</p>
            </div>
            <div>
                <a href="{{ route('pharmacy.reports.quarantine') }}" class="btn btn-outline-light">
                    <i class="fas fa-file-alt me-2"></i>View Report
                </a>
            </div>
        </div>
    </div>

    <div class="card bg-dark border-secondary shadow-sm">
        <div class="card-header border-secondary bg-transparent">
            <ul class="nav nav-tabs card-header-tabs" id="statusTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'QUARANTINED' ? 'active' : '' }} text-white" 
                       href="{{ route('pharmacy.quarantine.index', ['status' => 'QUARANTINED']) }}">
                        Quarantined
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'DESTROYED' ? 'active' : '' }} text-white" 
                       href="{{ route('pharmacy.quarantine.index', ['status' => 'DESTROYED']) }}">
                        Destroyed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'NAFDAC' ? 'active' : '' }} text-white" 
                       href="{{ route('pharmacy.quarantine.index', ['status' => 'NAFDAC']) }}">
                        Given to NAFDAC
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Drug Name</th>
                            <th>Batch #</th>
                            <th>Expiry Date</th>
                            <th>Quantity</th>
                            <th>Location</th>
                            <th>{{ $status === 'QUARANTINED' ? 'Moved At' : 'Acted At' }}</th>
                            @if($status === 'QUARANTINED')
                            <th class="text-end">Action</th>
                            @else
                            <th>Action Notes</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $record->drug->name }}</div>
                                <small class="text-white-50">{{ $record->drug->category }}</small>
                            </td>
                            <td><code>{{ $record->batch_number }}</code></td>
                            <td>
                                <span class="badge bg-danger">
                                    {{ $record->expiry_date->format('d M Y') }}
                                </span>
                            </td>
                            <td>{{ number_format($record->quantity) }}</td>
                            <td>
                                @if($record->location_type === 'CENTRAL_STORE')
                                    <span class="badge bg-info">Central Store</span>
                                @else
                                    <span class="badge bg-primary">{{ $record->command->name ?? 'Command' }}</span>
                                @endif
                            </td>
                            <td>
                                {{ ($status === 'QUARANTINED' ? $record->moved_at : $record->acted_at)?->format('d M Y H:i') }}
                            </td>
                            @if($status === 'QUARANTINED')
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#actionModal{{ $record->id }}">
                                    Action
                                </button>

                                <!-- Action Modal -->
                                <div class="modal fade" id="actionModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('pharmacy.quarantine.act', $record->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content bg-dark text-white border-secondary">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title">Record Action for {{ $record->drug->name }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">New Status</label>
                                                        <select name="status" class="form-select bg-dark text-white border-secondary" required>
                                                            <option value="DESTROYED">Destroy</option>
                                                            <option value="NAFDAC">Give to NAFDAC</option>
                                                            <option value="QUARANTINED">Keep Quarantined</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">Action Notes</label>
                                                        <textarea name="action_notes" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Enter custom notes..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Action</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            @else
                            <td class="text-white-50">
                                {{ $record->action_notes ?? 'No notes' }}
                                <br>
                                <small>By: {{ $record->actedBy->officer->full_name ?? 'System' }}</small>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-white-50 mb-0">No records found for status: {{ $status }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($records->hasPages())
        <div class="card-footer border-secondary">
            {{ $records->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
