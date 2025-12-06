<?php
/**
 * Enrollment Controller
 * Handles student enrollment operations
 */

class EnrollmentController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($method) {
            case 'GET':
                if ($action === '' || $action === 'list') {
                    $this->listEnrollments();
                } elseif (is_numeric($action)) {
                    $this->getEnrollment($action);
                } elseif ($action === 'student') {
                    $this->getByStudent($id);
                } elseif ($action === 'subject') {
                    $this->getBySubject($id);
                } elseif ($action === 'stats') {
                    $this->getStats();
                } elseif ($action === 'my') {
                    $this->getMyEnrollments();
                } else {
                    Response::error('Invalid enrollments action', 404);
                }
                break;
                
            case 'POST':
                if ($action === 'bulk') {
                    $this->bulkEnroll();
                } else {
                    $this->createEnrollment();
                }
                break;
                
            case 'PUT':
                if (is_numeric($action)) {
                    $this->updateEnrollment($action);
                } else {
                    Response::error('Enrollment ID required', 400);
                }
                break;
                
            case 'DELETE':
                if (is_numeric($action)) {
                    $this->deleteEnrollment($action);
                } else {
                    Response::error('Enrollment ID required', 400);
                }
                break;
                
            default:
                Response::error('Method not allowed', 405);
        }
    }
    
    /**
     * List all enrollments with filters
     */
    private function listEnrollments() {
        $params = $this->getQueryParams();
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 50;
        $semesterId = $params['semester_id'] ?? null;
        $subjectId = $params['subject_id'] ?? null;
        $status = $params['status'] ?? null;
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "
            SELECT e.*, 
                   st.student_id as student_code, st.first_name, st.last_name, st.email,
                   s.code as subject_code, s.name as subject_name, s.units,
                   sem.name as semester_name, sem.academic_year
            FROM enrollments e
            JOIN students st ON e.student_id = st.id
            JOIN subjects s ON e.subject_id = s.id
            JOIN semesters sem ON e.semester_id = sem.id
            WHERE 1=1
        ";
        $countSql = "SELECT COUNT(*) FROM enrollments e WHERE 1=1";
        $queryParams = [];
        
        if ($semesterId) {
            $sql .= " AND e.semester_id = :semester_id";
            $countSql .= " AND e.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        if ($subjectId) {
            $sql .= " AND e.subject_id = :subject_id";
            $countSql .= " AND e.subject_id = :subject_id";
            $queryParams['subject_id'] = $subjectId;
        }
        
        if ($status) {
            $sql .= " AND e.status = :status";
            $countSql .= " AND e.status = :status";
            $queryParams['status'] = $status;
        }
        
        $sql .= " ORDER BY st.last_name, st.first_name LIMIT :limit OFFSET :offset";
        
        // Get total count
        $stmt = $this->db->prepare($countSql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get enrollments
        $stmt = $this->db->prepare($sql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $enrollments = $stmt->fetchAll();
        
        Response::paginated($enrollments, $page, $perPage, $total);
    }
    
    /**
     * Get single enrollment
     */
    private function getEnrollment($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, 
                   st.student_id as student_code, st.first_name, st.last_name, st.email,
                   s.code as subject_code, s.name as subject_name, s.units,
                   sem.name as semester_name, sem.academic_year,
                   g.final_grade, g.midterm_grade, g.prelim_grade
            FROM enrollments e
            JOIN students st ON e.student_id = st.id
            JOIN subjects s ON e.subject_id = s.id
            JOIN semesters sem ON e.semester_id = sem.id
            LEFT JOIN grades g ON g.student_id = e.student_id 
                               AND g.subject_id = e.subject_id 
                               AND g.semester_id = e.semester_id
            WHERE e.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $enrollment = $stmt->fetch();
        
        if (!$enrollment) {
            Response::error('Enrollment not found', 404);
        }
        
        Response::success($enrollment);
    }
    
    /**
     * Get enrollments for a student
     */
    private function getByStudent($studentId) {
        if (!$studentId) {
            Response::error('Student ID required', 400);
        }
        
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT e.*, 
                   s.code as subject_code, s.name as subject_name, s.units,
                   sem.name as semester_name, sem.academic_year,
                   g.final_grade, g.midterm_grade, g.prelim_grade
            FROM enrollments e
            JOIN subjects s ON e.subject_id = s.id
            JOIN semesters sem ON e.semester_id = sem.id
            LEFT JOIN grades g ON g.student_id = e.student_id 
                               AND g.subject_id = e.subject_id 
                               AND g.semester_id = e.semester_id
            WHERE e.student_id = :student_id
        ";
        $queryParams = ['student_id' => $studentId];
        
        if ($semesterId) {
            $sql .= " AND e.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY sem.academic_year DESC, sem.semester_number DESC, s.code ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $enrollments = $stmt->fetchAll();
        
        Response::success($enrollments);
    }
    
    /**
     * Get enrollments for a subject
     */
    private function getBySubject($subjectId) {
        if (!$subjectId) {
            Response::error('Subject ID required', 400);
        }
        
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT e.*, 
                   st.student_id as student_code, st.first_name, st.last_name, st.email,
                   sem.name as semester_name, sem.academic_year,
                   g.final_grade, g.midterm_grade, g.prelim_grade
            FROM enrollments e
            JOIN students st ON e.student_id = st.id
            JOIN semesters sem ON e.semester_id = sem.id
            LEFT JOIN grades g ON g.student_id = e.student_id 
                               AND g.subject_id = e.subject_id 
                               AND g.semester_id = e.semester_id
            WHERE e.subject_id = :subject_id
        ";
        $queryParams = ['subject_id' => $subjectId];
        
        if ($semesterId) {
            $sql .= " AND e.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY st.last_name, st.first_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $enrollments = $stmt->fetchAll();
        
        Response::success($enrollments);
    }
    
    /**
     * Get current user's enrollments (authenticated student)
     */
    private function getMyEnrollments() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT e.*, 
                   s.code as subject_code, s.name as subject_name, s.units,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name,
                   sem.name as semester_name, sem.academic_year,
                   g.final_grade, g.midterm_grade, g.prelim_grade
            FROM enrollments e
            JOIN subjects s ON e.subject_id = s.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            JOIN semesters sem ON e.semester_id = sem.id
            LEFT JOIN grades g ON g.student_id = e.student_id 
                               AND g.subject_id = e.subject_id 
                               AND g.semester_id = e.semester_id
            WHERE e.student_id = :student_id
        ";
        $queryParams = ['student_id' => $auth['user_id']];
        
        if ($semesterId) {
            $sql .= " AND e.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY sem.academic_year DESC, sem.semester_number DESC, s.code ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $enrollments = $stmt->fetchAll();
        
        Response::success($enrollments);
    }
    
    /**
     * Get enrollment statistics
     */
    private function getStats() {
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        // Build where clause
        $where = $semesterId ? "WHERE e.semester_id = :semester_id" : "WHERE 1=1";
        $queryParams = $semesterId ? ['semester_id' => $semesterId] : [];
        
        // Total enrollments
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM enrollments e $where");
        $stmt->execute($queryParams);
        $totalEnrollments = $stmt->fetchColumn();
        
        // By status
        $stmt = $this->db->prepare("
            SELECT e.status, COUNT(*) as count 
            FROM enrollments e 
            $where 
            GROUP BY e.status
        ");
        $stmt->execute($queryParams);
        $byStatus = $stmt->fetchAll();
        
        // Popular subjects
        $stmt = $this->db->prepare("
            SELECT s.code, s.name, COUNT(e.id) as enrollment_count
            FROM enrollments e
            JOIN subjects s ON e.subject_id = s.id
            $where
            GROUP BY s.id
            ORDER BY enrollment_count DESC
            LIMIT 10
        ");
        $stmt->execute($queryParams);
        $popularSubjects = $stmt->fetchAll();
        
        Response::success([
            'total_enrollments' => $totalEnrollments,
            'by_status' => $byStatus,
            'popular_subjects' => $popularSubjects,
        ]);
    }
    
    /**
     * Create new enrollment
     */
    private function createEnrollment() {
        $input = $this->getInput();
        $this->validateRequired($input, ['student_id', 'subject_id', 'semester_id']);
        
        // Check if already enrolled
        $stmt = $this->db->prepare("
            SELECT id FROM enrollments 
            WHERE student_id = :student_id AND subject_id = :subject_id AND semester_id = :semester_id
        ");
        $stmt->execute([
            'student_id' => $input['student_id'],
            'subject_id' => $input['subject_id'],
            'semester_id' => $input['semester_id'],
        ]);
        if ($stmt->fetch()) {
            Response::error('Student is already enrolled in this subject for this semester', 409);
        }
        
        // Check prerequisites
        $prereqCheck = $this->checkPrerequisites($input['student_id'], $input['subject_id']);
        if (!$prereqCheck['passed']) {
            Response::error('Prerequisites not met: ' . implode(', ', $prereqCheck['missing']), 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO enrollments (student_id, subject_id, semester_id, status, created_at) 
            VALUES (:student_id, :subject_id, :semester_id, :status, NOW())
        ");
        $stmt->execute([
            'student_id' => $input['student_id'],
            'subject_id' => $input['subject_id'],
            'semester_id' => $input['semester_id'],
            'status' => $input['status'] ?? 'enrolled',
        ]);
        
        $enrollmentId = $this->db->lastInsertId();
        
        // Create empty grade record
        $stmt = $this->db->prepare("
            INSERT INTO grades (student_id, subject_id, semester_id, created_at) 
            VALUES (:student_id, :subject_id, :semester_id, NOW())
        ");
        $stmt->execute([
            'student_id' => $input['student_id'],
            'subject_id' => $input['subject_id'],
            'semester_id' => $input['semester_id'],
        ]);
        
        Response::success(['id' => $enrollmentId], 'Enrollment created successfully', 201);
    }
    
    /**
     * Bulk enroll students in a subject
     */
    private function bulkEnroll() {
        $input = $this->getInput();
        $this->validateRequired($input, ['student_ids', 'subject_id', 'semester_id']);
        
        if (!is_array($input['student_ids']) || empty($input['student_ids'])) {
            Response::error('student_ids must be a non-empty array', 400);
        }
        
        $enrolled = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($input['student_ids'] as $studentId) {
            // Check if already enrolled
            $stmt = $this->db->prepare("
                SELECT id FROM enrollments 
                WHERE student_id = :student_id AND subject_id = :subject_id AND semester_id = :semester_id
            ");
            $stmt->execute([
                'student_id' => $studentId,
                'subject_id' => $input['subject_id'],
                'semester_id' => $input['semester_id'],
            ]);
            
            if ($stmt->fetch()) {
                $skipped++;
                continue;
            }
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO enrollments (student_id, subject_id, semester_id, status, created_at) 
                    VALUES (:student_id, :subject_id, :semester_id, 'enrolled', NOW())
                ");
                $stmt->execute([
                    'student_id' => $studentId,
                    'subject_id' => $input['subject_id'],
                    'semester_id' => $input['semester_id'],
                ]);
                
                // Create empty grade record
                $stmt = $this->db->prepare("
                    INSERT INTO grades (student_id, subject_id, semester_id, created_at) 
                    VALUES (:student_id, :subject_id, :semester_id, NOW())
                ");
                $stmt->execute([
                    'student_id' => $studentId,
                    'subject_id' => $input['subject_id'],
                    'semester_id' => $input['semester_id'],
                ]);
                
                $enrolled++;
            } catch (Exception $e) {
                $errors[] = "Student $studentId: " . $e->getMessage();
            }
        }
        
        Response::success([
            'enrolled' => $enrolled,
            'skipped' => $skipped,
            'errors' => $errors,
        ], "Enrolled $enrolled students ($skipped already enrolled)");
    }
    
    /**
     * Update enrollment
     */
    private function updateEnrollment($id) {
        $input = $this->getInput();
        
        // Check if enrollment exists
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            Response::error('Enrollment not found', 404);
        }
        
        $allowedFields = ['status'];
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
        
        $sql = "UPDATE enrollments SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'Enrollment updated successfully');
    }
    
    /**
     * Delete enrollment
     */
    private function deleteEnrollment($id) {
        // Get enrollment
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $enrollment = $stmt->fetch();
        
        if (!$enrollment) {
            Response::error('Enrollment not found', 404);
        }
        
        // Delete associated grade
        $stmt = $this->db->prepare("
            DELETE FROM grades 
            WHERE student_id = :student_id 
              AND subject_id = :subject_id 
              AND semester_id = :semester_id
        ");
        $stmt->execute([
            'student_id' => $enrollment['student_id'],
            'subject_id' => $enrollment['subject_id'],
            'semester_id' => $enrollment['semester_id'],
        ]);
        
        // Delete enrollment
        $stmt = $this->db->prepare("DELETE FROM enrollments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        Response::success(null, 'Enrollment deleted successfully');
    }
    
    /**
     * Check if student has completed prerequisites for a subject
     */
    private function checkPrerequisites($studentId, $subjectId) {
        // Get prerequisites
        $stmt = $this->db->prepare("
            SELECT sp.prerequisite_id, s.code as prerequisite_code, s.name as prerequisite_name
            FROM subject_prerequisites sp
            JOIN subjects s ON sp.prerequisite_id = s.id
            WHERE sp.subject_id = :subject_id
        ");
        $stmt->execute(['subject_id' => $subjectId]);
        $prerequisites = $stmt->fetchAll();
        
        if (empty($prerequisites)) {
            return ['passed' => true, 'missing' => []];
        }
        
        $missing = [];
        
        foreach ($prerequisites as $prereq) {
            // Check if student has passed this prerequisite (final_grade exists and passed)
            $stmt = $this->db->prepare("
                SELECT g.final_grade FROM grades g
                WHERE g.student_id = :student_id 
                  AND g.subject_id = :subject_id
                  AND g.final_grade IS NOT NULL
                  AND g.final_grade <= 3.0
            ");
            $stmt->execute([
                'student_id' => $studentId,
                'subject_id' => $prereq['prerequisite_id'],
            ]);
            
            if (!$stmt->fetch()) {
                $missing[] = $prereq['prerequisite_code'] . ' - ' . $prereq['prerequisite_name'];
            }
        }
        
        return [
            'passed' => empty($missing),
            'missing' => $missing,
        ];
    }
}
