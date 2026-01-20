@extends('layouts.app')

@section('title', 'Promotion Eligibility Lists')
@section('page-title', 'Promotion Eligibility Lists')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-col gap-4" id="promotion-lists">
                <p class="text-secondary-foreground text-center py-8">Loading promotion lists...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadPromotionLists();
});

async function loadPromotionLists() {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/promotion-eligibility-lists?status=SUBMITTED_TO_BOARD&per_page=50', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('promotion-lists');
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(list => `
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-mono">Year ${list.year || 'N/A'} - ${list.officers_count || 0} officers</span>
                                <span class="text-xs text-secondary-foreground">Generated: ${new Date(list.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="/board/promotions/${list.id}" class="kt-btn kt-btn-sm kt-btn-primary">Review & Approve</a>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary-foreground text-center py-8">No promotion lists found</p>';
            }
        }
    } catch (error) {
        console.error('Error loading promotion lists:', error);
    }
}
</script>
@endpush
@endsection


