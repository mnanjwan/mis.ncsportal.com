import { apiClient } from './client';

export type ReportType =
    | 'service-record'
    | 'leave-history'
    | 'pass-history'
    | 'emolument-history'
    | 'posting-history'
    | 'course-history'
    | 'duty-roster'
    | 'quarter-history'
    | 'query-history';

export type ReportPayload = {
    success: boolean;
    data?: any; // The structure is highly dynamic based on the report type
    message?: string;
};

export const reportsApi = {
    /** Fetch the JSON data for a specific report */
    async getReportData(type: ReportType): Promise<ReportPayload> {
        const { data } = await apiClient.get<ReportPayload>(`/reports/${type}`);
        return data;
    },

    /** Initiate a PDF download for a specific report */
    async downloadReportPdf(type: ReportType): Promise<Blob> {
        const { data } = await apiClient.get<Blob>(`/reports/${type}/download`, {
            responseType: 'blob',
        });
        return data;
    },
};
