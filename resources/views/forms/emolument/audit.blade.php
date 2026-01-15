@extends('layouts.app')

@section('title', 'Audit Emolument')
@section('page-title', 'Audit Emolument')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('auditor.dashboard') }}">Auditor</a>
    <span>/</span>
    <span class="text-primary">Audit Emolument</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Emolument Details -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Emolument Details</h3>
                </div>
                <div class="kt-card-content space-y-4">
                    <!-- Officer Profile Section -->
                    <div class="flex items-center gap-4 p-4 rounded-lg bg-muted/50 border border-input mb-4">
                        <div class="kt-avatar size-20 cursor-pointer hover:ring-2 hover:ring-primary/50 transition-all" onclick="openProfileModal()">
                            <div class="kt-avatar-image">
                                @if($emolument->officer->getProfilePictureUrlFull())
                                    <img alt="avatar" src="{{ $emolument->officer->getProfilePictureUrlFull() }}" class="rounded-full object-cover" />
                                @else
                                    <div class="flex items-center justify-center size-20 rounded-full bg-primary/10 text-primary font-bold text-lg">
                                        {{ strtoupper(($emolument->officer->initials[0] ?? '') . ($emolument->officer->surname[0] ?? '')) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-base font-semibold text-foreground">
                                {{ $emolument->officer->initials }} {{ $emolument->officer->surname }}
                            </span>
                            <span class="text-sm text-secondary-foreground font-mono">
                                SVC: {{ $emolument->officer->service_number }}
                            </span>
                            @if($emolument->officer->getProfilePictureUrlFull())
                                <span class="text-xs text-primary cursor-pointer hover:underline" onclick="openProfileModal()">Click photo to view larger</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Officer Name</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $emolument->officer->initials }} {{ $emolument->officer->surname }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Service Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->officer->service_number }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Bank Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->bank_name }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Account Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->bank_account_number }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">PFA Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->pfa_name }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">RSA PIN</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->rsa_pin }}</span>
                        </div>
                    </div>

                    @if($emolument->notes)
                        <div class="flex flex-col gap-1 pt-4 border-t border-border">
                            <span class="text-sm text-secondary-foreground">Officer Notes</span>
                            <p class="text-sm text-mono">{{ $emolument->notes }}</p>
                        </div>
                    @endif

                    @if($emolument->assessment)
                        <div class="flex flex-col gap-1 pt-4 border-t border-border">
                            <span class="text-sm text-secondary-foreground">Assessment Comments</span>
                            <p class="text-sm text-mono">{{ $emolument->assessment->comments ?? 'No comments' }}</p>
                            <span class="text-xs text-secondary-foreground mt-1">
                                Assessed on: {{ $emolument->assessed_at ? $emolument->assessed_at->format('d/m/Y H:i') : 'N/A' }}
                            </span>
                        </div>
                    @endif

                    @if($emolument->validation)
                        <div class="flex flex-col gap-1 pt-4 border-t border-border">
                            <span class="text-sm text-secondary-foreground">Validation Comments</span>
                            <p class="text-sm text-mono">{{ $emolument->validation->comments ?? 'No comments' }}</p>
                            <span class="text-xs text-secondary-foreground mt-1">
                                Validated on: {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y H:i') : 'N/A' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audit Form -->
            <form class="kt-card" action="{{ route('emolument.process-audit', $emolument->id) }}" method="POST">
                @csrf
                
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="kt-card-header">
                    <h3 class="kt-card-title">Audit Decision</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Decision <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="audit_status" id="audit_status" value="{{ old('audit_status') ?? '' }}" required>
                            <button type="button" 
                                    id="audit_status_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('audit_status') border-red-500 @enderror">
                                <span id="audit_status_select_text">{{ old('audit_status') ? (old('audit_status') === 'APPROVED' ? 'Approve' : 'Reject') : 'Select Decision' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="audit_status_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="audit_status_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search..."
                                           autocomplete="off">
                                </div>
                                <div id="audit_status_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        @error('audit_status')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Comments <span id="comments-required" class="text-danger hidden">*</span></label>
                        <textarea class="kt-input @error('comments') border-red-500 @enderror" name="comments" id="comments" rows="4"
                            placeholder="Enter audit comments (optional for approval, required for rejection)">{{ old('comments') }}</textarea>
                        @error('comments')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('auditor.emoluments') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit" id="submit-btn">
                        Submit Audit
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>

            <script>
                document.getElementById('audit_status')?.addEventListener('change', function() {
                    const commentsRequired = document.getElementById('comments-required');
                    const commentsField = document.getElementById('comments');
                    
                    if (this.value === 'REJECTED') {
                        commentsRequired.classList.remove('hidden');
                        commentsField.setAttribute('required', 'required');
                        commentsField.classList.add('border-warning');
                    } else {
                        commentsRequired.classList.add('hidden');
                        commentsField.removeAttribute('required');
                        commentsField.classList.remove('border-warning');
                    }
                });

                // Trigger on page load if old value exists
                @if(old('audit_status') === 'REJECTED')
                    document.getElementById('audit_status').dispatchEvent(new Event('change'));
                @endif
            </script>
        </div>

        <div class="xl:col-span-1">
            <!-- Guidelines Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Audit Guidelines</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <p class="text-xs text-secondary-foreground">
                            Please perform final audit verification before approving for payment processing.
                        </p>
                        <ul class="text-xs text-secondary-foreground list-disc list-inside space-y-1">
                            <li>Review assessment and validation records</li>
                            <li>Verify all information is accurate</li>
                            <li>Check compliance with audit requirements</li>
                            <li>Final approval before Accounts processing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Photo Modal -->
    <div id="profile-photo-modal" class="kt-modal hidden" data-kt-modal="true">
        <div class="kt-modal-content max-w-2xl">
            <div class="kt-modal-header py-3 px-5 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-foreground">
                    {{ $emolument->officer->initials }} {{ $emolument->officer->surname }} - Profile Photo
                </h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" onclick="closeProfileModal()">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <div class="flex items-center justify-center min-h-[400px]">
                    @if($emolument->officer->getProfilePictureUrlFull())
                        <img src="{{ $emolument->officer->getProfilePictureUrlFull() }}" 
                             alt="Profile Photo" 
                             class="max-w-full max-h-[500px] rounded-lg shadow-lg object-contain" />
                    @else
                        <div class="flex flex-col items-center justify-center gap-4">
                            <div class="flex items-center justify-center size-48 rounded-full bg-primary/10 text-primary font-bold text-6xl">
                                {{ strtoupper(($emolument->officer->initials[0] ?? '') . ($emolument->officer->surname[0] ?? '')) }}
                            </div>
                            <p class="text-secondary-foreground">No profile photo available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function openProfileModal() {
            const modal = document.getElementById('profile-photo-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
                modal.style.visibility = 'visible';
                modal.style.opacity = '1';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeProfileModal() {
            const modal = document.getElementById('profile-photo-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
                modal.style.visibility = 'hidden';
                modal.style.opacity = '0';
                document.body.style.overflow = '';
            }
        }

        // Close modal when clicking outside
        document.getElementById('profile-photo-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProfileModal();
            }
        });

        // Reusable function to create searchable select
        function createSearchableSelect(config) {
            const {
                triggerId,
                hiddenInputId,
                dropdownId,
                searchInputId,
                optionsContainerId,
                displayTextId,
                options,
                displayFn,
                onSelect,
                placeholder = 'Select...',
                searchPlaceholder = 'Search...'
            } = config;

            const trigger = document.getElementById(triggerId);
            const hiddenInput = document.getElementById(hiddenInputId);
            const dropdown = document.getElementById(dropdownId);
            const searchInput = document.getElementById(searchInputId);
            const optionsContainer = document.getElementById(optionsContainerId);
            const displayText = document.getElementById(displayTextId);

            if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) {
                return;
            }

            let selectedOption = null;
            let filteredOptions = [...options];

            // Render options
            function renderOptions(opts) {
                if (opts.length === 0) {
                    optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                    return;
                }

                optionsContainer.innerHTML = opts.map(opt => {
                    const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
                    const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                             data-id="${value}" 
                             data-name="${display}">
                            <div class="text-sm text-foreground">${display}</div>
                        </div>
                    `;
                }).join('');

                // Add click handlers
                optionsContainer.querySelectorAll('.select-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        selectedOption = options.find(o => {
                            const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                            return String(optValue) === String(id);
                        });
                        
                        if (selectedOption || id === '') {
                            hiddenInput.value = id;
                            displayText.textContent = name;
                            dropdown.classList.add('hidden');
                            searchInput.value = '';
                            filteredOptions = [...options];
                            renderOptions(filteredOptions);
                            
                            if (onSelect) onSelect(selectedOption || {id: id, name: name});
                        }
                    });
                });
            }

            // Initial render
            renderOptions(filteredOptions);

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                filteredOptions = options.filter(opt => {
                    const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
                    return String(display).toLowerCase().includes(searchTerm);
                });
                renderOptions(filteredOptions);
            });

            // Toggle dropdown
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Initialize audit status select
        document.addEventListener('DOMContentLoaded', function() {
            const auditStatusOptions = [
                {id: '', name: 'Select Decision'},
                {id: 'APPROVED', name: 'Approve'},
                {id: 'REJECTED', name: 'Reject'}
            ];

            if (document.getElementById('audit_status_select_trigger')) {
                createSearchableSelect({
                    triggerId: 'audit_status_select_trigger',
                    hiddenInputId: 'audit_status',
                    dropdownId: 'audit_status_dropdown',
                    searchInputId: 'audit_status_search_input',
                    optionsContainerId: 'audit_status_options',
                    displayTextId: 'audit_status_select_text',
                    options: auditStatusOptions,
                    placeholder: 'Select Decision',
                    searchPlaceholder: 'Search...'
                });
            }
        });
    </script>

    <style>
        .kt-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        .kt-modal.hidden {
            display: none;
        }
        .kt-modal-content {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-height: 90vh;
            overflow-y: auto;
        }
        .kt-modal-header {
            border-bottom: 1px solid #e5e7eb;
        }
        .kt-modal-body {
            max-height: calc(90vh - 100px);
            overflow-y: auto;
        }
    </style>
@endsection

