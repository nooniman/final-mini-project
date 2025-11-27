/**
 * Grade Types
 */

export interface Grade {
  id: string;
  subjectId: string;
  subjectCode: string;
  subjectName: string;
  units: number;
  midtermGrade?: number;
  finalGrade?: number;
  grade: number;
  gradeEquivalent: string; // e.g., "1.0", "1.25", "1.50", etc.
  remarks: 'Passed' | 'Failed' | 'Incomplete' | 'Dropped' | 'In Progress';
  semesterId: string;
  semesterName: string;
  academicYear: string;
  instructor: string;
  datePosted?: string;
  createdAt: string;
  updatedAt: string;
}

export interface GradeDetails extends Grade {
  components: GradeComponent[];
  classStanding?: number;
  attendance?: number;
}

export interface GradeComponent {
  id: string;
  name: string;
  score: number;
  maxScore: number;
  percentage: number;
  weight: number;
}

export interface GWASummary {
  currentGWA: number;
  cumulativeGWA: number;
  totalUnits: number;
  totalUnitsPassed: number;
  totalUnitsFailed: number;
  semesterGWA: SemesterGWA[];
  standing: 'Dean\'s Lister' | 'University Scholar' | 'Regular' | 'Probation';
}

export interface SemesterGWA {
  semesterId: string;
  semesterName: string;
  academicYear: string;
  gwa: number;
  units: number;
  unitsPassed: number;
}

export interface GradesFilter {
  semesterId?: string;
  academicYear?: string;
  status?: Grade['remarks'];
}

export interface GradesSummary {
  totalSubjects: number;
  passedSubjects: number;
  failedSubjects: number;
  inProgressSubjects: number;
  highestGrade: number;
  lowestGrade: number;
  averageGrade: number;
}
