<?php
/**
 * Schedules Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    // Check for time conflict
                    $conflictStmt = $db->prepare("
                        SELECT s.*, sub.code as subject_code 
                        FROM schedules s
                        JOIN subjects sub ON s.subject_id = sub.id
                        WHERE s.semester_id = :semester_id 
                        AND s.day = :day 
                        AND s.room = :room
                        AND ((s.start_time <= :start_time AND s.end_time > :start_time)
                             OR (s.start_time < :end_time AND s.end_time >= :end_time)
                             OR (s.start_time >= :start_time AND s.end_time <= :end_time))
                    ");
                    $conflictStmt->execute([
                        'semester_id' => $_POST['semester_id'],
                        'day' => $_POST['day'],
                        'room' => $_POST['room'],
                        'start_time' => $_POST['start_time'],
                        'end_time' => $_POST['end_time'],
                    ]);
                    
                    $conflict = $conflictStmt->fetch();
                    if ($conflict) {
                        $message = "Schedule conflict! Room is already booked for {$conflict['subject_code']} from {$conflict['start_time']} to {$conflict['end_time']}.";
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO schedules (subject_id, semester_id, day, start_time, end_time, room, type)
                            VALUES (:subject_id, :semester_id, :day, :start_time, :end_time, :room, :type)
                        ");
                        $stmt->execute([
                            'subject_id' => $_POST['subject_id'],
                            'semester_id' => $_POST['semester_id'],
                            'day' => $_POST['day'],
                            'start_time' => $_POST['start_time'],
                            'end_time' => $_POST['end_time'],
                            'room' => $_POST['room'],
                            'type' => $_POST['type'],
                        ]);
                        $message = 'Schedule added successfully!';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $stmt = $db->prepare("
                        UPDATE schedules SET 
                            subject_id = :subject_id,
                            semester_id = :semester_id,
                            day = :day,
                            start_time = :start_time,
                            end_time = :end_time,
                            room = :room,
                            type = :type
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'subject_id' => $_POST['subject_id'],
                        'semester_id' => $_POST['semester_id'],
                        'day' => $_POST['day'],
                        'start_time' => $_POST['start_time'],
                        'end_time' => $_POST['end_time'],
                        'room' => $_POST['room'],
                        'type' => $_POST['type'],
                    ]);
                    $message = 'Schedule updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM schedules WHERE id = :id");
                    $stmt->execute(['id' => $_POST['id']]);
                    $message = 'Schedule deleted successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Filter by semester
$selectedSemester = $_GET['semester'] ?? '';

// Get all semesters
$semesters = $db->query("SELECT * FROM semesters ORDER BY academic_year DESC, semester_number DESC")->fetchAll();

// Get all subjects
$subjects = $db->query("SELECT id, code, name FROM subjects ORDER BY code")->fetchAll();

// Build query
$sql = "
    SELECT sch.*, 
           sub.code as subject_code, sub.name as subject_name, sub.units,
           sem.name as semester_name, sem.academic_year,
           i.first_name as instructor_first, i.last_name as instructor_last
    FROM schedules sch
    JOIN subjects sub ON sch.subject_id = sub.id
    JOIN semesters sem ON sch.semester_id = sem.id
    LEFT JOIN instructors i ON sub.instructor_id = i.id
";

if ($selectedSemester) {
    $sql .= " WHERE sch.semester_id = :semester_id";
}

$sql .= " ORDER BY FIELD(sch.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sch.start_time";

$stmt = $db->prepare($sql);
if ($selectedSemester) {
    $stmt->execute(['semester_id' => $selectedSemester]);
} else {
    $stmt->execute();
}
$schedules = $stmt->fetchAll();

// Group schedules by day for timetable view
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$schedulesByDay = [];
foreach ($days as $day) {
    $schedulesByDay[$day] = array_filter($schedules, function($s) use ($day) {
        return $s['day'] === $day;
    });
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clock me-2"></i>Schedules Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
        <i class="bi bi-plus-lg me-2"></i>Add Schedule
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Semester Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Filter by Semester</label>
                <select class="form-select" onchange="window.location.href='?page=schedules&semester=' + this.value">
                    <option value="">All Semesters</option>
                    <?php foreach ($semesters as $semester): ?>
                    <option value="<?= $semester['id'] ?>" <?= $selectedSemester == $semester['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                        <?= $semester['is_current'] ? ' (Current)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary active" onclick="showView('table')">
                        <i class="bi bi-table me-1"></i>Table View
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="showView('timetable')">
                        <i class="bi bi-calendar-week me-1"></i>Timetable View
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table View -->
<div id="tableView" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Instructor</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($schedule['subject_code']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($schedule['subject_name']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?= htmlspecialchars($schedule['day']) ?></span>
                        </td>
                        <td>
                            <?= date('h:i A', strtotime($schedule['start_time'])) ?> - 
                            <?= date('h:i A', strtotime($schedule['end_time'])) ?>
                        </td>
                        <td><strong><?= htmlspecialchars($schedule['room']) ?></strong></td>
                        <td>
                            <span class="badge <?= 
                                $schedule['type'] === 'Lecture' ? 'bg-info' : 
                                ($schedule['type'] === 'Laboratory' ? 'bg-success' : 'bg-warning text-dark') 
                            ?>">
                                <?= htmlspecialchars($schedule['type']) ?>
                            </span>
                        </td>
                        <td><?= $schedule['instructor_first'] ? htmlspecialchars($schedule['instructor_first'] . ' ' . $schedule['instructor_last']) : '-' ?></td>
                        <td>
                            <?= htmlspecialchars($schedule['semester_name']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($schedule['academic_year']) ?></small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editSchedule(<?= json_encode($schedule) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(<?= $schedule['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No schedules found. Add your first schedule!
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Timetable View -->
<div id="timetableView" class="card" style="display: none;">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered timetable">
                <thead>
                    <tr>
                        <th style="width: 100px;">Time</th>
                        <?php foreach ($days as $day): ?>
                        <th><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
                    foreach ($timeSlots as $time):
                        $timeObj = strtotime($time);
                    ?>
                    <tr>
                        <td class="text-center"><small><?= date('h:i A', $timeObj) ?></small></td>
                        <?php foreach ($days as $day): ?>
                        <td class="schedule-cell">
                            <?php 
                            foreach ($schedulesByDay[$day] as $sch):
                                $startTime = strtotime($sch['start_time']);
                                $endTime = strtotime($sch['end_time']);
                                if ($startTime <= $timeObj && $endTime > $timeObj):
                            ?>
                            <div class="schedule-block <?= strtolower($sch['type']) ?>">
                                <strong><?= htmlspecialchars($sch['subject_code']) ?></strong><br>
                                <small><?= $sch['room'] ?></small><br>
                                <small><?= date('h:i', $startTime) ?>-<?= date('h:i A', $endTime) ?></small>
                            </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.timetable th, .timetable td {
    font-size: 0.8rem;
    vertical-align: top;
    min-width: 120px;
}
.schedule-cell {
    height: 60px;
    position: relative;
}
.schedule-block {
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    margin-bottom: 2px;
}
.schedule-block.lecture {
    background: #cfe2ff;
    border-left: 3px solid #0d6efd;
}
.schedule-block.laboratory {
    background: #d1e7dd;
    border-left: 3px solid #198754;
}
.schedule-block.tutorial {
    background: #fff3cd;
    border-left: 3px solid #ffc107;
}
</style>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>">
                                <?= htmlspecialchars($subject['code'] . ' - ' . $subject['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester *</label>
                        <select name="semester_id" class="form-select" required>
                            <?php foreach ($semesters as $semester): ?>
                            <option value="<?= $semester['id'] ?>" <?= $semester['is_current'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Day *</label>
                            <select name="day" class="form-select" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="Lecture">Lecture</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Tutorial">Tutorial</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room *</label>
                        <input type="text" name="room" class="form-control" placeholder="e.g., CL-301" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_schedule_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <select name="subject_id" id="edit_subject_id" class="form-select" required>
                            <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>">
                                <?= htmlspecialchars($subject['code'] . ' - ' . $subject['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester *</label>
                        <select name="semester_id" id="edit_semester_id" class="form-select" required>
                            <?php foreach ($semesters as $semester): ?>
                            <option value="<?= $semester['id'] ?>">
                                <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Day *</label>
                            <select name="day" id="edit_day" class="form-select" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="Lecture">Lecture</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Tutorial">Tutorial</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room *</label>
                        <input type="text" name="room" id="edit_room" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this schedule?</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showView(view) {
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    if (view === 'table') {
        document.getElementById('tableView').style.display = 'block';
        document.getElementById('timetableView').style.display = 'none';
    } else {
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('timetableView').style.display = 'block';
    }
}

function editSchedule(schedule) {
    document.getElementById('edit_schedule_id').value = schedule.id;
    document.getElementById('edit_subject_id').value = schedule.subject_id;
    document.getElementById('edit_semester_id').value = schedule.semester_id;
    document.getElementById('edit_day').value = schedule.day;
    document.getElementById('edit_type').value = schedule.type;
    document.getElementById('edit_start_time').value = schedule.start_time;
    document.getElementById('edit_end_time').value = schedule.end_time;
    document.getElementById('edit_room').value = schedule.room;
    
    new bootstrap.Modal(document.getElementById('editScheduleModal')).show();
}

function deleteSchedule(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteScheduleModal')).show();
}
</script>
