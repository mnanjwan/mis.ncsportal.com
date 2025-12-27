<!-- PART 8: DECLARATIONS -->
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">DECLARATIONS</h3>
    </div>
    <div class="kt-card-content">
        <div class="flex flex-col gap-6">
            <!-- Officer Declaration -->
            <div class="flex flex-col gap-3">
                <h4 class="text-lg font-semibold">DECLARATION</h4>
                <p class="text-sm text-secondary-foreground italic">(Comments by the officer on whom the report is rendered)</p>
                <p class="text-sm text-secondary-foreground">
                    I certify that I have seen the content of this Report and that the reporting officer has discussed them with me. 
                    I have the following comments to make (if no comment, so hereunder):
                </p>
                @if(isset($form) && $form->status === 'OFFICER_REVIEW' && $form->officer->user_id === auth()->id())
                    <form action="{{ route('officer.aper-forms.update-comments', $form->id) }}" method="POST" class="flex flex-col gap-3">
                        @csrf
                        <textarea name="officer_comments" class="kt-input" rows="8" placeholder="Enter your comments here...">{{ old('officer_comments', $form->officer_comments) }}</textarea>
                        <div class="flex items-center justify-end gap-3">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Save Comments
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-muted/50 rounded-lg">
                        <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->officer_comments ?? 'No comments provided.' }}</p>
                        @if($form->officer_signed_at)
                            <div class="mt-4 pt-4 border-t border-border">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="kt-form-label text-sm">Date</label>
                                        <p class="text-sm text-foreground">{{ $form->officer_signed_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <label class="kt-form-label text-sm">Signature</label>
                                        <p class="text-sm text-foreground">{{ $form->officer->initials }} {{ $form->officer->surname }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Reporting Officer Declaration -->
            <div class="flex flex-col gap-3 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">DECLARATION BY REPORTING OFFICER</h4>
                @if($form->reporting_officer_declaration)
                    <div class="p-4 bg-muted/50 rounded-lg">
                        <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->reporting_officer_declaration }}</p>
                        @if($form->reporting_officer_signed_at)
                            <div class="mt-4 pt-4 border-t border-border">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="kt-form-label text-sm">Date</label>
                                        <p class="text-sm text-foreground">{{ $form->reporting_officer_signed_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <label class="kt-form-label text-sm">Signed By</label>
                                        <p class="text-sm text-foreground">{{ $form->reportingOfficerUser ? $form->reportingOfficerUser->email : ($form->reportingOfficer ? $form->reportingOfficer->email : 'N/A') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-secondary-foreground italic">Not yet completed</p>
                @endif
            </div>

            <!-- Countersigning Officer Declaration -->
            <div class="flex flex-col gap-3 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">DECLARATION BY COUNTERSIGNING OFFICER</h4>
                @if($form->countersigning_officer_declaration)
                    <div class="p-4 bg-muted/50 rounded-lg">
                        <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->countersigning_officer_declaration }}</p>
                        @if($form->countersigning_officer_signed_at)
                            <div class="mt-4 pt-4 border-t border-border">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="kt-form-label text-sm">Date</label>
                                        <p class="text-sm text-foreground">{{ $form->countersigning_officer_signed_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <label class="kt-form-label text-sm">Signed By</label>
                                        <p class="text-sm text-foreground">{{ $form->countersigningOfficerUser ? $form->countersigningOfficerUser->email : ($form->countersigningOfficer ? $form->countersigningOfficer->email : 'N/A') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-secondary-foreground italic">Not yet completed</p>
                @endif
            </div>

            <!-- Head of Department Declaration -->
            <div class="flex flex-col gap-3 border-t border-border pt-6">
                <h4 class="text-lg font-semibold">DECLARATION BY HEAD OF DEPARTMENT</h4>
                @if($form->head_of_department_declaration)
                    <div class="p-4 bg-muted/50 rounded-lg">
                        <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->head_of_department_declaration }}</p>
                        @if($form->head_of_department_signed_at)
                            <div class="mt-4 pt-4 border-t border-border">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="kt-form-label text-sm">Date</label>
                                        <p class="text-sm text-foreground">{{ $form->head_of_department_signed_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <label class="kt-form-label text-sm">Signed By</label>
                                        <p class="text-sm text-foreground">{{ $form->headOfDepartment ? $form->headOfDepartment->email : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-secondary-foreground italic">Not yet completed</p>
                @endif
            </div>
        </div>
    </div>
</div>

