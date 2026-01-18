@extends('layouts.app')

@section('title', 'Edit PFA')
@section('page-title', 'Edit PFA')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.pfas.index') }}">PFAs</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
@endsection

@section('content')
    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Edit PFA</h3>
        </div>
        <div class="kt-card-content">
            <form method="POST" action="{{ route('accounts.pfas.update', $pfa) }}" class="flex flex-col gap-5 max-w-xl">
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label">PFA Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="kt-input" value="{{ old('name', $pfa->name) }}" required maxlength="255" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">RSA Prefix <span class="text-danger">*</span></label>
                        <input type="text" name="rsa_prefix" class="kt-input" value="{{ old('rsa_prefix', $pfa->rsa_prefix) }}" required maxlength="20" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Digits After Prefix <span class="text-danger">*</span></label>
                        <input type="number" name="rsa_digits" class="kt-input" value="{{ old('rsa_digits', $pfa->rsa_digits) }}" required min="1" max="50" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="kt-checkbox" {{ old('is_active', $pfa->is_active ? '1' : '') ? 'checked' : '' }} />
                    <label for="is_active" class="kt-form-label mb-0">Active</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('accounts.pfas.index') }}" class="kt-btn kt-btn-secondary">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

