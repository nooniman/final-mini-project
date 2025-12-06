<?php
/**
 * Student Controller
 * Handles student profile operations
 */

class StudentController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($action) {
            case 'info':
            case 'profile':
                if ($method === 'GET') {
                    $this->getInfo();
                } elseif ($method === 'PUT') {
                    $this->updateProfile();
                }
                break;
                
            default:
                Response::error('Invalid student action', 404);
        }
    }
    
    /**
     * Get student info
     */
    private function getInfo() {
        $auth = Auth::requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as course_name, c.code as course_code
            FROM students s
            LEFT JOIN courses c ON s.course = c.code
            WHERE s.id = :id
        ");
        $stmt->execute(['id' => $auth['user_id']]);
        $student = $stmt->fetch();
        
        if (!$student) {
            Response::error('Student not found', 404);
        }
        
        // Remove sensitive data
        unset($student['password']);
        
        Response::success($student);
    }
    
    /**
     * Update student profile
     */
    private function updateProfile() {
        $auth = Auth::requireAuth();
        $input = $this->getInput();
        
        // Allowed fields to update
        $allowedFields = ['contact_number', 'address', 'profile_image'];
        $updates = [];
        $params = ['id' => $auth['user_id']];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $input[$field];
            }
        }
        
        if (empty($updates)) {
            Response::error('No valid fields to update', 400);
        }
        
        $sql = "UPDATE students SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'Profile updated successfully');
    }
}
