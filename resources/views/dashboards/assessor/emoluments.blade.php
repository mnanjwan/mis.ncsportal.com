@extends('layouts.app')

@section('title', 'Emoluments for Assessment')
@section('page-title', 'Emoluments for Assessment')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filter -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-mono">Pending Emoluments</h2>
        <select id="status-filter" class="kt-input" style="width: 200px;">
            <option value="SUBMITTED">Submitted</option>
            <option value="ASSESSED">Assessed</option>
            <option value="all">All</option>
        </select>
    </div>
    
    <!-- Emoluments List -->
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-col gap-4" id="emoluments-list">
                <p class="text-secondary-foreground text-center py-8">Loading emoluments...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadEmoluments();
    document.getElementById('status-filter').addEventListener('change', loadEmoluments);
});

async function loadEmoluments() {
    try {
        const token = window.API_CONFIG.token;
        const filter = document.getElementById('status-filter').value;
        const params = new URLSearchParams({ per_page: 50 });
        
        if (filter !== 'all') {
            params.append('status', filter);
        }
        
        const res = await fetch(`/api/v1/emoluments?${params}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('emoluments-list');
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(emol => {
                    const statusColors = {
                        'SUBMITTED': 'warning',
                        'ASSESSED': 'success',
                        'VALIDATED': 'info'
                    };
                    const statusColor = statusColors[emol.status] || 'secondary';
                    
                    return `
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                    <i class="ki-filled ki-wallet text-primary text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-mono">
                                        ${emol.officer?.initials || ''} ${emol.officer?.surname || ''} - SVC: ${emol.officer?.service_number || 'N/A'}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Submitted: ${new Date(emol.submitted_at).toLocaleDateString()}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="kt-badge kt-badge-${statusColor} kt-badge-sm">${emol.status}</span>
                                ${emol.status === 'SUBMITTED' ? `
                                    <a href="/assessor/emoluments/${emol.id}/assess" class="kt-btn kt-btn-sm kt-btn-primary">Assess</a>
                                ` : ''}
                                <a href="/assessor/emoluments/${emol.id}" class="kt-btn kt-btn-sm kt-btn-ghost">View</a>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<p class="text-secondary-foreground text-center py-8">No emoluments found</p>';
            }
        }
    } catch (error) {
        console.error('Error loading emoluments:', error);
    }
}
</script>
@endpush
@endsection


