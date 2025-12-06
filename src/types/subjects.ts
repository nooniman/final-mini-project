/**
 * Subject Types
 */

export interface Subject {
  id: string;
  code: string;
  name: string;
  description?: string;
  units: number;
  lectureHours?: number;
  labHours?: number;
  instructor?: Instructor;
  schedule?: Schedule[];
  schedules?: Schedule[]; // Backend returns 'schedules'
  room?: string;
  semesterId?: string;
  semesterName?: string;
  academicYear?: string;
  status?: 'enrolled' | 'completed' | 'dropped' | 'failed';
  createdAt?: string;
  updatedAt?: string;
}

export interface SubjectDetails extends Subject {
  syllabus?: string;
  prerequisites?: string[];
  corequisites?: string[];
  classSize?: number;
  enrolledCount?: number;
}

export interface Instructor {
  id: string;
  name: string;
  email?: string;
  department: string;
  title?: string;
  avatar?: string;
}

export interface Schedule {
  id?: string;
  day?: 'Monday' | 'Tuesday' | 'Wednesday' | 'Thursday' | 'Friday' | 'Saturday' | 'Sunday'; // Backend uses 'day'
  dayOfWeek?: 'Monday' | 'Tuesday' | 'Wednesday' | 'Thursday' | 'Friday' | 'Saturday' | 'Sunday'; // Alternative field name
  startTime?: string; // HH:mm format or TIME from backend
  endTime?: string; // HH:mm format or TIME from backend
  time?: string; // Combined time string (alternative format)
  room?: string;
  type?: 'Lecture' | 'Laboratory' | 'Tutorial' | 'lecture' | 'laboratory' | 'tutorial'; // Backend uses capitalized
}

export interface Semester {
  id: string;
  name: string; // e.g., "First Semester", "Second Semester", "Summer"
  academicYear: string; // e.g., "2024-2025"
  startDate: string;
  endDate: string;
  isCurrent: boolean;
  status: 'upcoming' | 'ongoing' | 'completed';
}

export interface SubjectsFilter {
  semesterId?: string;
  academicYear?: string;
  status?: Subject['status'];
  search?: string;
}
