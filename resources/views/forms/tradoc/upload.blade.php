@extends('layouts.app')

@section('title', 'Upload Training Results')
@section('page-title', 'Upload Training Results')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('tradoc.dashboard') }}">TRADOC</a>
    <span>/</span>
    <span class="text-primary">Upload Results</span>
@endsection

@section('content')
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
        <!-- Quick Steps -->
        <div class="kt-card bg-primary/5 border border-primary/20">
            <div class="kt-card-content p-5">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10 flex-shrink-0">
                        <i class="ki-filled ki-file-down text-primary text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-foreground mb-3">Quick Start Guide</h3>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-secondary-foreground">
                            <li><strong>Download Template:</strong> Get the CSV template with all new recruits pre-filled</li>
                            <li><strong>Fill Scores:</strong> Update the training scores (0-100) for each recruit</li>
                            <li><strong>Upload CSV:</strong> Select your completed CSV file and upload</li>
                            <li><strong>Review Results:</strong> Check the dashboard to see sorted results by rank</li>
                        </ol>
                        <div class="mt-4">
                            <button type="button" onclick="downloadTemplate()" class="kt-btn kt-btn-primary kt-btn-sm">
                                <i class="ki-filled ki-file-down"></i> Download Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Upload Training Results</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('tradoc.upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Rank Selection -->
                        <div>
                            <label for="rank" class="block text-sm font-medium text-foreground mb-2">
                                Select Rank <span class="text-danger">*</span>
                            </label>
                            <select name="rank" id="rank" class="kt-input w-full" required>
                                <option value="">Select Rank</option>
                                @foreach($availableRanks ?? [] as $rank)
                                    <option value="{{ $rank }}" {{ old('rank') === $rank ? 'selected' : '' }}>{{ $rank }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-secondary-foreground mt-1">
                                Select the rank for which you are uploading training results. The system will validate that all recruits in your CSV belong to this rank.
                            </p>
                        </div>

                        <!-- CSV File Upload -->
                        <div>
                            <label for="csv_file" class="block text-sm font-medium text-foreground mb-2">
                                Select CSV File <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv,.txt"
                                   class="kt-input w-full" 
                                   required>
                            <span class="text-xs" style="color: red; display: block; margin-top: 0.5rem;">
                                <strong>Document Type Allowed:</strong> CSV, TXT<br>
                                <strong>Document Size Allowed:</strong> Maximum 5MB
                            </span>
                        </div>

                        <!-- CSV Format Instructions -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-header">
                                <h4 class="text-sm font-semibold text-info">
                                    <i class="ki-filled ki-information text-info"></i> CSV File Format
                                </h4>
                            </div>
                            <div class="kt-card-content p-4 space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-foreground mb-2">Your CSV file must have exactly 3 columns (in this order):</p>
                                    <div class="bg-muted/30 rounded border border-input p-3 mt-2">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="border-b border-border">
                                                    <th class="text-left py-2 px-3 font-semibold">Column 1</th>
                                                    <th class="text-left py-2 px-3 font-semibold">Column 2</th>
                                                    <th class="text-left py-2 px-3 font-semibold">Column 3</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border-b border-border">
                                                    <td class="py-2 px-3 font-mono">Appointment Number</td>
                                                    <td class="py-2 px-3 font-mono">Officer Name</td>
                                                    <td class="py-2 px-3 font-mono">Training Score</td>
                                                </tr>
                                                <tr>
                                                    <td class="py-2 px-3 font-mono text-secondary-foreground">CDT00001</td>
                                                    <td class="py-2 px-3 text-secondary-foreground">John D. Smith</td>
                                                    <td class="py-2 px-3 font-mono text-secondary-foreground">85</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-start gap-2">
                                        <i class="ki-filled ki-check-circle text-success text-sm mt-0.5"></i>
                                        <div>
                                            <p class="text-xs font-medium text-foreground">Column 1: Appointment Number</p>
                                            <p class="text-xs text-secondary-foreground">Format: CDT00001, RCT00002, etc. (Must match existing recruits)</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="ki-filled ki-check-circle text-success text-sm mt-0.5"></i>
                                        <div>
                                            <p class="text-xs font-medium text-foreground">Column 2: Officer Name</p>
                                            <p class="text-xs text-secondary-foreground">Initials and surname (e.g., J.D Smith)</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="ki-filled ki-check-circle text-success text-sm mt-0.5"></i>
                                        <div>
                                            <p class="text-xs font-medium text-foreground">Column 3: Training Score</p>
                                            <p class="text-xs text-secondary-foreground">Number from 0 to 100</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="kt-card bg-warning/10 border border-warning/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-warning text-xl mt-0.5"></i>
                                    <div class="flex-1 space-y-2">
                                        <p class="text-sm font-semibold text-warning">Important Notes:</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>Only upload results for <strong>new recruits</strong> (those without service numbers)</li>
                                            <li>Appointment numbers must exist in the system</li>
                                            <li>Results will be automatically sorted by <strong>rank</strong> after upload</li>
                                            <li>You can download a template with all new recruits pre-filled</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Messages -->
                        @if($errors->has('csv_errors'))
                            <div class="kt-card bg-danger/10 border border-danger/20">
                                <div class="kt-card-header">
                                    <h4 class="text-sm font-semibold text-danger">
                                        <i class="ki-filled ki-cross-circle text-danger"></i> CSV Validation Errors
                                    </h4>
                                </div>
                                <div class="kt-card-content p-4">
                                    <p class="text-sm text-danger mb-3 font-medium">
                                        Please fix the following errors and try again:
                                    </p>
                                    <div class="max-h-60 overflow-y-auto bg-danger/5 rounded p-3">
                                        <ul class="list-disc list-inside text-xs text-danger space-y-1">
                                            @foreach($errors->get('csv_errors') as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($errors->any() && !$errors->has('csv_errors'))
                            <div class="kt-card bg-danger/10 border border-danger/20">
                                <div class="kt-card-content p-4">
                                    <ul class="list-disc list-inside text-xs text-danger space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-border">
                            <a href="{{ route('tradoc.dashboard') }}" class="kt-btn kt-btn-secondary">
                                <i class="ki-filled ki-arrow-left"></i> Back to Dashboard
                            </a>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="downloadTemplate()" class="kt-btn kt-btn-outline">
                                    <i class="ki-filled ki-file-down"></i> Download Template
                                </button>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-file-up"></i> Upload CSV
                            </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function downloadTemplate() {
            const rankSelect = document.getElementById('rank');
            const selectedRank = rankSelect.value;
            
            if (!selectedRank) {
                alert('Please select a rank first before downloading the template.');
                rankSelect.focus();
                return;
            }
            
            // Download template with rank parameter
            window.location.href = '{{ route("tradoc.download-template") }}?rank=' + encodeURIComponent(selectedRank);
        }
    </script>
    @endpush
@endsection
