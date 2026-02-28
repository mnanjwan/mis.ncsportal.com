import { apiClient } from './client';

export type RosterAssignmentItem = {
  id: number;
  duty_roster_id: number;
  officer_id: number;
  duty_date: string;
  duty_type: string;
  roster?: { id: number; command_id?: number; start_date?: string; end_date?: string; command?: { name: string } };
};

export const dutyRosterApi = {
  async officerSchedule(
    officerId: number,
    params?: { start_date?: string; end_date?: string }
  ): Promise<{ success: boolean; data?: RosterAssignmentItem[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: RosterAssignmentItem[] }>(
      `/officers/${officerId}/duty-schedule`,
      { params: params ?? {} }
    );
    return data;
  },
};
