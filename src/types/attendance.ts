/**
 * Attendance / Monitoring Types
 */

export interface AttendanceRecord {
  id: string;
  subjectId: string;
  subjectCode: string;
  subjectName: string;
  date: string;
  status: AttendanceStatus;
  timeIn?: string;
  timeOut?: string;
  remarks?: string;
  recordedBy: string;
  createdAt: string;
}

export type AttendanceStatus = 'present' | 'absent' | 'late' | 'excused';

export interface AttendanceSummary {
  subjectId: string;
  subjectCode: string;
  subjectName: string;
  totalClasses: number;
  present: number;
  absent: number;
  late: number;
  excused: number;
  attendanceRate: number; // Percentage
  status: 'good' | 'warning' | 'critical';
}

export interface AttendanceStats {
  overallRate: number;
  totalClasses: number;
  totalPresent: number;
  totalAbsent: number;
  totalLate: number;
  totalExcused: number;
  subjectSummaries: AttendanceSummary[];
  monthlyTrend: MonthlyAttendance[];
}

export interface MonthlyAttendance {
  month: string; // YYYY-MM format
  monthName: string;
  attendanceRate: number;
  totalClasses: number;
  present: number;
  absent: number;
}

export interface AttendanceFilter {
  subjectId?: string;
  startDate?: string;
  endDate?: string;
  status?: AttendanceStatus;
}

export interface CalendarDay {
  date: string;
  hasClass: boolean;
  attendance?: AttendanceRecord[];
}
