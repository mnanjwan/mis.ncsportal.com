@extends('layouts.app')

@section('title', 'Command Officers')
@section('page-title', 'Command Officers')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-mono">Command Officers</h2>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-input" class="kt-input pl-10" placeholder="Search officers..."
                        style="width: 300px;" />
                </div>
                <button class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-file-down"></i> Export Nominal Roll
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="kt-card">
            <div class="kt-card-content p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-input bg-muted/20">
                                <th class="text-left p-4 text-sm font-semibold text-mono">Officer Details</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Service No</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Rank</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Unit</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Status</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="officers-table-body" class="divide-y divide-input">
                            <tr>
                                <td colspan="6" class="text-center p-8 text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-4"></div>
                                        <p>Loading officers...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between p-4 border-t border-input" id="pagination">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentPage = 1;

            document.addEventListener('DOMContentLoaded', async () => {
                await loadOfficers();
            });

            async function loadOfficers(page = 1) {
                // Mock data for now
                const tbody = document.getElementById('officers-table-body');
                tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center p-8 text-muted-foreground">
                        <div class="flex flex-col items-center justify-center">
                            <div class="h-12 w-12 rounded-full bg-secondary flex items-center justify-center mb-3">
                                <i class="ki-filled ki-users text-xl text-muted-foreground"></i>
                            </div>
                            <p class="text-base font-medium">No officers found in command</p>
                            <p class="text-sm text-muted-foreground mt-1">Officers posted to this command will appear here</p>
                        </div>
                    </td>
                </tr>
            `;
            }
        </script>
    @endpush
@endsection