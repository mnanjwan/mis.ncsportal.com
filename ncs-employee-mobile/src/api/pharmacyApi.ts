import { apiClient } from './client';

export type PharmacyDrug = {
    id: number;
    name: string;
    generic_name: string;
    category: string;
    unit: string;
    description?: string;
};

export type PharmacyStock = {
    id: number;
    drug: PharmacyDrug;
    command_id: number;
    quantity: number;
    batch_number?: string;
    expiry_date?: string;
};

export type RequisitionStatus = 'DRAFT' | 'SUBMITTED' | 'APPROVED' | 'ISSUED' | 'DISPENSED' | 'REJECTED';

export type PharmacyRequisitionItem = {
    id: number;
    drug: PharmacyDrug;
    requested_quantity: number;
    approved_quantity?: number;
    issued_quantity?: number;
    notes?: string;
};

export type PharmacyRequisition = {
    id: number;
    reference_number: string;
    command_id: number;
    command?: { id: number; name: string };
    status: RequisitionStatus;
    notes?: string;
    items: PharmacyRequisitionItem[];
    created_by: number;
    submitted_at?: string;
    approved_at?: string;
    issued_at?: string;
    dispensed_at?: string;
    created_at: string;
};

export const pharmacyApi = {
    /** Drug Catalog */
    async drugs(): Promise<{ success: boolean; data?: PharmacyDrug[] }> {
        const { data } = await apiClient.get('/pharmacy/drugs');
        return data;
    },

    /** Pharmacy Stock Overview (for current command) */
    async stock(): Promise<{ success: boolean; data?: PharmacyStock[] }> {
        const { data } = await apiClient.get('/pharmacy/stock');
        return data;
    },

    async lowStock(): Promise<{ success: boolean; data?: PharmacyStock[] }> {
        const { data } = await apiClient.get('/pharmacy/stock/low');
        return data;
    },

    /** Requisitions List */
    async requisitions(page = 1): Promise<{ success: boolean; data?: PharmacyRequisition[]; meta?: any }> {
        const { data } = await apiClient.get('/pharmacy/requisitions', { params: { page, per_page: 20 } });
        return data;
    },

    /** Create Requisition */
    async createRequisition(payload: { items: { drug_id: number; requested_quantity: number }[]; notes?: string }): Promise<{ success: boolean; data?: PharmacyRequisition }> {
        const { data } = await apiClient.post('/pharmacy/requisitions', payload);
        return data;
    },

    /** Single Requisition */
    async requisitionDetail(id: number): Promise<{ success: boolean; data?: PharmacyRequisition }> {
        const { data } = await apiClient.get(`/pharmacy/requisitions/${id}`);
        return data;
    },

    /** Submit Requisition for Approval */
    async submitRequisition(id: number): Promise<{ success: boolean; message?: string }> {
        const { data } = await apiClient.post(`/pharmacy/requisitions/${id}/submit`);
        return data;
    },
};
