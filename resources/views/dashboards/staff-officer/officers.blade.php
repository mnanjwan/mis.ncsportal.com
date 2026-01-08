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
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Command Officers</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Officer Details
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Service No
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Rank
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Unit
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Status
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="officers-table-body">
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="ki-filled ki-loader text-4xl text-muted-foreground mb-4 animate-spin"></i>
                                    <p class="text-secondary-foreground">Loading officers...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="pagination" class="mt-6 pt-4 border-t border-border px-4 pb-4"></div>
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
                    <td colspan="6" class="py-12 text-center">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No officers found in command</p>
                        <p class="text-sm text-muted-foreground mt-1">Officers posted to this command will appear here</p>
                    </td>
                </tr>
            `;
            }
        </script>
    @endpush

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