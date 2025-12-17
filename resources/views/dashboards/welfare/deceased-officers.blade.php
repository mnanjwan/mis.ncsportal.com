@extends('layouts.app')

@section('title', 'Deceased Officers')
@section('page-title', 'Deceased Officers')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-col gap-4" id="deceased-officers-list">
                <p class="text-secondary-foreground text-center py-8">Loading deceased officers...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadDeceasedOfficers();
});

async function loadDeceasedOfficers() {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/deceased-officers?per_page=50', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('deceased-officers-list');
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(deceased => `
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-mono">${deceased.officer?.initials || ''} ${deceased.officer?.surname || ''}</span>
                                <span class="text-xs text-secondary-foreground">Date of Death: ${new Date(deceased.date_of_death).toLocaleDateString()}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="kt-badge kt-badge-${deceased.settlement_status === 'SETTLED' ? 'success' : 'warning'} kt-badge-sm">
                                ${deceased.settlement_status || 'PENDING'}
                            </span>
                            <a href="/welfare/deceased-officers/${deceased.id}" class="kt-btn kt-btn-sm kt-btn-ghost">View</a>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary-foreground text-center py-8">No deceased officers found</p>';
            }
        }
    } catch (error) {
        console.error('Error loading deceased officers:', error);
    }
}
</script>
@endpush
@endsection


