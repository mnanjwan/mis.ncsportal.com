@extends('layouts.app')

@section('title', 'Next of KIN Management')
@section('page-title', 'Next of KIN Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Next of KIN</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Current Next of KIN -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Current Next of KIN</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('officer.next-of-kin.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Add Next of KIN
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($nextOfKins->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block">
                        <!-- Table with horizontal scroll wrapper -->
                        <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                            <table class="kt-table" style="min-width: 900px; width: 100%;">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Name</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Relationship</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Phone Number</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Email</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Address</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Primary</th>
                                        <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nextOfKins as $nextOfKin)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                            <td class="py-3 px-4" style="white-space: nowrap;">
                                                <span class="text-sm font-medium text-foreground">{{ $nextOfKin->name }}</span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                                {{ $nextOfKin->relationship }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                                {{ $nextOfKin->phone_number ?? '—' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                                {{ $nextOfKin->email ?? '—' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                                {{ \Illuminate\Support\Str::limit($nextOfKin->address ?? '—', 30) }}
                                            </td>
                                            <td class="py-3 px-4" style="white-space: nowrap;">
                                                @if($nextOfKin->is_primary)
                                                    <span class="kt-badge kt-badge-success kt-badge-sm">Primary</span>
                                                @else
                                                    <span class="text-sm text-secondary-foreground">—</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('officer.next-of-kin.edit', $nextOfKin->id) }}" 
                                                       class="kt-btn kt-btn-sm kt-btn-ghost"
                                                       title="Edit">
                                                        <i class="ki-filled ki-notepad-edit"></i>
                                                    </a>
                                                    <button 
                                                        onclick="showDeleteModal({{ $nextOfKin->id }}, '{{ $nextOfKin->name }}')"
                                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger" 
                                                        title="Delete">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="flex flex-col gap-4">
                            @foreach($nextOfKins as $nextOfKin)
                                <div class="p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                                <i class="ki-filled ki-people text-info text-xl"></i>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <span class="text-sm font-semibold text-foreground">
                                                    {{ $nextOfKin->name }}
                                                </span>
                                                <span class="text-xs text-secondary-foreground">
                                                    {{ $nextOfKin->relationship }}
                                                </span>
                                                @if($nextOfKin->is_primary)
                                                    <span class="kt-badge kt-badge-success kt-badge-sm w-fit">Primary</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 gap-3 mb-3">
                                        @if($nextOfKin->phone_number)
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-medium text-secondary-foreground">Phone Number</span>
                                                <span class="text-sm text-foreground">{{ $nextOfKin->phone_number }}</span>
                                            </div>
                                        @endif
                                        @if($nextOfKin->email)
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-medium text-secondary-foreground">Email</span>
                                                <span class="text-sm text-foreground">{{ $nextOfKin->email }}</span>
                                            </div>
                                        @endif
                                        @if($nextOfKin->address)
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-medium text-secondary-foreground">Address</span>
                                                <span class="text-sm text-foreground">{{ $nextOfKin->address }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex justify-end gap-2 pt-3 border-t border-border">
                                        <a href="{{ route('officer.next-of-kin.edit', $nextOfKin->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           title="Edit">
                                            <i class="ki-filled ki-notepad-edit"></i>
                                            Edit
                                        </a>
                                        <button 
                                            onclick="showDeleteModal({{ $nextOfKin->id }}, '{{ $nextOfKin->name }}')"
                                            class="kt-btn kt-btn-sm kt-btn-ghost text-danger" 
                                            title="Delete">
                                            <i class="ki-filled ki-trash"></i>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 px-4">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No Next of KIN records found</p>
                        <a href="{{ route('officer.next-of-kin.create') }}" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Add Next of KIN
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Change Requests -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Change Requests</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($requests->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block">
                        <!-- Table with horizontal scroll wrapper -->
                        <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                            <table class="kt-table" style="min-width: 800px; width: 100%;">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Request Date</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Action</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Name</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Verified At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                            <td class="py-3 px-4 text-sm text-foreground" style="white-space: nowrap;">
                                                {{ $request->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-3 px-4" style="white-space: nowrap;">
                                                <span class="kt-badge kt-badge-{{ $request->action_type === 'add' ? 'success' : ($request->action_type === 'edit' ? 'info' : 'danger') }} kt-badge-sm">
                                                    {{ strtoupper($request->action_type) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-foreground" style="white-space: nowrap;">
                                                {{ $request->name }}
                                            </td>
                                            <td class="py-3 px-4" style="white-space: nowrap;">
                                                <span class="kt-badge kt-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'danger' : 'warning') }} kt-badge-sm">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                                {{ $request->verified_at ? $request->verified_at->format('d/m/Y H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="flex flex-col gap-4">
                            @foreach($requests as $request)
                                <div class="p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                                <i class="ki-filled ki-information-2 text-info text-xl"></i>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <span class="text-sm font-semibold text-foreground">
                                                    {{ $request->name }}
                                                </span>
                                                <span class="text-xs text-secondary-foreground">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="kt-badge kt-badge-{{ $request->action_type === 'add' ? 'success' : ($request->action_type === 'edit' ? 'info' : 'danger') }} kt-badge-sm">
                                                {{ strtoupper($request->action_type) }}
                                            </span>
                                            <span class="kt-badge kt-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'danger' : 'warning') }} kt-badge-sm">
                                                {{ $request->status }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($request->verified_at)
                                        <div class="pt-3 border-t border-border">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-medium text-secondary-foreground">Verified At</span>
                                                <span class="text-sm text-foreground">
                                                    {{ $request->verified_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($requests->hasPages())
                        <div class="mt-6 pt-4 border-t border-border px-4">
                            {{ $requests->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12 px-4">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No change requests found</p>
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

    <!-- Delete Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="delete-confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                        <i class="ki-filled ki-information text-danger text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Deletion</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to delete this Next of KIN? This will create a deletion request that needs to be verified by Welfare.
                </p>
                <p class="text-sm font-semibold text-foreground mt-2" id="delete-kin-name"></p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <form action="" method="POST" class="inline" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-trash"></i>
                        <span>Delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showDeleteModal(id, name) {
                document.getElementById('delete-kin-name').textContent = name;
                document.getElementById('deleteForm').action = `/officer/next-of-kin/${id}`;
                
                const modal = document.getElementById('delete-confirm-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }
        </script>
    @endpush
@endsection

