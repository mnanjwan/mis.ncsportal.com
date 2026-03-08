import { apiClient } from './client';

export type FleetRequestType = 'new_vehicle' | 'reallocation' | 'requisition' | 'repair';

export type FleetRequest = {
    id: number;
    request_type: FleetRequestType;
    status: string;
    requested_vehicle_type?: string;
    requested_make?: string;
    requested_model?: string;
    requested_year?: number;
    requested_quantity?: number;
    amount?: number;
    fleet_vehicle_id?: number;
    notes?: string;
    current_step_order?: number;
    submitted_at?: string;
    created_at: string;
    created_by?: number;
    origin_command?: { id: number; name: string };
    target_command?: { id: number; name: string };
    steps?: FleetRequestStep[];
    vehicle?: FleetVehicle;
};

export type FleetRequestStep = {
    id: number;
    step_order: number;
    title: string;
    status: 'pending' | 'approved' | 'rejected' | 'skipped';
    actor_role?: string;
    action_at?: string;
    remarks?: string;
    actor?: { id: number; name?: string };
};

export type FleetVehicle = {
    id: number;
    make: string;
    model: string;
    year_of_manufacture?: number;
    vehicle_type?: string;
    reg_no?: string;
    chassis_number?: string;
    engine_number?: string;
    service_status?: 'active' | 'maintenance' | 'retired';
    lifecycle_status?: 'new' | 'assigned' | 'returned';
    current_command?: { id: number; name: string };
    current_officer?: { id: number; name?: string };
};

export const fleetApi = {
    /** My assigned vehicles (regular officers) */
    async myVehicles(): Promise<{ success: boolean; data?: FleetVehicle[] }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetVehicle[] }>('/fleet/my-vehicles');
        return data;
    },

    /** Fleet requests in command (T&L officer) */
    async requests(page = 1): Promise<{ success: boolean; data?: FleetRequest[]; meta?: any }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetRequest[]; meta?: any }>(
            '/fleet/requests', { params: { page, per_page: 20 } }
        );
        return data;
    },

    /** Create a fleet request */
    async createRequest(payload: {
        request_type: FleetRequestType;
        requested_vehicle_type?: string;
        requested_make?: string;
        requested_model?: string;
        requested_year?: number;
        requested_quantity?: number;
        amount?: number;
        target_command_id?: number;
        fleet_vehicle_id?: number;
        notes?: string;
    }): Promise<{ success: boolean; data?: FleetRequest; message?: string }> {
        const { data } = await apiClient.post<{ success: boolean; data?: FleetRequest; message?: string }>(
            '/fleet/requests', payload
        );
        return data;
    },

    /** Single request with steps */
    async requestDetail(id: number): Promise<{ success: boolean; data?: FleetRequest }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetRequest }>(`/fleet/requests/${id}`);
        return data;
    },

    /** All vehicles in command */
    async vehicles(): Promise<{ success: boolean; data?: FleetVehicle[] }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetVehicle[] }>('/fleet/vehicles');
        return data;
    },

    /** Pending approvals for current approver role */
    async pendingApprovals(): Promise<{ success: boolean; data?: FleetRequest[] }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetRequest[] }>('/fleet/pending-approvals');
        return data;
    },

    /** Approve a request step */
    async approve(id: number, remarks?: string): Promise<{ success: boolean; message?: string }> {
        const { data } = await apiClient.post<{ success: boolean; message?: string }>(`/fleet/requests/${id}/approve`, { remarks });
        return data;
    },

    /** Reject a request */
    async reject(id: number, reason: string): Promise<{ success: boolean; message?: string }> {
        const { data } = await apiClient.post<{ success: boolean; message?: string }>(`/fleet/requests/${id}/reject`, { reason });
        return data;
    },
};
