<?php
/**
 * Subject Controller
 * Handles subject-related operations
 */

class SubjectController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        switch ($action) {
            case '':
            case 'list':
                $this->listSubjects();
                break;
                
            case 'schedule':
                $this->getSchedule();
                break;
                
            default:
                // Check if action is a subject ID
                if (is_numeric($action)) {
                    $this->getSubject($action);
                } else {
                    Response::error('Invalid subjects action', 404);
                }
        }
    }
    
    /**
     * List enrolled subjects
     */
    private function listSubjects() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT s.*, e.semester_id, sem.name as semester_name, sem.academic_year,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name,
                   i.email as instructor_email
            FROM enrollments e
            JOIN subjects s ON e.subject_id = s.id
            JOIN semesters sem ON e.semester_id = sem.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            WHERE e.student_id = :student_id
        ";
        
        $queryParams = ['student_id' => $auth['user_id']];
        
        if ($semesterId) {
            $sql .= " AND e.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY sem.academic_year DESC, s.code ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $subjects = $stmt->fetchAll();
        
        // Get schedules for each subject
        foreach ($subjects as &$subject) {
            $stmt = $this->db->prepare("
                SELECT * FROM schedules 
                WHERE subject_id = :subject_id AND semester_id = :semester_id
                ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
            ");
            $stmt->execute([
                'subject_id' => $subject['id'],
                'semester_id' => $subject['semester_id'],
            ]);
            $subject['schedules'] = $stmt->fetchAll();
        }
        
        Response::success($subjects);
    }
    
    /**
     * Get single subject details
     */
    private function getSubject($id) {
        $auth = Auth::requireAuth();
        
        // Verify student is enrolled
        $stmt = $this->db->prepare("
            SELECT s.*, sem.name as semester_name, sem.academic_year,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name,
                   i.email as instructor_email, i.department as instructor_department,
                   i.office as instructor_office, i.consultation_hours
            FROM subjects s
            JOIN enrollments e ON e.subject_id = s.id AND e.student_id = :student_id
            JOIN semesters sem ON e.semester_id = sem.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            WHERE s.id = :subject_id
        ");
        $stmt->execute([
            'student_id' => $auth['user_id'],
            'subject_id' => $id,
        ]);
        $subject = $stmt->fetch();
        
        if (!$subject) {
            Response::error('Subject not found or not enrolled', 404);
        }
        
        // Get schedules
        $stmt = $this->db->prepare("
            SELECT * FROM schedules 
            WHERE subject_id = :subject_id
            ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ");
        $stmt->execute(['subject_id' => $id]);
        $subject['schedules'] = $stmt->fetchAll();
        
        // Get prerequisites
        $stmt = $this->db->prepare("
            SELECT p.code, p.name 
            FROM subject_prerequisites sp
            JOIN subjects p ON sp.prerequisite_id = p.id
            WHERE sp.subject_id = :subject_id
        ");
        $stmt->execute(['subject_id' => $id]);
        $subject['prerequisites'] = $stmt->fetchAll();
        
        Response::success($subject);
    }
    
    /**
     * Get weekly schedule
     */
    private function getSchedule() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        // If no semester specified, get current semester
        if (!$semesterId) {
            $stmt = $this->db->prepare("SELECT id FROM semesters WHERE is_current = 1 LIMIT 1");
            $stmt->execute();
            $current = $stmt->fetch();
            $semesterId = $current['id'] ?? null;
        }
        
        if (!$semesterId) {
            Response::success([]);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT sc.*, s.code as subject_code, s.name as subject_name,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name
            FROM schedules sc
            JOIN subjects s ON sc.subject_id = s.id
            JOIN enrollments e ON e.subject_id = s.id AND e.semester_id = sc.semester_id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            WHERE e.student_id = :student_id AND sc.semester_id = :semester_id
            ORDER BY FIELD(sc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                     sc.start_time
        ");
        $stmt->execute([
            'student_id' => $auth['user_id'],
            'semester_id' => $semesterId,
        ]);
        $schedules = $stmt->fetchAll();
        
        Response::success($schedules);
    }
}
