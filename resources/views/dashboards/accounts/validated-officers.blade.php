@extends('layouts.app')

@section('title', 'Validated Officers')
@section('page-title', 'Validated Officers')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-col gap-4" id="validated-officers-list">
                <p class="text-secondary-foreground text-center py-8">Loading validated officers...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadValidatedOfficers();
});

async function loadValidatedOfficers() {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/emoluments?status=VALIDATED&per_page=50', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('validated-officers-list');
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(emol => `
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-mono">${emol.officer?.initials || ''} ${emol.officer?.surname || ''}</span>
                                <span class="text-xs text-secondary-foreground">SVC: ${emol.officer?.service_number || 'N/A'}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <button onclick="processPayment(${emol.id})" class="kt-btn kt-btn-sm kt-btn-primary">Process Payment</button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary-foreground text-center py-8">No validated officers found</p>';
            }
        }
    } catch (error) {
        console.error('Error loading validated officers:', error);
    }
}

async function processPayment(emolumentId) {
    if (!confirm('Process payment for this officer?')) return;
    const token = window.API_CONFIG.token;
    try {
        const res = await fetch(`/api/v1/emoluments/${emolumentId}/process-payment`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (res.ok) {
            alert('Payment processed!');
            loadValidatedOfficers();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error processing payment');
    }
}

window.processPayment = processPayment;
</script>
@endpush
@endsection


