<?php
/**
 * Grade Controller
 * Handles grade-related operations
 */

class GradeController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        switch ($action) {
            case '':
            case 'list':
                $this->listGrades();
                break;
                
            case 'subject':
                if ($id) {
                    $this->getBySubject($id);
                } else {
                    Response::error('Subject ID required', 400);
                }
                break;
                
            case 'gwa':
                $this->getGWA();
                break;
                
            case 'summary':
                $this->getSummary();
                break;
                
            default:
                Response::error('Invalid grades action', 404);
        }
    }
    
    /**
     * List all grades for student
     */
    private function listGrades() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT g.*, s.code as subject_code, s.name as subject_name, 
                   s.units, sem.name as semester_name, sem.academic_year
            FROM grades g
            JOIN subjects s ON g.subject_id = s.id
            JOIN semesters sem ON g.semester_id = sem.id
            WHERE g.student_id = :student_id
        ";
        
        $queryParams = ['student_id' => $auth['user_id']];
        
        if ($semesterId) {
            $sql .= " AND g.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY sem.academic_year DESC, sem.semester_number DESC, s.code ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $grades = $stmt->fetchAll();
        
        Response::success($grades);
    }
    
    /**
     * Get grade for specific subject
     */
    private function getBySubject($subjectId) {
        $auth = Auth::requireAuth();
        
        // Get grade with components
        $stmt = $this->db->prepare("
            SELECT g.*, s.code as subject_code, s.name as subject_name, s.units,
                   sem.name as semester_name, sem.academic_year
            FROM grades g
            JOIN subjects s ON g.subject_id = s.id
            JOIN semesters sem ON g.semester_id = sem.id
            WHERE g.student_id = :student_id AND g.subject_id = :subject_id
        ");
        $stmt->execute([
            'student_id' => $auth['user_id'],
            'subject_id' => $subjectId,
        ]);
        $grade = $stmt->fetch();
        
        if (!$grade) {
            Response::error('Grade not found', 404);
        }
        
        // Get grade components
        $stmt = $this->db->prepare("
            SELECT * FROM grade_components 
            WHERE grade_id = :grade_id
            ORDER BY component_type, created_at
        ");
        $stmt->execute(['grade_id' => $grade['id']]);
        $components = $stmt->fetchAll();
        
        $grade['components'] = $components;
        
        Response::success($grade);
    }
    
    /**
     * Get GWA summary
     */
    private function getGWA() {
        $auth = Auth::requireAuth();
        
        // Calculate overall GWA
        $stmt = $this->db->prepare("
            SELECT 
                ROUND(SUM(g.final_grade * s.units) / SUM(s.units), 4) as gwa,
                SUM(s.units) as total_units,
                COUNT(g.id) as total_subjects
            FROM grades g
            JOIN subjects s ON g.subject_id = s.id
            WHERE g.student_id = :student_id AND g.final_grade IS NOT NULL
        ");
        $stmt->execute(['student_id' => $auth['user_id']]);
        $overall = $stmt->fetch();
        
        // Get GWA per semester
        $stmt = $this->db->prepare("
            SELECT 
                sem.id as semester_id,
                sem.name as semester_name,
                sem.academic_year,
                ROUND(SUM(g.final_grade * s.units) / SUM(s.units), 4) as gwa,
                SUM(s.units) as units,
                COUNT(g.id) as subjects
            FROM grades g
            JOIN subjects s ON g.subject_id = s.id
            JOIN semesters sem ON g.semester_id = sem.id
            WHERE g.student_id = :student_id AND g.final_grade IS NOT NULL
            GROUP BY sem.id
            ORDER BY sem.academic_year DESC, sem.semester_number DESC
        ");
        $stmt->execute(['student_id' => $auth['user_id']]);
        $bySemester = $stmt->fetchAll();
        
        Response::success([
            'overall' => [
                'gwa' => $overall['gwa'] ? floatval($overall['gwa']) : null,
                'totalUnits' => intval($overall['total_units'] ?? 0),
                'totalSubjects' => intval($overall['total_subjects'] ?? 0),
            ],
            'bySemester' => $bySemester,
        ]);
    }
    
    /**
     * Get grades summary
     */
    private function getSummary() {
        $auth = Auth::requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(CASE WHEN final_grade >= 1.0 AND final_grade <= 1.5 THEN 1 END) as excellent,
                COUNT(CASE WHEN final_grade > 1.5 AND final_grade <= 2.0 THEN 1 END) as very_good,
                COUNT(CASE WHEN final_grade > 2.0 AND final_grade <= 2.5 THEN 1 END) as good,
                COUNT(CASE WHEN final_grade > 2.5 AND final_grade <= 3.0 THEN 1 END) as satisfactory,
                COUNT(CASE WHEN final_grade > 3.0 THEN 1 END) as needs_improvement,
                COUNT(CASE WHEN final_grade = 5.0 THEN 1 END) as failed
            FROM grades
            WHERE student_id = :student_id AND final_grade IS NOT NULL
        ");
        $stmt->execute(['student_id' => $auth['user_id']]);
        $summary = $stmt->fetch();
        
        Response::success($summary);
    }
}
