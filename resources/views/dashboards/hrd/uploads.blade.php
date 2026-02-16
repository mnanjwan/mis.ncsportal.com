@extends('layouts.app')

@section('title', 'Officer Uploads')
@section('page-title', 'Officer Uploads')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Uploads</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Search Officer Documents</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('hrd.uploads') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Officer Select -->
                        <div class="flex-1 min-w-[300px] w-full md:w-auto">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Select Officer</label>
                            <div class="relative">
                                <input type="hidden" name="officer_id" id="officer_id" value="{{ request('officer_id') ?? '' }}">
                                <button type="button" 
                                        id="officer_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="officer_select_text">
                                        @if($selectedOfficer)
                                            {{ $selectedOfficer->initials }} {{ $selectedOfficer->surname }} ({{ $selectedOfficer->service_number ?? 'No S/N' }})
                                        @else
                                            Select an officer...
                                        @endif
                                    </span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="officer_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="officer_search_input" 
                                               class="kt-input w-full" 
                                               placeholder="Search by name or service number..."
                                               autocomplete="off">
                                    </div>
                                    <div id="officer_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Select -->
                        <div class="w-full md:w-56">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Category</label>
                            <select name="category" class="kt-input w-full">
                                <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>All Categories</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ $selectedCategory === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            <a href="{{ route('hrd.uploads') }}" class="kt-btn kt-btn-secondary">
                                <i class="ki-filled ki-arrows-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedOfficer)
            <!-- Officer Info -->
            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-4">
                        <div class="kt-avatar size-16">
                            <div class="kt-avatar-image">
                                @if($selectedOfficer->getProfilePictureUrlFull())
                                    <img alt="avatar" src="{{ $selectedOfficer->getProfilePictureUrlFull() }}" />
                                @else
                                    <div class="flex items-center justify-center size-16 rounded-full bg-primary/10 text-primary font-bold text-lg">
                                        {{ strtoupper(($selectedOfficer->initials[0] ?? '') . ($selectedOfficer->surname[0] ?? '')) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">{{ $selectedOfficer->initials }} {{ $selectedOfficer->surname }}</h3>
                            <p class="text-sm text-secondary-foreground">
                                Service Number: <span class="font-medium">{{ $selectedOfficer->service_number ?? 'N/A' }}</span>
                                <span class="mx-2">|</span>
                                Rank: <span class="font-medium">{{ $selectedOfficer->display_rank }}</span>
                            </p>
                        </div>
                        <div class="ml-auto">
                            <a href="{{ route('hrd.officers.show', $selectedOfficer->id) }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                                <i class="ki-filled ki-eye"></i> View Full Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents by Category -->
            @php
                $documentsByCategory = $documents->groupBy('document_type');
                $totalDocuments = $documents->count();
            @endphp

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        Documents 
                        <span class="text-secondary-foreground font-normal">({{ $totalDocuments }} total)</span>
                    </h3>
                </div>
                <div class="kt-card-content">
                    @if($totalDocuments > 0)
                        @if($selectedCategory === 'all')
                            <!-- Group by category -->
                            @foreach($categories as $categoryKey => $categoryLabel)
                                @if($documentsByCategory->has($categoryKey))
                                    <div class="mb-6 last:mb-0">
                                        <div class="flex items-center gap-2 mb-3 cursor-pointer category-header" data-category="{{ $categoryKey }}">
                                            <i class="ki-filled ki-down text-sm transition-transform category-arrow" id="arrow-{{ $categoryKey }}"></i>
                                            <h4 class="text-base font-semibold">{{ $categoryLabel }}</h4>
                                            <span class="bg-primary/10 text-primary text-xs px-2 py-0.5 rounded-full">
                                                {{ $documentsByCategory[$categoryKey]->count() }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 category-content" id="content-{{ $categoryKey }}">
                                            @foreach($documentsByCategory[$categoryKey] as $doc)
                                                @include('dashboards.hrd.partials.document-card', ['doc' => $doc])
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <!-- Uncategorized documents (old documents with file extension as type) -->
                            @php
                                $knownCategories = array_keys($categories);
                                $uncategorized = $documents->filter(function($doc) use ($knownCategories) {
                                    return !in_array($doc->document_type, $knownCategories);
                                });
                            @endphp
                            @if($uncategorized->count() > 0)
                                <div class="mb-6 last:mb-0">
                                    <div class="flex items-center gap-2 mb-3 cursor-pointer category-header" data-category="uncategorized">
                                        <i class="ki-filled ki-down text-sm transition-transform category-arrow" id="arrow-uncategorized"></i>
                                        <h4 class="text-base font-semibold">Uncategorized</h4>
                                        <span class="bg-warning/10 text-warning text-xs px-2 py-0.5 rounded-full">
                                            {{ $uncategorized->count() }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 category-content" id="content-uncategorized">
                                        @foreach($uncategorized as $doc)
                                            @include('dashboards.hrd.partials.document-card', ['doc' => $doc])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Single category filtered -->
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($documents as $doc)
                                    @include('dashboards.hrd.partials.document-card', ['doc' => $doc])
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-folder text-4xl text-secondary-foreground mb-3"></i>
                            <p class="text-secondary-foreground">
                                @if($selectedCategory !== 'all')
                                    No documents found in the "{{ $categories[$selectedCategory] ?? $selectedCategory }}" category.
                                @else
                                    No documents found for this officer.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- No Officer Selected -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="text-center py-12">
                        <i class="ki-filled ki-folder-up text-5xl text-secondary-foreground mb-4"></i>
                        <h3 class="text-lg font-semibold mb-2">Select an Officer</h3>
                        <p class="text-secondary-foreground">
                            Use the search above to select an officer and view their uploaded documents organized by category.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Document Preview Modal -->
    <div class="kt-modal" data-kt-modal="true" id="document-preview-modal">
        <div class="kt-modal-content max-w-[800px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="kt-modal-title" id="modal-document-title">Document Preview</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-close="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body p-5">
                <div id="modal-document-content" class="flex items-center justify-center min-h-[300px]">
                    <!-- Image or file info will be inserted here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Officer searchable select
        const officers = @json($officers);
        const officerTrigger = document.getElementById('officer_select_trigger');
        const officerDropdown = document.getElementById('officer_dropdown');
        const officerSearch = document.getElementById('officer_search_input');
        const officerOptions = document.getElementById('officer_options');
        const officerHidden = document.getElementById('officer_id');
        const officerText = document.getElementById('officer_select_text');

        function renderOfficerOptions(filter = '') {
            const filtered = officers.filter(o => {
                const name = `${o.initials || ''} ${o.surname || ''}`.toLowerCase();
                const sn = (o.service_number || '').toLowerCase();
                const search = filter.toLowerCase();
                return name.includes(search) || sn.includes(search);
            });

            officerOptions.innerHTML = filtered.slice(0, 50).map(o => `
                <div class="px-4 py-2 hover:bg-muted cursor-pointer officer-option" data-id="${o.id}" data-text="${o.initials || ''} ${o.surname || ''} (${o.service_number || 'No S/N'})">
                    <div class="font-medium">${o.initials || ''} ${o.surname || ''}</div>
                    <div class="text-xs text-secondary-foreground">${o.service_number || 'No Service Number'}</div>
                </div>
            `).join('');

            if (filtered.length === 0) {
                officerOptions.innerHTML = '<div class="px-4 py-3 text-secondary-foreground text-center">No officers found</div>';
            }

            // Add click handlers
            document.querySelectorAll('.officer-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    officerHidden.value = this.dataset.id;
                    officerText.textContent = this.dataset.text;
                    officerDropdown.classList.add('hidden');
                });
            });
        }

        officerTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            officerDropdown.classList.toggle('hidden');
            if (!officerDropdown.classList.contains('hidden')) {
                officerSearch.focus();
                renderOfficerOptions();
            }
        });

        officerSearch.addEventListener('input', function() {
            renderOfficerOptions(this.value);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!officerTrigger.contains(e.target) && !officerDropdown.contains(e.target)) {
                officerDropdown.classList.add('hidden');
            }
        });

        // Category collapse/expand
        document.querySelectorAll('.category-header').forEach(header => {
            header.addEventListener('click', function() {
                const category = this.dataset.category;
                const content = document.getElementById('content-' + category);
                const arrow = document.getElementById('arrow-' + category);
                
                if (content) {
                    content.classList.toggle('hidden');
                    arrow.classList.toggle('-rotate-90');
                }
            });
        });

        // Document preview modal
        const modal = document.getElementById('document-preview-modal');
        const modalTitle = document.getElementById('modal-document-title');
        const modalContent = document.getElementById('modal-document-content');

        document.querySelectorAll('.document-preview-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const url = this.dataset.url;
                const name = this.dataset.name;
                const isImage = this.dataset.isImage === '1';

                modalTitle.textContent = name;

                if (isImage && url) {
                    modalContent.innerHTML = `<img src="${url}" alt="${name}" class="max-w-full max-h-[500px] object-contain">`;
                } else {
                    modalContent.innerHTML = `
                        <div class="text-center">
                            <i class="ki-filled ki-file text-5xl text-primary mb-4"></i>
                            <p class="text-secondary-foreground mb-4">${name}</p>
                            <a href="${url}" target="_blank" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-eye"></i> Open File
                            </a>
                        </div>
                    `;
                }

                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                }
            });
        });
    });
</script>
@endpush
