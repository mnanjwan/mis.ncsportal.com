@extends('layouts.app')

@section('title', 'Request Quarter')
@section('page-title', 'Request Quarter')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Request Quarter</h3>
        </div>
        <div class="kt-card-content">
            <form id="request-quarter-form" class="flex flex-col gap-5">
                <!-- Preferred Quarter Type -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Preferred Quarter Type</label>
                    <select id="preferred-quarter-type" name="preferred_quarter_type" class="kt-select">
                        <option value="">Any</option>
                        <option value="Single Room">Single Room</option>
                        <option value="One Bedroom">One Bedroom</option>
                        <option value="Two Bedroom">Two Bedroom</option>
                        <option value="Three Bedroom">Three Bedroom</option>
                        <option value="Four Bedroom">Four Bedroom</option>
                        <option value="Duplex">Duplex</option>
                        <option value="Bungalow">Bungalow</option>
                        <option value="Other">Other</option>
                    </select>
                    <span class="text-xs text-secondary-foreground">Select your preferred quarter type (optional)</span>
                </div>

                <!-- Specific Quarter (Optional) -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Specific Quarter (Optional)</label>
                    <select id="quarter-id" name="quarter_id" class="kt-select">
                        <option value="">Select a specific quarter (optional)</option>
                    </select>
                    <span class="text-xs text-secondary-foreground">Leave blank to request any available quarter</span>
                </div>

                <!-- Info Message -->
                <div class="kt-alert kt-alert-info">
                    <i class="ki-filled ki-information"></i>
                    <div>
                        <strong>Note:</strong> Your request will be reviewed by Building Unit. You will be notified once a decision is made.
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Submit Request
                    </button>
                    <a href="{{ route('officer.quarter-requests') }}" class="kt-btn kt-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadAvailableQuarters();
    document.getElementById('request-quarter-form').addEventListener('submit', handleSubmit);
});

async function loadAvailableQuarters() {
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            console.error('API token not found');
            return;
        }

        const res = await fetch('/api/v1/quarters?is_occupied=0', {
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            const quarters = data.data || [];
            const select = document.getElementById('quarter-id');
            
            quarters.forEach(quarter => {
                const option = document.createElement('option');
                option.value = quarter.id;
                option.textContent = `${quarter.quarter_number} (${quarter.quarter_type})`;
                select.appendChild(option);
            });
        } else {
            console.error('Failed to load quarters:', data.message);
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
    }
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ki-filled ki-loader"></i> Submitting...';

    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            throw new Error('API token not found');
        }

        const formData = {
            preferred_quarter_type: document.getElementById('preferred-quarter-type').value || null,
            quarter_id: document.getElementById('quarter-id').value || null,
        };

        const res = await fetch('/api/v1/quarters/request', {
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            alert('Quarter request submitted successfully!');
            window.location.href = '{{ route("officer.quarter-requests") }}';
        } else {
            const errorMsg = data.message || 'Failed to submit request';
            
            if (data.meta?.code === 'PENDING_REQUEST_EXISTS') {
                alert('You already have a pending quarter request. Please wait for it to be processed.');
            } else {
                alert(errorMsg);
            }
        }
    } catch (error) {
        console.error('Error submitting request:', error);
        alert('An error occurred while submitting your request');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}
</script>
@endpush




