<?php
/**
 * Semester Controller
 * Handles semester operations
 */

class SemesterController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        switch ($action) {
            case '':
            case 'list':
                $this->listSemesters();
                break;
                
            case 'current':
                $this->getCurrentSemester();
                break;
                
            default:
                if (is_numeric($action)) {
                    $this->getSemester($action);
                } else {
                    Response::error('Invalid semester action', 404);
                }
        }
    }
    
    /**
     * List all semesters
     */
    private function listSemesters() {
        $stmt = $this->db->prepare("
            SELECT * FROM semesters 
            ORDER BY academic_year DESC, semester_number DESC
        ");
        $stmt->execute();
        $semesters = $stmt->fetchAll();
        
        Response::success($semesters);
    }
    
    /**
     * Get current semester
     */
    private function getCurrentSemester() {
        $stmt = $this->db->prepare("
            SELECT * FROM semesters 
            WHERE is_current = 1 
            LIMIT 1
        ");
        $stmt->execute();
        $semester = $stmt->fetch();
        
        if (!$semester) {
            Response::error('No current semester set', 404);
        }
        
        Response::success($semester);
    }
    
    /**
     * Get single semester
     */
    private function getSemester($id) {
        $stmt = $this->db->prepare("SELECT * FROM semesters WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $semester = $stmt->fetch();
        
        if (!$semester) {
            Response::error('Semester not found', 404);
        }
        
        Response::success($semester);
    }
}
