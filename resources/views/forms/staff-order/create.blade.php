@extends('layouts.app')

@section('title', 'Create Staff Order')
@section('page-title', 'Create Staff Order')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.staff-orders') }}">Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.staff-orders') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Staff Orders
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Create Staff Order</h3>
            </div>
            <div class="kt-card-content">
                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('hrd.staff-orders.store') }}" method="POST" id="staff-order-form">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Order Number (Auto-generated, but editable) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number</label>
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       name="order_number" 
                                       id="order_number"
                                       class="kt-input flex-1" 
                                       value="{{ old('order_number', $orderNumber) }}"
                                       placeholder="Auto-generated order number"
                                       readonly>
                                <button type="button" 
                                        id="edit-order-number"
                                        class="kt-btn kt-btn-sm kt-btn-ghost"
                                        title="Edit order number">
                                    <i class="ki-filled ki-pencil"></i>
                                </button>
                            </div>
                            <span class="text-xs text-secondary-foreground">Order number is auto-generated. Click edit to customize.</span>
                            @error('order_number')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Officer Selection -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Officer <span class="text-danger">*</span></label>
                            <select name="officer_id" 
                                    id="officer_id" 
                                    class="kt-input" 
                                    required
                                    onchange="updateFromCommand(this)">
                                <option value="">Select Officer</option>
                                @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}" 
                                            data-command-id="{{ $officer->present_station }}"
                                            data-command-name="{{ $officer->presentStation->name ?? 'N/A' }}"
                                            {{ old('officer_id') == $officer->id ? 'selected' : '' }}>
                                        {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }} 
                                        - {{ $officer->service_number ?? 'N/A' }}
                                        @if($officer->presentStation)
                                            ({{ $officer->presentStation->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-xs text-secondary-foreground mt-1">
                                The "From Command" will be auto-filled when you select an officer
                            </span>
                            @error('officer_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- From Command (Auto-filled from officer) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">From Command <span class="text-danger">*</span></label>
                            <select name="from_command_id" 
                                    id="from_command_id" 
                                    class="kt-input" 
                                    required>
                                <option value="">Select Command (will auto-fill when officer is selected)</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" 
                                            {{ old('from_command_id') == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                        @if($command->zone)
                                            ({{ $command->zone->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('from_command_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- To Command -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">To Command <span class="text-danger">*</span></label>
                            <select name="to_command_id" 
                                    id="to_command_id" 
                                    class="kt-input" 
                                    required>
                                <option value="">Select Command</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" 
                                            {{ old('to_command_id') == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                        @if($command->zone)
                                            ({{ $command->zone->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('to_command_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Effective Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="effective_date" 
                                   class="kt-input" 
                                   value="{{ old('effective_date') }}"
                                   required>
                            @error('effective_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Order Type -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Type</label>
                            <select name="order_type" class="kt-input">
                                <option value="">Select Order Type</option>
                                <option value="POSTING" {{ old('order_type') == 'POSTING' ? 'selected' : '' }}>Posting</option>
                                <option value="TRANSFER" {{ old('order_type') == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                                <option value="DEPLOYMENT" {{ old('order_type') == 'DEPLOYMENT' ? 'selected' : '' }}>Deployment</option>
                                <option value="REASSIGNMENT" {{ old('order_type') == 'REASSIGNMENT' ? 'selected' : '' }}>Reassignment</option>
                            </select>
                            @error('order_type')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <select name="status" class="kt-input">
                                <option value="DRAFT" {{ old('status', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="PUBLISHED" {{ old('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                            </select>
                            <span class="text-xs text-secondary-foreground mt-1">
                                <strong>Draft:</strong> Order is saved but not yet effective. <br>
                                <strong>Published:</strong> Order becomes effective immediately and triggers workflow automation.
                            </span>
                            @error('status')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Description</label>
                            <textarea name="description" 
                                      class="kt-input" 
                                      rows="4"
                                      placeholder="Enter order description (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('hrd.staff-orders') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Create Staff Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Order Number Edit Toggle
        document.getElementById('edit-order-number').addEventListener('click', function() {
            const input = document.getElementById('order_number');
            if (input.readOnly) {
                input.readOnly = false;
                input.focus();
                this.innerHTML = '<i class="ki-filled ki-check"></i>';
                this.title = 'Lock order number';
            } else {
                input.readOnly = true;
                this.innerHTML = '<i class="ki-filled ki-pencil"></i>';
                this.title = 'Edit order number';
            }
        });

        // Auto-fill From Command when officer is selected
        function updateFromCommand(select) {
            const officerId = select.value;
            if (officerId) {
                const selectedOption = select.options[select.selectedIndex];
                const commandId = selectedOption.getAttribute('data-command-id');
                const commandName = selectedOption.getAttribute('data-command-name');
                
                if (commandId) {
                    const fromCommandSelect = document.getElementById('from_command_id');
                    fromCommandSelect.value = commandId;
                }
            }
        }

        // Form validation before submit
        document.getElementById('staff-order-form').addEventListener('submit', function(e) {
            const officerId = document.getElementById('officer_id').value;
            const fromCommandId = document.getElementById('from_command_id').value;
            const toCommandId = document.getElementById('to_command_id').value;
            
            if (!officerId || !fromCommandId || !toCommandId) {
                e.preventDefault();
                let missing = [];
                if (!officerId) missing.push('Officer');
                if (!fromCommandId) missing.push('From Command');
                if (!toCommandId) missing.push('To Command');
                alert('Please select: ' + missing.join(', '));
                return false;
            }
            
            if (fromCommandId === toCommandId) {
                e.preventDefault();
                alert('From Command and To Command cannot be the same.');
                return false;
            }
            
            // Ensure all required fields are set
            if (!document.getElementById('effective_date').value) {
                e.preventDefault();
                alert('Please select an Effective Date');
                return false;
            }
        });
    </script>
    @endpush
@endsection
