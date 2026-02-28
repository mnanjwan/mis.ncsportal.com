/**
 * Shared API response types (backend: success, data, meta)
 */

export type ApiResponse<T = unknown> = {
  success: boolean;
  data?: T;
  message?: string;
  meta?: { current_page?: number; per_page?: number; total?: number; last_page?: number };
};

export type PassApplicationItem = {
  id: number;
  officer_id?: number;
  start_date: string;
  end_date: string;
  number_of_days: number;
  reason?: string;
  status: string;
  submitted_at?: string;
  minuted_at?: string;
  approved_at?: string;
  rejected_at?: string;
  rejection_reason?: string;
};

export type LeaveApplicationItem = {
  id: number;
  officer_id?: number;
  leave_type_id: number;
  leave_type?: { id: number; name: string; code?: string };
  start_date: string;
  end_date: string;
  number_of_days: number;
  reason?: string;
  status: string;
  expected_date_of_delivery?: string;
  medical_certificate_url?: string;
  submitted_at?: string;
  rejection_reason?: string;
};

export type LeaveTypeItem = {
  id: number;
  name: string;
  code?: string;
  max_duration_days?: number;
};

export type EmolumentItem = {
  id: number;
  year: number;
  timeline?: { id: number; year: number };
  bank_name?: string;
  bank_account_number?: string;
  pfa_name?: string;
  rsa_pin?: string;
  notes?: string;
  status: string;
  submitted_at?: string;
  assessed_at?: string;
  validated_at?: string;
  audited_at?: string;
  processed_at?: string;
};

export type EmolumentTimelineItem = {
  id: number;
  year: number;
  is_active: boolean;
  can_submit?: boolean;
};
