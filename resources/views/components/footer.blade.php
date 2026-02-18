@props(['role' => null, 'description' => null])

<footer class="mt-10 pt-8 pb-0 border-t border-input bg-secondary/30 dark:bg-secondary/50 rounded-lg">
    <!-- Support & Contact with Developer Info -->
    <div class="pt-3 border-t border-input">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs text-secondary-foreground">
            <!-- Support & Contact -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="ki-filled ki-sms text-sm"></i>
                    <a href="mailto:support@ncsportal.gov.ng?subject=Support Request - {{ $role ?? 'Portal' }} Dashboard" 
                       class="text-primary hover:underline">
                        <span>support@ncsportal.gov.ng</span>
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <i class="ki-filled ki-setting-2 text-sm"></i>
                    <a href="mailto:ict.mod@ncsportal.gov.ng?subject=Technical Issue - {{ $role ?? 'Portal' }} Dashboard" 
                       class="text-primary hover:underline">
                        <span>ict.mod@ncsportal.gov.ng</span>
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <i class="ki-filled ki-question-2 text-sm"></i>
                    <a href="#" class="text-primary hover:underline" data-kt-modal-toggle="#faqModal">
                        <span>FAQ</span>
                    </a>
                </div>
            </div>
            
            <!-- Developer Info -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="ki-filled ki-code text-sm"></i>
                    <span>Developed by <strong class="text-foreground">ICT MOD</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="ki-filled ki-calendar text-sm"></i>
                    <span>Year: <strong class="text-foreground">{{ date('Y') }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Modal -->
    <div class="kt-modal hidden" data-kt-modal="true" id="faqModal">
        <div class="kt-modal-content max-w-[600px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="kt-modal-title">Frequently Asked Questions</h3>
                <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <div class="flex flex-col gap-4">
                    <div class="border-b border-input pb-3">
                        <h5 class="font-semibold text-foreground mb-2">How do I approve pending requests?</h5>
                        <p class="text-sm text-secondary-foreground">
                            Click on the "Review" button next to any pending item to view details and approve or reject it.
                        </p>
                    </div>
                    <div class="border-b border-input pb-3">
                        <h5 class="font-semibold text-foreground mb-2">Where can I find my assigned tasks?</h5>
                        <p class="text-sm text-secondary-foreground">
                            Check the statistics cards at the top of your dashboard and the "Recent Activity" sections for pending items requiring your attention.
                        </p>
                    </div>
                    <div class="border-b border-input pb-3">
                        <h5 class="font-semibold text-foreground mb-2">How do I navigate between different modules?</h5>
                        <p class="text-sm text-secondary-foreground">
                            Use the "Quick Actions" section or the sidebar menu to access different modules like Emoluments, Leave & Pass, Manning Requests, and Duty Rosters.
                        </p>
                    </div>
                    <div class="pb-3">
                        <h5 class="font-semibold text-foreground mb-2">What should I do if I encounter an error?</h5>
                        <p class="text-sm text-secondary-foreground">
                            Please report any errors or issues by clicking the support email link above or contact <strong class="text-foreground">ICT MOD</strong> directly. Include a screenshot if possible.
                        </p>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Close</button>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="mt-6 pt-4 pb-2 border-t border-input">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-secondary-foreground">
            <p>
                &copy; {{ date('Y') }} Nigeria Customs Service. All rights reserved.
            </p>
            <p class="flex items-center gap-2">
                <span>Powered by</span>
                <strong class="text-foreground">ICT MOD</strong>
            </p>
        </div>
    </div>
</footer>
