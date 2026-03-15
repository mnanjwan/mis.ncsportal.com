@extends('layouts.app')

@section('title', 'Document Category Settings')
@section('page-title', 'Document Category Settings')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Document Category Settings</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @php
        $totalCount = $categories->count();
        $inactiveCount = $categories->where('is_active', false)->count();
        $recruitVisibleCount = $categories->filter(fn($c) => $c->is_active && in_array($c->applies_to, ['recruit', 'both'], true))->count();
        $officerVisibleCount = $categories->filter(fn($c) => $c->is_active && in_array($c->applies_to, ['officer', 'both'], true))->count();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="kt-card">
            <div class="kt-card-content p-4">
                <div class="text-xs text-secondary-foreground">Total Categories</div>
                <div class="text-2xl font-semibold">{{ $totalCount }}</div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content p-4">
                <div class="text-xs text-secondary-foreground">Recruit Onboarding Visible</div>
                <div class="text-2xl font-semibold">{{ $recruitVisibleCount }}</div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content p-4">
                <div class="text-xs text-secondary-foreground">Regular Officer Onboarding Visible</div>
                <div class="text-2xl font-semibold">{{ $officerVisibleCount }}</div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content p-4">
                <div class="text-xs text-secondary-foreground">Inactive</div>
                <div class="text-2xl font-semibold">{{ $inactiveCount }}</div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Where Categories Are Used</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="p-3 border border-input rounded-md bg-muted/20">
                    <div class="font-medium mb-1">Recruit Flow</div>
                    <div class="text-secondary-foreground">Shown in `recruit onboarding step 2` document upload category dropdown.</div>
                </div>
                <div class="p-3 border border-input rounded-md bg-muted/20">
                    <div class="font-medium mb-1">Regular Officer Flow</div>
                    <div class="text-secondary-foreground">Shown in `onboarding step 4` document upload category dropdown.</div>
                </div>
                <div class="p-3 border border-input rounded-md bg-muted/20 md:col-span-2">
                    <div class="font-medium mb-1">HRD Upload Browser</div>
                    <div class="text-secondary-foreground">Used as category filters and labels in `HRD Uploads` page.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Create Document Category</h3>
        </div>
        <div class="kt-card-content">
            <form method="POST" action="{{ route('hrd.document-categories.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div class="md:col-span-1">
                    <label class="kt-form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="kt-input" value="{{ old('name') }}" placeholder="e.g. Medical Fitness Certificate" required>
                    @error('name')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-1">
                    <label class="kt-form-label">Applies To <span class="text-danger">*</span></label>
                    <select name="applies_to" class="kt-input" required>
                        <option value="both" {{ old('applies_to') === 'both' ? 'selected' : '' }}>Both (Recruit + Regular Officer)</option>
                        <option value="recruit" {{ old('applies_to') === 'recruit' ? 'selected' : '' }}>Recruit</option>
                        <option value="officer" {{ old('applies_to') === 'officer' ? 'selected' : '' }}>Regular Officer</option>
                    </select>
                    @error('applies_to')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-1 flex items-end gap-3">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" class="kt-checkbox" checked>
                        <span class="text-sm">Active</span>
                    </label>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Manage Existing Categories</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                <div class="md:col-span-2">
                    <label class="kt-form-label">Search</label>
                    <input type="text" id="category-search" class="kt-input" placeholder="Search by name or key...">
                </div>
                <div>
                    <label class="kt-form-label">Applies To</label>
                    <select id="category-audience-filter" class="kt-input">
                        <option value="all">All</option>
                        <option value="recruit">Recruit</option>
                        <option value="officer">Regular Officer</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" id="category-active-only" class="kt-checkbox">
                        <span class="text-sm">Active only</span>
                    </label>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="kt-table w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Key</th>
                            <th>Applies To</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr class="category-row"
                                data-name="{{ strtolower($category->name) }}"
                                data-key="{{ strtolower($category->key) }}"
                                data-applies-to="{{ $category->applies_to }}"
                                data-active="{{ $category->is_active ? '1' : '0' }}">
                                <td colspan="5" class="p-0 border-0">
                                    <form method="POST" action="{{ route('hrd.document-categories.update', $category->id) }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 p-3 border-b border-input items-center">
                                        @csrf
                                        @method('PUT')
                                        <div class="md:col-span-3">
                                            <input type="text" name="name" class="kt-input" value="{{ $category->name }}" required>
                                        </div>
                                        <div class="md:col-span-3">
                                            <input type="text" class="kt-input bg-muted/40" value="{{ $category->key }}" readonly>
                                        </div>
                                        <div class="md:col-span-2">
                                            <select name="applies_to" class="kt-input" required>
                                                <option value="both" {{ $category->applies_to === 'both' ? 'selected' : '' }}>Both</option>
                                                <option value="recruit" {{ $category->applies_to === 'recruit' ? 'selected' : '' }}>Recruit</option>
                                                <option value="officer" {{ $category->applies_to === 'officer' ? 'selected' : '' }}>Regular Officer</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="inline-flex items-center gap-2">
                                                <input type="checkbox" name="is_active" value="1" class="kt-checkbox" {{ $category->is_active ? 'checked' : '' }}>
                                                <span class="text-sm">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                                            </label>
                                        </div>
                                        <div class="md:col-span-2 text-right">
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                                                <i class="ki-filled ki-check"></i> Save
                                            </button>
                                        </div>
                                        <div class="md:col-span-12 text-xs text-secondary-foreground">
                                            Visible in:
                                            @if($category->applies_to === 'recruit')
                                                Recruit onboarding only
                                            @elseif($category->applies_to === 'officer')
                                                Regular officer onboarding only
                                            @else
                                                Recruit + Regular officer onboarding
                                            @endif
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-secondary-foreground">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('category-search');
    const audienceFilter = document.getElementById('category-audience-filter');
    const activeOnly = document.getElementById('category-active-only');
    const rows = Array.from(document.querySelectorAll('.category-row'));

    function applyFilters() {
        const term = (searchInput?.value || '').trim().toLowerCase();
        const audience = audienceFilter?.value || 'all';
        const active = activeOnly?.checked || false;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const key = row.dataset.key || '';
            const appliesTo = row.dataset.appliesTo || 'both';
            const isActive = row.dataset.active === '1';

            const matchesTerm = term === '' || name.includes(term) || key.includes(term);
            const matchesAudience = audience === 'all' || appliesTo === audience;
            const matchesActive = !active || isActive;

            row.style.display = (matchesTerm && matchesAudience && matchesActive) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', applyFilters);
    audienceFilter?.addEventListener('change', applyFilters);
    activeOnly?.addEventListener('change', applyFilters);
});
</script>
@endpush
