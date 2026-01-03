@extends('layouts.app')

@section('title', 'Role Assignments')
@section('page-title', 'Role Assignments Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.dashboard') }}">Admin</a>
    <span>/</span>
    <span class="text-primary">Role Assignments</span>
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
        <!-- Command Info -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information-2 text-info text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-info">Managing role assignments for: <strong>{{ $adminCommand->name }}</strong></p>
                        <p class="text-xs text-secondary-foreground mt-1">You can assign: Staff Officer, Area Controller, and DC Admin roles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Role Assignments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('admin.role-assignments') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 w-full md:min-w-[250px]">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="kt-input w-full" 
                                       placeholder="Search by name or service number...">
                            </div>
                        </div>

                        <!-- Role Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Role</label>
                            <select name="role_id" class="kt-input w-full">
                                <option value="">All Roles</option>
                                @foreach($assignableRoles as $role)
                                    <option value="{{ $role->id }}" {{ (string)request('role_id') === (string)$role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <select name="status" class="kt-input w-full">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'role_id', 'status']))
                                <a href="{{ route('admin.role-assignments') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Assignments Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Role Assignments</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('admin.role-assignments.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Assign Role
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-auto">
                <table class="kt-table w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Role</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Assigned</th>
                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $displayRoles = $user->roles->filter(function($role) {
                                    return $role->name !== 'Officer';
                                });
                            @endphp
                            @foreach($displayRoles as $role)
                                @php
                                    $pivot = $role->pivot;
                                    $officer = $user->officer;
                                    $initials = $officer->initials ?? '';
                                    $surname = $officer->surname ?? '';
                                    $fullName = trim("{$initials} {$surname}");
                                    $avatarInitials = strtoupper(($initials[0] ?? '') . ($surname[0] ?? ''));
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                                {{ $avatarInitials }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-foreground">{{ $fullName }}</div>
                                                <div class="text-xs text-secondary-foreground">{{ $user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-mono text-foreground">
                                            {{ $officer->service_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-primary kt-badge-sm">
                                            {{ $role->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $pivot->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                            {{ $pivot->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $pivot->assigned_at ? \Carbon\Carbon::parse($pivot->assigned_at)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                type="button"
                                                class="kt-btn kt-btn-sm kt-btn-ghost text-danger" 
                                                title="Remove Role"
                                                data-kt-modal-toggle="#delete-modal-{{ $user->id }}-{{ $role->id }}">
                                                <i class="ki-filled ki-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                    <p class="text-secondary-foreground">No role assignments found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $users->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modals -->
    @foreach($users as $user)
        @php
            $displayRoles = $user->roles->filter(function($role) {
                return $role->name !== 'Officer';
            });
        @endphp
        @foreach($displayRoles as $role)
            @php
                $officer = $user->officer;
                $initials = $officer->initials ?? '';
                $surname = $officer->surname ?? '';
                $fullName = trim("{$initials} {$surname}");
            @endphp
            <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $user->id }}-{{ $role->id }}">
                <div class="kt-modal-content max-w-[400px]">
                    <div class="kt-modal-header py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                                <i class="ki-filled ki-information text-danger text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-foreground">Confirm Removal</h3>
                        </div>
                        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                            <i class="ki-filled ki-cross"></i>
                        </button>
                    </div>
                    <div class="kt-modal-body py-5 px-5">
                        <p class="text-sm text-secondary-foreground">
                            Are you sure you want to remove the role <strong>{{ $role->name }}</strong> from <strong>{{ $fullName }}</strong>?
                        </p>
                        <p class="text-xs text-secondary-foreground mt-2">
                            This action will deactivate the role assignment for this officer.
                        </p>
                    </div>
                    <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                        <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                            Cancel
                        </button>
                        <form action="{{ route('admin.role-assignments.destroy', [$user->id, $role->id]) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="kt-btn kt-btn-danger">
                                <i class="ki-filled ki-trash"></i>
                                <span>Remove Role</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection

