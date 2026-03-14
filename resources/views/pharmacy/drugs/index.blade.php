@extends('layouts.app')

@section('title', 'Drug / Item Catalog')
@section('page-title', 'Drug / Item Catalog')
@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="#">Pharmacy</a>
    <span>/</span>
    <span class="text-primary">Drugs / Items</span>
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

        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-grow min-w-[200px]">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                        <input type="text" name="search" class="kt-input" value="{{ $search }}" placeholder="Search drug / item name...">
                    </div>
                    <div class="w-full sm:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Category</label>
                        <select name="category" class="kt-input shadow-none">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center pb-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_inactive" value="1" {{ $showInactive ? 'checked' : '' }} class="kt-checkbox">
                            <span class="text-sm font-medium text-foreground">Show Inactive</span>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                    </div>
                    @if(auth()->user()->hasRole('OC Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                        <div class="ml-auto">
                            <a href="{{ route('pharmacy.drugs.create') }}" class="kt-btn kt-btn-success">
                                <i class="ki-filled ki-plus"></i> Add New
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Drug List -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Drug / Item Catalog</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($drugs->count() > 0)
                    <!-- Swipe hint for mobile -->
                    <div class="px-5 pb-5 lg:hidden">
                        <div class="flex items-center gap-2 text-xs text-secondary-foreground bg-secondary/5 p-2 rounded">
                            <i class="ki-filled ki-information-2 text-primary"></i>
                            <span>Swipe left to view more columns</span>
                        </div>
                    </div>

                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">#</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Name</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Category</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Unit of Measure</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($drugs as $index => $drug)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors {{ !$drug->is_active ? 'opacity-60' : '' }}">
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $drugs->firstItem() + $index }}</td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $drug->name }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $drug->category ?? '-' }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $drug->unit_of_measure }}</td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $drug->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $drug->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('pharmacy.stocks.show', $drug->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost" title="View Stock">
                                                    <i class="ki-filled ki-package"></i> View Stock
                                                </a>
                                                @if(auth()->user()->hasRole('OC Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                                                    <a href="{{ route('pharmacy.drugs.edit', $drug->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost" title="Edit">
                                                        <i class="ki-filled ki-notepad-edit"></i> Edit
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-5 pb-5">
                        {{ $drugs->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-pill text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No drugs / items found in the catalog.</p>
                        @if(auth()->user()->hasRole('OC Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                            <a href="{{ route('pharmacy.drugs.create') }}" class="kt-btn kt-btn-primary mt-4">
                                Add First Drug / Item
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection
