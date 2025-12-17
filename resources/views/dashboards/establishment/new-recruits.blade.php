@extends('layouts.app')

@section('title', 'New Recruits')
@section('page-title', 'New Recruits')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Actions -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-mono">New Recruits</h2>
            <div class="flex items-center gap-3">
                <button class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Add New Recruit
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
                                <th class="text-left p-4 text-sm font-semibold text-mono">Recruit Details</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Application ID</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Entry Rank</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Status</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-input">
                            <tr>
                                <td colspan="5" class="text-center p-8 text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="h-12 w-12 rounded-full bg-secondary flex items-center justify-center mb-3">
                                            <i class="ki-filled ki-users text-xl text-muted-foreground"></i>
                                        </div>
                                        <p class="text-base font-medium">No new recruits found</p>
                                        <p class="text-sm text-muted-foreground mt-1">Add a new recruit to get started</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection