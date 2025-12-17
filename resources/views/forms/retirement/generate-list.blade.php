@extends('layouts.app')

@section('title', 'Generate Retirement List')
@section('page-title', 'Generate Retirement List')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.retirement-list') }}">Retirement List</a>
    <span>/</span>
    <span class="text-primary">Generate</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
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

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Generate Retirement List</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.retirement-list.store') }}" method="POST">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Year -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Retirement Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="year" 
                                   class="kt-input" 
                                   min="2020" 
                                   max="2100"
                                   value="{{ date('Y') }}"
                                   required>
                            <span class="text-xs text-secondary-foreground">
                                The year for which this retirement list is being generated. 
                                <strong>Note:</strong> The system finds officers who will reach <strong>60 years of age OR 35 years of service</strong> by the end of this year (whichever comes first).
                            </span>
                            @error('year')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end gap-2.5 pt-4">
                            <a href="{{ route('hrd.retirement-list') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Generate Retirement List
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

