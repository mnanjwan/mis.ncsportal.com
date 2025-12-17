<!-- Notifications Drawer -->
<div class="kt-drawer kt-drawer-end" data-kt-drawer="true" data-kt-drawer-name="notifications" id="notifications_drawer">
    <div class="kt-drawer-content flex flex-col w-full lg:w-[400px]">
        <div class="kt-drawer-header">
            <h3 class="kt-drawer-title">Notifications</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-drawer-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-drawer-body flex flex-col gap-5 p-5" id="notifications-list">
            <!-- Notifications will be loaded via API -->
            <div class="flex grow gap-2.5">
                <div class="kt-avatar size-8">
                    <div class="kt-avatar-image">
                        <img alt="avatar" src="{{ asset('ncs-employee-portal/dist/assets/media/avatars/300-1.png') }}"/>
                    </div>
                </div>
                <div class="flex flex-col gap-1 grow">
                    <div class="text-sm font-medium">
                        <span class="text-mono font-semibold">Loading notifications...</span>
                    </div>
                    <span class="text-xs text-muted-foreground">Please wait</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Notifications Drawer -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/notifications?per_page=10', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const container = document.getElementById('notifications-list');
            
            if (data.data && data.data.length > 0) {
                container.innerHTML = data.data.map(notif => `
                    <div class="flex grow gap-2.5">
                        <div class="kt-avatar size-8">
                            <div class="kt-avatar-image">
                                <img alt="avatar" src="{{ asset('ncs-employee-portal/dist/assets/media/avatars/300-1.png') }}"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 grow">
                            <div class="text-sm font-medium">
                                <span class="text-mono font-semibold">${notif.title || 'Notification'}</span>
                            </div>
                            <span class="text-xs text-muted-foreground">${notif.message || ''}</span>
                            <span class="text-xs text-muted-foreground">${new Date(notif.created_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary-foreground text-center py-4">No notifications</p>';
            }
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
});
</script>
@endpush
