/**
 * Notification Types
 */

export interface Notification {
  id: string;
  type: NotificationType;
  title: string;
  message: string;
  data?: Record<string, unknown>;
  isRead: boolean;
  createdAt: string;
  readAt?: string;
}

export type NotificationType = 
  | 'grade_posted'
  | 'attendance_alert'
  | 'schedule_change'
  | 'announcement'
  | 'deadline_reminder'
  | 'system';

export interface NotificationSettings {
  gradePosted: boolean;
  attendanceAlert: boolean;
  scheduleChange: boolean;
  announcements: boolean;
  deadlineReminder: boolean;
  pushEnabled: boolean;
  emailEnabled: boolean;
}
