<?php
/**
 * Course Controller
 * Handles course/program operations
 */

class CourseController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($method) {
            case 'GET':
                if ($action === '' || $action === 'list') {
                    $this->listCourses();
                } elseif (is_numeric($action)) {
                    $this->getCourse($action);
                } elseif ($action === 'stats') {
                    $this->getStats();
                } else {
                    Response::error('Invalid courses action', 404);
                }
                break;
                
            case 'POST':
                $this->createCourse();
                break;
                
            case 'PUT':
                if (is_numeric($action)) {
                    $this->updateCourse($action);
                } else {
                    Response::error('Course ID required', 400);
                }
                break;
                
            case 'DELETE':
                if (is_numeric($action)) {
                    $this->deleteCourse($action);
                } else {
                    Response::error('Course ID required', 400);
                }
                break;
                
            default:
                Response::error('Method not allowed', 405);
        }
    }
    
    /**
     * List all courses
     */
    private function listCourses() {
        $params = $this->getQueryParams();
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 50;
        $search = $params['search'] ?? '';
        
        $offset = ($page - 1) * $perPage;
        
        // Base query
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM students s WHERE s.course = c.code) as student_count FROM courses c";
        $countSql = "SELECT COUNT(*) FROM courses c";
        $queryParams = [];
        
        if ($search) {
            $sql .= " WHERE c.name LIKE :search OR c.code LIKE :search2";
            $countSql .= " WHERE name LIKE :search OR code LIKE :search2";
            $queryParams['search'] = "%$search%";
            $queryParams['search2'] = "%$search%";
        }
        
        $sql .= " ORDER BY c.code ASC LIMIT :limit OFFSET :offset";
        
        // Get total count
        $stmt = $this->db->prepare($countSql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get courses
        $stmt = $this->db->prepare($sql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $courses = $stmt->fetchAll();
        
        Response::paginated($courses, $page, $perPage, $total);
    }
    
    /**
     * Get single course
     */
    private function getCourse($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM students s WHERE s.course = c.code) as student_count
            FROM courses c 
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch();
        
        if (!$course) {
            Response::error('Course not found', 404);
        }
        
        // Get students in this course
        $stmt = $this->db->prepare("
            SELECT id, student_id, first_name, last_name, email, year_level 
            FROM students 
            WHERE course = :code 
            ORDER BY last_name, first_name
            LIMIT 100
        ");
        $stmt->execute(['code' => $course['code']]);
        $course['students'] = $stmt->fetchAll();
        
        Response::success($course);
    }
    
    /**
     * Get course statistics
     */
    private function getStats() {
        // Total courses
        $stmt = $this->db->query("SELECT COUNT(*) FROM courses");
        $totalCourses = $stmt->fetchColumn();
        
        // Students per course
        $stmt = $this->db->query("
            SELECT c.code, c.name, COUNT(s.id) as student_count
            FROM courses c
            LEFT JOIN students s ON s.course = c.code
            GROUP BY c.id
            ORDER BY student_count DESC
        ");
        $courseDistribution = $stmt->fetchAll();
        
        Response::success([
            'total_courses' => $totalCourses,
            'course_distribution' => $courseDistribution,
        ]);
    }
    
    /**
     * Create new course
     */
    private function createCourse() {
        $input = $this->getInput();
        $this->validateRequired($input, ['code', 'name']);
        
        // Check if code already exists
        $stmt = $this->db->prepare("SELECT id FROM courses WHERE code = :code");
        $stmt->execute(['code' => $input['code']]);
        if ($stmt->fetch()) {
            Response::error('Course code already exists', 409);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO courses (code, name, created_at) 
            VALUES (:code, :name, NOW())
        ");
        $stmt->execute([
            'code' => $input['code'],
            'name' => $input['name'],
        ]);
        
        $courseId = $this->db->lastInsertId();
        
        Response::success(['id' => $courseId], 'Course created successfully', 201);
    }
    
    /**
     * Update course
     */
    private function updateCourse($id) {
        $input = $this->getInput();
        
        // Check if course exists
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch();
        
        if (!$course) {
            Response::error('Course not found', 404);
        }
        
        // Check if new code conflicts with existing
        if (isset($input['code']) && $input['code'] !== $course['code']) {
            $stmt = $this->db->prepare("SELECT id FROM courses WHERE code = :code AND id != :id");
            $stmt->execute(['code' => $input['code'], 'id' => $id]);
            if ($stmt->fetch()) {
                Response::error('Course code already exists', 409);
            }
        }
        
        $updates = [];
        $params = ['id' => $id];
        
        if (isset($input['code'])) {
            $updates[] = "code = :code";
            $params['code'] = $input['code'];
        }
        if (isset($input['name'])) {
            $updates[] = "name = :name";
            $params['name'] = $input['name'];
        }
        
        if (empty($updates)) {
            Response::error('No valid fields to update', 400);
        }
        
        $sql = "UPDATE courses SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'Course updated successfully');
    }
    
    /**
     * Delete course
     */
    private function deleteCourse($id) {
        // Check if course exists
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch();
        
        if (!$course) {
            Response::error('Course not found', 404);
        }
        
        // Check if students are enrolled in this course
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE course = :code");
        $stmt->execute(['code' => $course['code']]);
        $studentCount = $stmt->fetchColumn();
        
        if ($studentCount > 0) {
            Response::error("Cannot delete course. $studentCount students are enrolled.", 409);
        }
        
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        Response::success(null, 'Course deleted successfully');
    }
}
