<div class="space-y-4">
    <!-- Raw Data Display -->
    <div class="kt-card bg-muted/50">
        <div class="kt-card-header">
            <h4 class="kt-card-title text-sm">Raw Uploaded Data</h4>
        </div>
        <div class="kt-card-content p-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Appointment Number</label>
                    <p class="text-sm font-mono text-foreground">{{ $result->appointment_number }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Officer Name</label>
                    <p class="text-sm text-foreground">{{ $result->officer_name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Training Score</label>
                    <p class="text-sm font-semibold text-foreground">{{ number_format($result->training_score, 2) }}%</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Status</label>
                    <span class="kt-badge kt-badge-{{ $result->status === 'PASS' ? 'success' : 'danger' }} kt-badge-sm">
                        {{ $result->status }}
                    </span>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Rank</label>
                    <p class="text-sm text-foreground">{{ $result->rank ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Service Number</label>
                    <p class="text-sm font-mono text-foreground">{{ $result->service_number ?? 'Not Assigned' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CSV Format Representation -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h4 class="kt-card-title text-sm">CSV Format Representation</h4>
        </div>
        <div class="kt-card-content p-4">
            <div class="bg-muted/30 rounded border border-input p-3">
                <pre class="text-xs font-mono text-foreground overflow-x-auto">{{ $result->appointment_number }},{{ $result->officer_name }},{{ $result->training_score }},{{ $result->status }}</pre>
            </div>
            <p class="text-xs text-secondary-foreground mt-2">
                This represents how this record would appear in the CSV file.
            </p>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h4 class="kt-card-title text-sm">Additional Information</h4>
        </div>
        <div class="kt-card-content p-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Uploaded By</label>
                    <p class="text-sm text-foreground">{{ $result->uploadedBy->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Uploaded At</label>
                    <p class="text-sm text-foreground">{{ $result->uploaded_at->format('d/m/Y H:i:s') }}</p>
                </div>
                @if($result->officer_id)
                <div>
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Linked Officer ID</label>
                    <p class="text-sm text-foreground">{{ $result->officer_id }}</p>
                </div>
                @endif
                @if($result->notes)
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-secondary-foreground mb-1">Notes</label>
                    <p class="text-sm text-foreground">{{ $result->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Validation Check -->
    <div class="kt-card bg-info/10 border border-info/20">
        <div class="kt-card-content p-4">
            <div class="flex items-start gap-3">
                <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-info mb-2">Validation Status</p>
                    <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                        <li>Appointment Number: {{ $result->appointment_number ? '✓ Valid' : '✗ Missing' }}</li>
                        <li>Officer Name: {{ $result->officer_name ? '✓ Valid' : '✗ Missing' }}</li>
                        <li>Training Score: {{ ($result->training_score >= 0 && $result->training_score <= 100) ? '✓ Valid (' . number_format($result->training_score, 2) . '%)' : '✗ Invalid' }}</li>
                        <li>Status: {{ in_array($result->status, ['PASS', 'FAIL']) ? '✓ Valid (' . $result->status . ')' : '✗ Invalid' }}</li>
                        @if($result->service_number)
                        <li>Service Number: ✓ Assigned ({{ $result->service_number }})</li>
                        @else
                        <li>Service Number: ⏳ Pending Assignment</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
