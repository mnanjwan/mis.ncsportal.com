@extends('layouts.app')

@section('title', 'Create Quarter')
@section('page-title', 'Create Quarter')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Create New Quarter</h3>
        </div>
        <div class="kt-card-content">
            <form id="create-quarter-form" class="flex flex-col gap-5">
                <!-- Command Selection (Readonly - Building Unit's Command) -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Command <span class="text-danger">*</span></label>
                    @if($commandId)
                        <input type="text" 
                               id="command-display" 
                               class="kt-input bg-muted/50 cursor-not-allowed" 
                               value="{{ $commandName ?? 'N/A' }}"
                               readonly
                               disabled>
                        <input type="hidden" 
                               id="command-id" 
                               name="command_id" 
                               value="{{ $commandId }}">
                        <span class="text-xs text-secondary-foreground">Command is automatically set based on your Building Unit assignment</span>
                    @else
                        <div class="kt-alert kt-alert-warning">
                            <i class="ki-filled ki-information"></i>
                            <div>
                                <strong>No Command Assigned:</strong> You must be assigned to a command to create quarters. Please contact HRD.
                            </div>
                        </div>
                        <input type="hidden" id="command-id" name="command_id" value="">
                    @endif
                </div>

                <!-- Quarter Number -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Quarter Number <span class="text-danger">*</span></label>
                    <input type="text" id="quarter-number" name="quarter_number" 
                        class="kt-input" 
                        placeholder="e.g., Q001, Block A-101"
                        maxlength="50"
                        required />
                    <span class="text-xs text-secondary-foreground">Enter a unique quarter number or identifier</span>
                </div>

                <!-- Quarter Type -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Quarter Type <span class="text-danger">*</span></label>
                    <select id="quarter-type" name="quarter_type" class="kt-select" required>
                        <option value="">Select type</option>
                        <option value="Single Room">Single Room</option>
                        <option value="One Bedroom">One Bedroom</option>
                        <option value="Two Bedroom">Two Bedroom</option>
                        <option value="Three Bedroom">Three Bedroom</option>
                        <option value="Four Bedroom">Four Bedroom</option>
                        <option value="Duplex">Duplex</option>
                        <option value="Bungalow">Bungalow</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Custom Type Input (if Other selected) -->
                <div class="flex flex-col gap-2" id="custom-type-container" style="display: none;">
                    <label class="kt-form-label">Specify Type <span class="text-danger">*</span></label>
                    <input type="text" id="custom-quarter-type" name="custom_quarter_type" 
                        class="kt-input" 
                        placeholder="Enter quarter type" />
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Create Quarter
                    </button>
                    <a href="{{ route('building.quarters') }}" class="kt-btn kt-btn-secondary">
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
    @if(!$commandId)
        // Disable form if no command assigned
        document.getElementById('create-quarter-form').querySelectorAll('input, select, button[type="submit"]').forEach(el => {
            el.disabled = true;
        });
    @endif
    
    document.getElementById('quarter-type').addEventListener('change', (e) => {
        const customContainer = document.getElementById('custom-type-container');
        if (e.target.value === 'Other') {
            customContainer.style.display = 'block';
            document.getElementById('custom-quarter-type').required = true;
        } else {
            customContainer.style.display = 'none';
            document.getElementById('custom-quarter-type').required = false;
        }
    });
    
    document.getElementById('create-quarter-form').addEventListener('submit', handleSubmit);
});

async function handleSubmit(e) {
    e.preventDefault();
    
    const commandId = document.getElementById('command-id').value;
    const quarterNumber = document.getElementById('quarter-number').value.trim();
    let quarterType = document.getElementById('quarter-type').value;
    
    if (quarterType === 'Other') {
        quarterType = document.getElementById('custom-quarter-type').value.trim();
        if (!quarterType) {
            showError('Please specify the quarter type');
            return;
        }
    }
    
    @if(!$commandId)
        showError('You must be assigned to a command to create quarters. Please contact HRD.');
        return;
    @endif
    
    if (!commandId) {
        showError('Command is required');
        return;
    }
    
    if (!quarterNumber) {
        showError('Please enter a quarter number');
        return;
    }
    
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/quarters', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                command_id: parseInt(commandId),
                quarter_number: quarterNumber,
                quarter_type: quarterType
            })
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            showSuccess('Quarter created successfully!');
            setTimeout(() => {
                window.location.href = '{{ route("building.quarters") }}';
            }, 1500);
        } else {
            const errorMsg = data.message || 'Failed to create quarter';
            console.error('API Error:', errorMsg);
            showError(errorMsg);
        }
    } catch (error) {
        console.error('Error creating quarter:', error);
        showError('Error creating quarter. Please try again.');
    }
}

function showSuccess(message) {
    const notification = document.createElement('div');
    notification.className = 'kt-card bg-success/10 border border-success/20 mb-4';
    notification.innerHTML = `
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success font-medium">${message}</p>
            </div>
        </div>
    `;
    
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        setTimeout(() => notification.remove(), 5000);
    } else {
        alert(message);
    }
}

function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'kt-card bg-danger/10 border border-danger/20 mb-4';
    notification.innerHTML = `
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger font-medium">${message}</p>
            </div>
        </div>
    `;
    
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        setTimeout(() => notification.remove(), 5000);
    } else {
        alert(message);
    }
}
</script>
@endpush
@endsection

