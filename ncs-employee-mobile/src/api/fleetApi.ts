import { apiClient } from './client';

export type FleetRequestType = 'FLEET_NEW_VEHICLE' | 'FLEET_RE_ALLOCATION' | 'FLEET_OPE' | 'FLEET_REPAIR' | 'FLEET_USE' | 'FLEET_REQUISITION';

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
    async myVehicles(): Promise<{ success: boolean; data?: FleetVehicle[], message: string }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetVehicle[], message: string }>('/fleet/my-vehicles');
        return data;
    },

    /** Fleet requests overview (My Requests & Inbox) */
    async requests(): Promise<{ success: boolean; data?: { myRequests: FleetRequest[], inbox: FleetRequest[] }, message: string }> {
        const { data } = await apiClient.get<{ success: boolean; data?: { myRequests: FleetRequest[], inbox: FleetRequest[] }, message: string }>(
            '/fleet/requests'
        );
        return data;
    },

    /** Create a draft fleet request */
    async createRequest(payload: {
        request_type: FleetRequestType;
        requested_vehicle_type?: string;
        requested_make?: string;
        requested_model?: string;
        requested_year?: number;
        requested_quantity?: number;
        amount?: number;
        fleet_vehicle_id?: number;
        notes?: string;
        document?: { uri: string; name: string; type: string };
    }): Promise<{ success: boolean; data?: FleetRequest; message?: string }> {
        const formData = new FormData();
        formData.append('request_type', payload.request_type);
        if (payload.requested_vehicle_type) formData.append('requested_vehicle_type', payload.requested_vehicle_type);
        if (payload.requested_make) formData.append('requested_make', payload.requested_make);
        if (payload.requested_model) formData.append('requested_model', payload.requested_model);
        if (payload.requested_year) formData.append('requested_year', payload.requested_year.toString());
        if (payload.requested_quantity) formData.append('requested_quantity', payload.requested_quantity.toString());
        if (payload.amount) formData.append('amount', payload.amount.toString());
        if (payload.fleet_vehicle_id) formData.append('fleet_vehicle_id', payload.fleet_vehicle_id.toString());
        if (payload.notes) formData.append('notes', payload.notes);

        if (payload.document) {
            formData.append('document', {
                uri: payload.document.uri,
                name: payload.document.name,
                type: payload.document.type || 'application/pdf',
            } as any);
        }

        const { data } = await apiClient.post<{ success: boolean; data?: FleetRequest; message?: string }>(
            '/fleet/requests',
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        return data;
    },

    /** Submit a drafted request */
    async submitRequest(id: number): Promise<{ success: boolean; data?: FleetRequest; message?: string }> {
        const { data } = await apiClient.post<{ success: boolean; data?: FleetRequest; message?: string }>(
            `/fleet/requests/${id}/submit`
        );
        return data;
    },

    /** Single request with steps */
    async requestDetail(id: number): Promise<{ success: boolean; data?: FleetRequest, message: string }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetRequest, message: string }>(`/fleet/requests/${id}`);
        return data;
    },

    /** All vehicles in command (T&L Officer) */
    async commandVehicles(): Promise<{ success: boolean; data?: FleetVehicle[], message: string }> {
        const { data } = await apiClient.get<{ success: boolean; data?: FleetVehicle[], message: string }>('/fleet/command-vehicles');
        return data;
    },

    /** Act on a request step (Approve/Reject/Forward) */
    async act(id: number, payload: { decision: 'FORWARDED' | 'APPROVED' | 'REJECTED' | 'REVIEWED' | 'KIV', comment?: string }): Promise<{ success: boolean; data?: FleetRequest, message?: string }> {
        const { data } = await apiClient.post<{ success: boolean; data?: FleetRequest, message?: string }>(`/fleet/requests/${id}/act`, payload);
        return data;
    },
};
