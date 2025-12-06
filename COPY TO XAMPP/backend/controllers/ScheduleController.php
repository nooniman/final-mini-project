<?php
/**
 * Schedule Controller
 * Handles class schedule operations
 */

class ScheduleController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($method) {
            case 'GET':
                if ($action === '' || $action === 'list') {
                    $this->listSchedules();
                } elseif (is_numeric($action)) {
                    $this->getSchedule($action);
                } elseif ($action === 'subject') {
                    $this->getBySubject($id);
                } elseif ($action === 'day') {
                    $this->getByDay($id);
                } elseif ($action === 'timetable') {
                    $this->getTimetable();
                } else {
                    Response::error('Invalid schedules action', 404);
                }
                break;
                
            case 'POST':
                $this->createSchedule();
                break;
                
            case 'PUT':
                if (is_numeric($action)) {
                    $this->updateSchedule($action);
                } else {
                    Response::error('Schedule ID required', 400);
                }
                break;
                
            case 'DELETE':
                if (is_numeric($action)) {
                    $this->deleteSchedule($action);
                } else {
                    Response::error('Schedule ID required', 400);
                }
                break;
                
            default:
                Response::error('Method not allowed', 405);
        }
    }
    
    /**
     * List all schedules with filters
     */
    private function listSchedules() {
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        $subjectId = $params['subject_id'] ?? null;
        $day = $params['day'] ?? null;
        
        $sql = "
            SELECT sc.*, 
                   s.code as subject_code, s.name as subject_name, s.units,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name,
                   sem.name as semester_name, sem.academic_year
            FROM schedules sc
            JOIN subjects s ON sc.subject_id = s.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            JOIN semesters sem ON sc.semester_id = sem.id
            WHERE 1=1
        ";
        $queryParams = [];
        
        if ($semesterId) {
            $sql .= " AND sc.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        } else {
            // Default to current semester
            $sql .= " AND sem.is_current = 1";
        }
        
        if ($subjectId) {
            $sql .= " AND sc.subject_id = :subject_id";
            $queryParams['subject_id'] = $subjectId;
        }
        
        if ($day) {
            $sql .= " AND sc.day = :day";
            $queryParams['day'] = $day;
        }
        
        $sql .= " ORDER BY FIELD(sc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sc.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $schedules = $stmt->fetchAll();
        
        Response::success($schedules);
    }
    
    /**
     * Get single schedule
     */
    private function getSchedule($id) {
        $stmt = $this->db->prepare("
            SELECT sc.*, 
                   s.code as subject_code, s.name as subject_name, s.units,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name,
                   sem.name as semester_name, sem.academic_year
            FROM schedules sc
            JOIN subjects s ON sc.subject_id = s.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            JOIN semesters sem ON sc.semester_id = sem.id
            WHERE sc.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $schedule = $stmt->fetch();
        
        if (!$schedule) {
            Response::error('Schedule not found', 404);
        }
        
        Response::success($schedule);
    }
    
    /**
     * Get schedules by subject
     */
    private function getBySubject($subjectId) {
        if (!$subjectId) {
            Response::error('Subject ID required', 400);
        }
        
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT sc.*, sem.name as semester_name, sem.academic_year
            FROM schedules sc
            JOIN semesters sem ON sc.semester_id = sem.id
            WHERE sc.subject_id = :subject_id
        ";
        $queryParams = ['subject_id' => $subjectId];
        
        if ($semesterId) {
            $sql .= " AND sc.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        }
        
        $sql .= " ORDER BY sem.academic_year DESC, FIELD(sc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sc.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $schedules = $stmt->fetchAll();
        
        Response::success($schedules);
    }
    
    /**
     * Get schedules by day
     */
    private function getByDay($day) {
        if (!$day) {
            Response::error('Day is required', 400);
        }
        
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        if (!in_array($day, $validDays)) {
            Response::error('Invalid day', 400);
        }
        
        $stmt = $this->db->prepare("
            SELECT sc.*, 
                   s.code as subject_code, s.name as subject_name,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name,
                   sem.name as semester_name
            FROM schedules sc
            JOIN subjects s ON sc.subject_id = s.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            JOIN semesters sem ON sc.semester_id = sem.id
            WHERE sc.day = :day AND sem.is_current = 1
            ORDER BY sc.start_time
        ");
        $stmt->execute(['day' => $day]);
        $schedules = $stmt->fetchAll();
        
        Response::success($schedules);
    }
    
    /**
     * Get timetable view (schedules grouped by day)
     */
    private function getTimetable() {
        $params = $this->getQueryParams();
        $semesterId = $params['semester_id'] ?? null;
        
        $sql = "
            SELECT sc.*, 
                   s.code as subject_code, s.name as subject_name,
                   i.first_name as instructor_first_name, i.last_name as instructor_last_name
            FROM schedules sc
            JOIN subjects s ON sc.subject_id = s.id
            LEFT JOIN instructors i ON s.instructor_id = i.id
            JOIN semesters sem ON sc.semester_id = sem.id
            WHERE 1=1
        ";
        $queryParams = [];
        
        if ($semesterId) {
            $sql .= " AND sc.semester_id = :semester_id";
            $queryParams['semester_id'] = $semesterId;
        } else {
            $sql .= " AND sem.is_current = 1";
        }
        
        $sql .= " ORDER BY FIELD(sc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sc.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);
        $allSchedules = $stmt->fetchAll();
        
        // Group by day
        $timetable = [
            'Monday' => [],
            'Tuesday' => [],
            'Wednesday' => [],
            'Thursday' => [],
            'Friday' => [],
            'Saturday' => [],
            'Sunday' => [],
        ];
        
        foreach ($allSchedules as $schedule) {
            $timetable[$schedule['day']][] = $schedule;
        }
        
        Response::success($timetable);
    }
    
    /**
     * Create new schedule
     */
    private function createSchedule() {
        $input = $this->getInput();
        $this->validateRequired($input, ['subject_id', 'semester_id', 'day', 'start_time', 'end_time']);
        
        // Validate day
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        if (!in_array($input['day'], $validDays)) {
            Response::error('Invalid day', 400);
        }
        
        // Check for time conflicts in same room
        if (!empty($input['room'])) {
            $conflict = $this->checkRoomConflict(
                $input['semester_id'],
                $input['day'],
                $input['start_time'],
                $input['end_time'],
                $input['room']
            );
            
            if ($conflict) {
                Response::error('Room is already booked for this time slot', 409);
            }
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO schedules (subject_id, semester_id, day, start_time, end_time, room, type, created_at) 
            VALUES (:subject_id, :semester_id, :day, :start_time, :end_time, :room, :type, NOW())
        ");
        $stmt->execute([
            'subject_id' => $input['subject_id'],
            'semester_id' => $input['semester_id'],
            'day' => $input['day'],
            'start_time' => $input['start_time'],
            'end_time' => $input['end_time'],
            'room' => $input['room'] ?? null,
            'type' => $input['type'] ?? 'lecture',
        ]);
        
        $scheduleId = $this->db->lastInsertId();
        
        Response::success(['id' => $scheduleId], 'Schedule created successfully', 201);
    }
    
    /**
     * Update schedule
     */
    private function updateSchedule($id) {
        $input = $this->getInput();
        
        // Check if schedule exists
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $schedule = $stmt->fetch();
        
        if (!$schedule) {
            Response::error('Schedule not found', 404);
        }
        
        // Validate day if provided
        if (isset($input['day'])) {
            $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            if (!in_array($input['day'], $validDays)) {
                Response::error('Invalid day', 400);
            }
        }
        
        // Check for room conflicts if room or time is being changed
        $room = $input['room'] ?? $schedule['room'];
        if (!empty($room)) {
            $semesterId = $input['semester_id'] ?? $schedule['semester_id'];
            $day = $input['day'] ?? $schedule['day'];
            $startTime = $input['start_time'] ?? $schedule['start_time'];
            $endTime = $input['end_time'] ?? $schedule['end_time'];
            
            $conflict = $this->checkRoomConflict($semesterId, $day, $startTime, $endTime, $room, $id);
            
            if ($conflict) {
                Response::error('Room is already booked for this time slot', 409);
            }
        }
        
        $allowedFields = ['subject_id', 'semester_id', 'day', 'start_time', 'end_time', 'room', 'type'];
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
        
        $sql = "UPDATE schedules SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'Schedule updated successfully');
    }
    
    /**
     * Delete schedule
     */
    private function deleteSchedule($id) {
        // Check if schedule exists
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            Response::error('Schedule not found', 404);
        }
        
        $stmt = $this->db->prepare("DELETE FROM schedules WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        Response::success(null, 'Schedule deleted successfully');
    }
    
    /**
     * Check for room conflicts
     */
    private function checkRoomConflict($semesterId, $day, $startTime, $endTime, $room, $excludeId = null) {
        $sql = "
            SELECT id FROM schedules 
            WHERE semester_id = :semester_id 
              AND day = :day 
              AND room = :room
              AND (
                  (start_time < :end_time AND end_time > :start_time)
              )
        ";
        $params = [
            'semester_id' => $semesterId,
            'day' => $day,
            'room' => $room,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
}
