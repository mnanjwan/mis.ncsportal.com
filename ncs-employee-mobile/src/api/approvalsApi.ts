import { apiClient } from './client';

export type ApprovalModuleType = 'pass' | 'leave' | 'emolument' | 'manning' | 'quarters' | 'fleet' | 'pharmacy' | 'postings' | 'queries';

export type ApprovalsDashboardStats = {
    total_pending: number;
    by_module: Record<ApprovalModuleType, { pending: number; label: string }>;
    recent_actions: { type: string; summary: string; timestamp: string }[];
};

export type PendingApprovalItem = {
    id: number;
    module: ApprovalModuleType;
    type: string;
    action_required: string;
    officer: { name: string; rank: string; service_number: string };
    summary: string;
    submitted_at: string;
    priority: 'normal' | 'high' | 'urgent';
    deep_link?: string;
};

export type PaginatedPendingApprovals = {
    success: boolean;
    data?: PendingApprovalItem[];
    meta?: { current_page: number; per_page: number; total: number; last_page: number };
};

export const approvalsApi = {
    /** Get high-level aggregated counts for the dashboard */
    async dashboardStats(): Promise<{ success: boolean; data?: ApprovalsDashboardStats }> {
        const { data } = await apiClient.get<{ success: boolean; data?: ApprovalsDashboardStats }>('/approvals/dashboard');
        return data;
    },

    /** Get a paginated list of all pending items, optionally filtered by module */
    async pendingItems(params?: { module?: ApprovalModuleType; page?: number; per_page?: number }): Promise<PaginatedPendingApprovals> {
        const { data } = await apiClient.get<PaginatedPendingApprovals>('/approvals/pending', { params: params ?? {} });
        return data;
    },

    /** Get a paginated history of completed approvals by this user */
    async history(params?: { page?: number; per_page?: number }): Promise<PaginatedPendingApprovals> {
        const { data } = await apiClient.get<PaginatedPendingApprovals>('/approvals/history', { params: params ?? {} });
        return data;
    },
};
