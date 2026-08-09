export interface ActivityLogItem {
    id: number;
    action: string;
    action_label: string;
    subject_type: string;
    subject_id: string | null;
    properties: Record<string, any> | null;
    user: string | null;
    ip: string | null;
    created_at: string | null;
}