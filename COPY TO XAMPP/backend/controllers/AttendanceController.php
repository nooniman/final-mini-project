<?php
/**
 * Attendance Controller
 * Handles attendance-related operations
 */

class AttendanceController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        switch ($action) {
            case '':
            case 'list':
                $this->listAttendance();
                break;
                
            case 'subject':
                if ($id) {
                    $this->getBySubject($id);
                } else {
                    Response::error('Subject ID required', 400);
                }
                break;
                
            case 'summary':
                $this->getSummary();
                break;
                
            default:
                Response::error('Invalid attendance action', 404);
        }
    }
    
    /**
     * List attendance records
     */
    private function listAttendance() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        $subjectId = $params['subject_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;
        
        $sql = "
            SELECT a.*, s.code as subject_code, s.name as subject_name
            FROM attendance a
            JOIN subjects s ON a.subject_id = s.id
            WHERE a.student_id = :student_id
        ";
        
        $queryParams = ['student_id' => $auth['user_id']];
        
        if ($semesterId) {
            $sql .= " AND a.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        if ($subjectId) {
            $sql .= " AND a.subject_id = :subject_id";
            $queryParams['subject_id'] = $subjectId;
        }
        
        if ($startDate) {
            $sql .= " AND a.date >= :start_date";
            $queryParams['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND a.date <= :end_date";
            $queryParams['end_date'] = $endDate;
        }
        
        $sql .= " ORDER BY a.date DESC, s.code ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $attendance = $stmt->fetchAll();
        
        Response::success($attendance);
    }
    
    /**
     * Get attendance for specific subject
     */
    private function getBySubject($subjectId) {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT a.*, s.code as subject_code, s.name as subject_name
            FROM attendance a
            JOIN subjects s ON a.subject_id = s.id
            WHERE a.student_id = :student_id AND a.subject_id = :subject_id
        ";
        
        $queryParams = [
            'student_id' => $auth['user_id'],
            'subject_id' => $subjectId,
        ];
        
        if ($semesterId) {
            $sql .= " AND a.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY a.date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $records = $stmt->fetchAll();
        
        // Calculate summary
        $summary = [
            'total' => count($records),
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];
        
        foreach ($records as $record) {
            if (isset($summary[$record['status']])) {
                $summary[$record['status']]++;
            }
        }
        
        $summary['attendanceRate'] = $summary['total'] > 0 
            ? round(($summary['present'] + $summary['late']) / $summary['total'] * 100, 1) 
            : 0;
        
        Response::success([
            'records' => $records,
            'summary' => $summary,
        ]);
    }
    
    /**
     * Get attendance summary
     */
    private function getSummary() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT 
                s.id as subject_id,
                s.code as subject_code,
                s.name as subject_name,
                COUNT(a.id) as total_classes,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
            FROM attendance a
            JOIN subjects s ON a.subject_id = s.id
            WHERE a.student_id = :student_id
        ";
        
        $queryParams = ['student_id' => $auth['user_id']];
        
        if ($semesterId) {
            $sql .= " AND a.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " GROUP BY s.id ORDER BY s.code";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $summary = $stmt->fetchAll();
        
        // Calculate attendance rate for each subject
        foreach ($summary as &$item) {
            $attended = $item['present_count'] + $item['late_count'];
            $item['attendance_rate'] = $item['total_classes'] > 0
                ? round($attended / $item['total_classes'] * 100, 1)
                : 0;
        }
        
        Response::success($summary);
    }
}
