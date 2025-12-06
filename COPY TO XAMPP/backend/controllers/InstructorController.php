<?php
/**
 * Instructor Controller
 * Handles instructor operations
 */

class InstructorController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($method) {
            case 'GET':
                if ($action === '' || $action === 'list') {
                    $this->listInstructors();
                } elseif (is_numeric($action)) {
                    $this->getInstructor($action);
                } elseif ($action === 'search') {
                    $this->searchInstructors();
                } else {
                    Response::error('Invalid instructors action', 404);
                }
                break;
                
            case 'POST':
                $this->createInstructor();
                break;
                
            case 'PUT':
                if (is_numeric($action)) {
                    $this->updateInstructor($action);
                } else {
                    Response::error('Instructor ID required', 400);
                }
                break;
                
            case 'DELETE':
                if (is_numeric($action)) {
                    $this->deleteInstructor($action);
                } else {
                    Response::error('Instructor ID required', 400);
                }
                break;
                
            default:
                Response::error('Method not allowed', 405);
        }
    }
    
    /**
     * List all instructors
     */
    private function listInstructors() {
        $params = $this->getQueryParams();
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 50;
        $department = $params['department'] ?? '';
        
        $offset = ($page - 1) * $perPage;
        
        // Base query
        $sql = "
            SELECT i.*, 
                   (SELECT COUNT(*) FROM subjects s WHERE s.instructor_id = i.id) as subject_count
            FROM instructors i
        ";
        $countSql = "SELECT COUNT(*) FROM instructors i";
        $queryParams = [];
        
        if ($department) {
            $sql .= " WHERE i.department = :department";
            $countSql .= " WHERE department = :department";
            $queryParams['department'] = $department;
        }
        
        $sql .= " ORDER BY i.last_name, i.first_name LIMIT :limit OFFSET :offset";
        
        // Get total count
        $stmt = $this->db->prepare($countSql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get instructors
        $stmt = $this->db->prepare($sql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $instructors = $stmt->fetchAll();
        
        Response::paginated($instructors, $page, $perPage, $total);
    }
    
    /**
     * Get single instructor with subjects
     */
    private function getInstructor($id) {
        $stmt = $this->db->prepare("
            SELECT i.*,
                   (SELECT COUNT(*) FROM subjects s WHERE s.instructor_id = i.id) as subject_count
            FROM instructors i
            WHERE i.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $instructor = $stmt->fetch();
        
        if (!$instructor) {
            Response::error('Instructor not found', 404);
        }
        
        // Get subjects taught by this instructor
        $stmt = $this->db->prepare("
            SELECT s.id, s.code, s.name, s.units
            FROM subjects s
            WHERE s.instructor_id = :instructor_id
            ORDER BY s.code
        ");
        $stmt->execute(['instructor_id' => $id]);
        $instructor['subjects'] = $stmt->fetchAll();
        
        // Get current schedules
        $stmt = $this->db->prepare("
            SELECT sc.*, s.code as subject_code, s.name as subject_name,
                   sem.name as semester_name, sem.academic_year
            FROM schedules sc
            JOIN subjects s ON sc.subject_id = s.id
            JOIN semesters sem ON sc.semester_id = sem.id
            WHERE s.instructor_id = :instructor_id AND sem.is_current = 1
            ORDER BY FIELD(sc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                     sc.start_time
        ");
        $stmt->execute(['instructor_id' => $id]);
        $instructor['schedules'] = $stmt->fetchAll();
        
        Response::success($instructor);
    }
    
    /**
     * Search instructors by name
     */
    private function searchInstructors() {
        $params = $this->getQueryParams();
        $query = $params['q'] ?? '';
        
        if (strlen($query) < 2) {
            Response::error('Search query too short', 400);
        }
        
        $stmt = $this->db->prepare("
            SELECT i.id, i.first_name, i.last_name, i.email, i.department
            FROM instructors i
            WHERE i.first_name LIKE :query1 
               OR i.last_name LIKE :query2
               OR CONCAT(i.first_name, ' ', i.last_name) LIKE :query3
            ORDER BY i.last_name, i.first_name
            LIMIT 20
        ");
        $stmt->execute([
            'query1' => "%$query%",
            'query2' => "%$query%",
            'query3' => "%$query%",
        ]);
        $instructors = $stmt->fetchAll();
        
        Response::success($instructors);
    }
    
    /**
     * Create new instructor
     */
    private function createInstructor() {
        $input = $this->getInput();
        $this->validateRequired($input, ['first_name', 'last_name', 'email']);
        
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM instructors WHERE email = :email");
        $stmt->execute(['email' => $input['email']]);
        if ($stmt->fetch()) {
            Response::error('Email already exists', 409);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO instructors (first_name, last_name, email, department, office, consultation_hours, created_at) 
            VALUES (:first_name, :last_name, :email, :department, :office, :consultation_hours, NOW())
        ");
        $stmt->execute([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'department' => $input['department'] ?? null,
            'office' => $input['office'] ?? null,
            'consultation_hours' => $input['consultation_hours'] ?? null,
        ]);
        
        $instructorId = $this->db->lastInsertId();
        
        Response::success(['id' => $instructorId], 'Instructor created successfully', 201);
    }
    
    /**
     * Update instructor
     */
    private function updateInstructor($id) {
        $input = $this->getInput();
        
        // Check if instructor exists
        $stmt = $this->db->prepare("SELECT * FROM instructors WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $instructor = $stmt->fetch();
        
        if (!$instructor) {
            Response::error('Instructor not found', 404);
        }
        
        // Check if new email conflicts
        if (isset($input['email']) && $input['email'] !== $instructor['email']) {
            $stmt = $this->db->prepare("SELECT id FROM instructors WHERE email = :email AND id != :id");
            $stmt->execute(['email' => $input['email'], 'id' => $id]);
            if ($stmt->fetch()) {
                Response::error('Email already exists', 409);
            }
        }
        
        $allowedFields = ['first_name', 'last_name', 'email', 'department', 'office', 'consultation_hours'];
        $updates = [];
        $params = ['id' => $id];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $input[$field];
            }
        }
        
        if (empty($updates)) {
            Response::error('No valid fields to update', 400);
        }
        
        $sql = "UPDATE instructors SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'Instructor updated successfully');
    }
    
    /**
     * Delete instructor
     */
    private function deleteInstructor($id) {
        // Check if instructor exists
        $stmt = $this->db->prepare("SELECT * FROM instructors WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            Response::error('Instructor not found', 404);
        }
        
        // Check if instructor has subjects assigned
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE instructor_id = :id");
        $stmt->execute(['id' => $id]);
        $subjectCount = $stmt->fetchColumn();
        
        if ($subjectCount > 0) {
            Response::error("Cannot delete instructor. $subjectCount subjects are assigned.", 409);
        }
        
        $stmt = $this->db->prepare("DELETE FROM instructors WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        Response::success(null, 'Instructor deleted successfully');
    }
}
