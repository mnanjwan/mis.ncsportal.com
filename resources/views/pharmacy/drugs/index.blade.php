@extends('layouts.app')

@section('title', 'Drug Catalog')
@section('page-title', 'Drug Catalog')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Drugs</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('success'))
            <div class="kt-alert kt-alert-success">
                <i class="ki-filled ki-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-grow">
                        <label class="kt-label text-xs">Search</label>
                        <input type="text" name="search" class="kt-input kt-input-sm" value="{{ $search }}" placeholder="Search drug name...">
                    </div>
                    <div>
                        <label class="kt-label text-xs">Category</label>
                        <select name="category" class="kt-input kt-input-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_inactive" value="1" {{ $showInactive ? 'checked' : '' }} class="kt-checkbox">
                            <span class="text-sm">Show Inactive</span>
                        </label>
                    </div>
                    <div>
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                    </div>
                    @if(auth()->user()->hasRole('OC Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                        <div class="ml-auto">
                            <a href="{{ route('pharmacy.drugs.create') }}" class="kt-btn kt-btn-sm kt-btn-success">
                                <i class="ki-filled ki-plus"></i> Add Drug
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Drug List -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Drug Catalog</h3>
            </div>
            <div class="kt-card-content">
                @if($drugs->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Unit of Measure</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($drugs as $index => $drug)
                                    <tr class="{{ !$drug->is_active ? 'opacity-50' : '' }}">
                                        <td>{{ $drugs->firstItem() + $index }}</td>
                                        <td class="font-medium">{{ $drug->name }}</td>
                                        <td>{{ $drug->category ?? '-' }}</td>
                                        <td>{{ $drug->unit_of_measure }}</td>
                                        <td>
                                            <span class="kt-badge kt-badge-{{ $drug->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $drug->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('pharmacy.stocks.show', $drug->id) }}" class="kt-btn kt-btn-sm kt-btn-light" title="View Stock">
                                                    <i class="ki-filled ki-package"></i>
                                                </a>
                                                @if(auth()->user()->hasRole('OC Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                                                    <a href="{{ route('pharmacy.drugs.edit', $drug->id) }}" class="kt-btn kt-btn-sm kt-btn-light" title="Edit">
                                                        <i class="ki-filled ki-notepad-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $drugs->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-pill text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No drugs found in the catalog.</p>
                        @if(auth()->user()->hasRole('OC Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                            <a href="{{ route('pharmacy.drugs.create') }}" class="kt-btn kt-btn-primary mt-4">
                                Add First Drug
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
