<!-- Notifications Drawer -->
<div class="kt-drawer kt-drawer-end" data-kt-drawer="true" data-kt-drawer-name="notifications" id="notifications_drawer">
    <div class="kt-drawer-content flex flex-col w-full lg:w-[400px]">
        <div class="kt-drawer-header flex items-center justify-between p-4 border-b border-border">
            <div class="flex items-center gap-3">
                <h3 class="kt-drawer-title text-lg font-semibold text-foreground">Notifications</h3>
                <span id="unread-count" class="kt-badge kt-badge-primary kt-badge-sm hidden">0</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="markAllNotificationsRead()" class="kt-btn kt-btn-sm kt-btn-ghost text-xs" id="mark-all-read-btn" style="display: none;">
                    Mark all read
                </button>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-drawer-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
        </div>
        <div class="kt-drawer-body flex flex-col p-0" id="notifications-list" style="max-height: calc(100vh - 120px); overflow-y: auto;">
            <!-- Notifications will be loaded via API -->
            <div class="flex items-center justify-center py-12" id="loading-state">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-3"></div>
                    <p class="text-sm text-secondary-foreground">Loading notifications...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Notifications Drawer -->

@push('scripts')
<script>
let notificationsData = [];
let unreadCount = 0;

async function loadNotifications() {
    try {
        const container = document.getElementById('notifications-list');
        const loadingState = document.getElementById('loading-state');
        
        // Use API with Sanctum token
        const token = window.API_CONFIG?.token || '';
        const response = await fetch('/api/v1/notifications?per_page=20', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Authorization': token ? `Bearer ${token}` : '',
            },
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const data = await response.json();
            notificationsData = data.data || [];
            unreadCount = notificationsData.filter(n => !n.is_read).length;
            
            // Update unread count badge
            const unreadBadge = document.getElementById('unread-count');
            if (unreadCount > 0) {
                unreadBadge.textContent = unreadCount;
                unreadBadge.classList.remove('hidden');
                document.getElementById('mark-all-read-btn').style.display = 'block';
            } else {
                unreadBadge.classList.add('hidden');
                document.getElementById('mark-all-read-btn').style.display = 'none';
            }
            
            // Update sidebar notification icon badge
            updateNotificationIconBadge(unreadCount);
            
            if (loadingState) {
                loadingState.remove();
            }
            
            if (notificationsData.length > 0) {
                container.innerHTML = notificationsData.map(notif => {
                    const isUnread = !notif.is_read;
                    const date = new Date(notif.created_at);
                    const timeAgo = getTimeAgo(date);
                    
                    return `
                        <div class="p-4 border-b border-border hover:bg-muted/50 transition-colors cursor-pointer ${isUnread ? 'bg-primary/5' : ''}" 
                             onclick="markNotificationRead(${notif.id}, this)" 
                             data-notification-id="${notif.id}">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <i class="ki-filled ki-notification-status text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h4 class="text-sm font-semibold text-foreground ${isUnread ? 'font-bold' : ''}">${escapeHtml(notif.title || 'Notification')}</h4>
                                        ${isUnread ? '<span class="kt-badge kt-badge-primary kt-badge-sm shrink-0">New</span>' : ''}
                                    </div>
                                    <p class="text-sm text-secondary-foreground mb-2 line-clamp-2">${escapeHtml(notif.message || '')}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-muted-foreground">${timeAgo}</span>
                                        ${notif.entity_type && notif.entity_id ? 
                                            `<a href="/notifications/${notif.id}" class="text-xs text-primary hover:underline">View Details</a>` : 
                                            ''
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 px-4">
                        <i class="ki-filled ki-notification-off text-4xl text-muted-foreground mb-3"></i>
                        <p class="text-secondary-foreground text-center">No notifications</p>
                        <p class="text-xs text-muted-foreground text-center mt-1">You're all caught up!</p>
                    </div>
                `;
            }
        } else {
            if (loadingState) {
                loadingState.innerHTML = `
                    <div class="text-center">
                        <i class="ki-filled ki-information text-2xl text-danger mb-2"></i>
                        <p class="text-sm text-danger">Failed to load notifications</p>
                        <button onclick="loadNotifications()" class="kt-btn kt-btn-sm kt-btn-primary mt-3">Retry</button>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
        const container = document.getElementById('notifications-list');
        const loadingState = document.getElementById('loading-state');
        if (loadingState) {
            loadingState.innerHTML = `
                <div class="text-center">
                    <i class="ki-filled ki-information text-2xl text-danger mb-2"></i>
                    <p class="text-sm text-danger">Error loading notifications</p>
                    <button onclick="loadNotifications()" class="kt-btn kt-btn-sm kt-btn-primary mt-3">Retry</button>
                </div>
            `;
        }
    }
}

async function markNotificationRead(id, element) {
    try {
        const token = window.API_CONFIG?.token || '';
        const response = await fetch(`/api/v1/notifications/${id}/read`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Authorization': token ? `Bearer ${token}` : '',
            },
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            // Remove unread styling
            element.classList.remove('bg-primary/5');
            const badge = element.querySelector('.kt-badge');
            if (badge) badge.remove();
            const title = element.querySelector('h4');
            if (title) title.classList.remove('font-bold');
            
            // Update unread count
            unreadCount = Math.max(0, unreadCount - 1);
            updateNotificationIconBadge(unreadCount);
            
            const unreadBadge = document.getElementById('unread-count');
            if (unreadCount > 0) {
                unreadBadge.textContent = unreadCount;
                unreadBadge.classList.remove('hidden');
            } else {
                unreadBadge.classList.add('hidden');
                document.getElementById('mark-all-read-btn').style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

async function markAllNotificationsRead() {
    try {
        const token = window.API_CONFIG?.token || '';
        const response = await fetch('/api/v1/notifications/read-all', {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Authorization': token ? `Bearer ${token}` : '',
            },
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            // Reload notifications
            await loadNotifications();
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

function updateNotificationIconBadge(count) {
    const notificationBtn = document.querySelector('[data-kt-drawer-toggle="#notifications_drawer"]');
    if (notificationBtn) {
        // Remove existing badge
        const existingBadge = notificationBtn.querySelector('.notification-badge');
        if (existingBadge) existingBadge.remove();
        
        // Add badge if there are unread notifications
        if (count > 0) {
            const badge = document.createElement('span');
            badge.className = 'notification-badge absolute -top-1 -right-1 size-5 rounded-full bg-danger flex items-center justify-center text-white text-xs font-bold';
            badge.textContent = count > 9 ? '9+' : count;
            notificationBtn.style.position = 'relative';
            notificationBtn.appendChild(badge);
        }
    }
}

function getTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load notifications when drawer opens
document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.getElementById('notifications_drawer');
    if (drawer) {
        drawer.addEventListener('shown', () => {
            loadNotifications();
        });
        
        // Also load on initial page load
        loadNotifications();
        
        // Refresh notifications every 30 seconds
        setInterval(loadNotifications, 30000);
    }
});
</script>
@endpush
