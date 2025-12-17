@extends('layouts.app')

@section('title', 'Edit Staff Order')
@section('page-title', 'Edit Staff Order')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.staff-orders') }}">Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.staff-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Order Details
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
                <h3 class="kt-card-title">Edit Staff Order</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.staff-orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-5">
                        <!-- Order Number -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="order_number" 
                                   class="kt-input" 
                                   value="{{ old('order_number', $order->order_number) }}"
                                   placeholder="Enter order number"
                                   required>
                            @error('order_number')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Officer -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Officer <span class="text-danger">*</span></label>
                            <select name="officer_id" class="kt-input" required>
                                <option value="">Select Officer</option>
                                @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}" {{ old('officer_id', $order->officer_id) == $officer->id ? 'selected' : '' }}>
                                        {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }} - {{ $officer->service_number ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('officer_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- From Command -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">From Command <span class="text-danger">*</span></label>
                            <select name="from_command_id" class="kt-input" required>
                                <option value="">Select Command</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ old('from_command_id', $order->from_command_id) == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
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
                            <select name="to_command_id" class="kt-input" required>
                                <option value="">Select Command</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ old('to_command_id', $order->to_command_id) == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
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
                                   value="{{ old('effective_date', $order->effective_date ? $order->effective_date->format('Y-m-d') : '') }}"
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
                                <option value="POSTING" {{ old('order_type', $order->order_type) == 'POSTING' ? 'selected' : '' }}>Posting</option>
                                <option value="TRANSFER" {{ old('order_type', $order->order_type) == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                                <option value="DEPLOYMENT" {{ old('order_type', $order->order_type) == 'DEPLOYMENT' ? 'selected' : '' }}>Deployment</option>
                                <option value="REASSIGNMENT" {{ old('order_type', $order->order_type) == 'REASSIGNMENT' ? 'selected' : '' }}>Reassignment</option>
                            </select>
                            @error('order_type')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <select name="status" class="kt-input">
                                <option value="DRAFT" {{ old('status', $order->status ?? 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="PUBLISHED" {{ old('status', $order->status ?? 'DRAFT') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                                <option value="CANCELLED" {{ old('status', $order->status ?? 'DRAFT') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                            </select>
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
                                      placeholder="Enter order description">{{ old('description', $order->description) }}</textarea>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('hrd.staff-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Update Staff Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

