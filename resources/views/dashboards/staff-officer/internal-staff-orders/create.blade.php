@extends('layouts.app')

@section('title', 'Create Internal Staff Order')
@section('page-title', 'Create Internal Staff Order')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.internal-staff-orders.index') }}">Internal Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('staff-officer.internal-staff-orders.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Internal Staff Orders
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
                <h3 class="kt-card-title">Create Internal Staff Order</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('staff-officer.internal-staff-orders.store') }}" method="POST">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Command Info (Read-only) -->
                        @if($command)
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Command</label>
                                <input type="text" 
                                       class="kt-input" 
                                       value="{{ $command->name }}" 
                                       readonly>
                                <span class="text-xs text-secondary-foreground">Internal staff orders are created for your assigned command.</span>
                            </div>
                        @endif

                        <!-- Order Number (Auto-generated, but editable) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number <span class="text-danger">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       name="order_number" 
                                       id="order_number"
                                       class="kt-input flex-1" 
                                       value="{{ old('order_number', $orderNumber) }}"
                                       placeholder="Auto-generated order number"
                                       readonly
                                       required>
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

                        <!-- Order Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="order_date" 
                                   id="order_date"
                                   class="kt-input" 
                                   value="{{ old('order_date', date('Y-m-d')) }}"
                                   required>
                            @error('order_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Description</label>
                            <textarea name="description" 
                                      id="description"
                                      class="kt-input" 
                                      rows="5"
                                      placeholder="Enter order description or details...">{{ old('description') }}</textarea>
                            <span class="text-xs text-secondary-foreground">Optional: Provide additional details about this internal staff order.</span>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center gap-3 pt-4 border-t border-border">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Create Internal Staff Order
                            </button>
                            <a href="{{ route('staff-officer.internal-staff-orders.index') }}" class="kt-btn kt-btn-ghost">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Allow editing order number
        document.getElementById('edit-order-number').addEventListener('click', function() {
            const orderNumberInput = document.getElementById('order_number');
            orderNumberInput.removeAttribute('readonly');
            orderNumberInput.focus();
            this.style.display = 'none';
        });
    </script>
@endsection

