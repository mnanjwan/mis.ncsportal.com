@extends('layouts.app')

@section('title', 'Course Nominations')
@section('page-title', 'Course Nominations')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Home</a>
    <span>/</span>
    <span class="text-primary">Course Nominations</span>
@endsection

@section('content')
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Course Nominations</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('officer.course-nominations') }}" class="flex flex-col gap-4">
                    <!-- Filter Controls -->
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <select name="status" class="kt-input w-full">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <!-- Year Select -->
                        <div class="w-full md:w-36">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <select name="year" class="kt-input w-full">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Sort By</label>
                            <select name="sort_by" class="kt-input w-full">
                                <option value="start_date" {{ request('sort_by') == 'start_date' ? 'selected' : '' }}>Start Date</option>
                                <option value="course_name" {{ request('sort_by') == 'course_name' ? 'selected' : '' }}>Course Name</option>
                                <option value="completion_date" {{ request('sort_by') == 'completion_date' ? 'selected' : '' }}>Completion Date</option>
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Nominated Date</option>
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div class="w-full md:w-36">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Order</label>
                            <select name="sort_order" class="kt-input w-full">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['status', 'year', 'sort_by', 'sort_order']))
                                <a href="{{ route('officer.course-nominations') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Course Nominations List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Course Nominations</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $courses->total() }} records
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 1000px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Course Name
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Type
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Start Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        End Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Completion Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Status
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Nominated By
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $course->course_name }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $course->course_type ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $course->start_date->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $course->end_date ? $course->end_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $course->completion_date ? $course->completion_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-{{ $course->is_completed ? 'success' : 'warning' }} kt-badge-sm">
                                                {{ $course->is_completed ? 'Completed' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            @if($course->nominatedBy && $course->nominatedBy->officer)
                                                {{ $course->nominatedBy->officer->initials }} {{ $course->nominatedBy->officer->surname }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No course nominations found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        @forelse($courses as $course)
                            <div class="flex flex-col gap-3 p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex items-center justify-center size-12 rounded-full {{ $course->is_completed ? 'bg-success/10' : 'bg-warning/10' }}">
                                            <i class="ki-filled ki-book {{ $course->is_completed ? 'text-success' : 'text-warning' }} text-xl"></i>
                                        </div>
                                        <div class="flex flex-col gap-1 flex-1">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ $course->course_name }}
                                            </span>
                                            @if($course->course_type)
                                            <span class="text-xs text-secondary-foreground">
                                                Type: {{ $course->course_type }}
                                            </span>
                                            @endif
                                            <span class="text-xs text-secondary-foreground">
                                                Start: {{ $course->start_date->format('d/m/Y') }}
                                            </span>
                                            @if($course->end_date)
                                            <span class="text-xs text-secondary-foreground">
                                                End: {{ $course->end_date->format('d/m/Y') }}
                                            </span>
                                            @endif
                                            @if($course->is_completed && $course->completion_date)
                                            <span class="text-xs text-secondary-foreground">
                                                Completed: {{ $course->completion_date->format('d/m/Y') }}
                                            </span>
                                            @endif
                                            @if($course->nominatedBy && $course->nominatedBy->officer)
                                            <span class="text-xs text-secondary-foreground">
                                                Nominated by: {{ $course->nominatedBy->officer->initials }} {{ $course->nominatedBy->officer->surname }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="kt-badge kt-badge-{{ $course->is_completed ? 'success' : 'warning' }} kt-badge-sm">
                                        {{ $course->is_completed ? 'Completed' : 'Pending' }}
                                    </span>
                                </div>
                                @if($course->notes)
                                <div class="pt-2 border-t border-border">
                                    <p class="text-xs text-secondary-foreground">
                                        <span class="font-medium">Notes:</span> {{ $course->notes }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No course nominations found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($courses->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $courses->links() }}
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

