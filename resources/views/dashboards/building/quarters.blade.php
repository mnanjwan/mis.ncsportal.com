@extends('layouts.app')

@section('title', 'Quarters Management')
@section('page-title', 'Quarters Management')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-col gap-4" id="quarters-list">
                <p class="text-secondary-foreground text-center py-8">Loading quarters...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadQuarters();
});

async function loadQuarters() {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/quarters?per_page=50', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('quarters-list');
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(quarter => `
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-mono">${quarter.quarter_number || 'N/A'}</span>
                                <span class="text-xs text-secondary-foreground">${quarter.location || 'N/A'}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="kt-badge kt-badge-${quarter.is_occupied ? 'success' : 'secondary'} kt-badge-sm">
                                ${quarter.is_occupied ? 'Occupied' : 'Available'}
                            </span>
                            ${quarter.officer ? `
                                <span class="text-sm text-secondary-foreground">${quarter.officer.initials} ${quarter.officer.surname}</span>
                            ` : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary-foreground text-center py-8">No quarters found</p>';
            }
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
    }
}
</script>
@endpush
@endsection


