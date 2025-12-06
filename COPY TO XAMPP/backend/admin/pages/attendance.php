<?php
/**
 * Attendance Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    // Check for duplicate
                    $checkStmt = $db->prepare("
                        SELECT id FROM attendance 
                        WHERE student_id = :student_id AND subject_id = :subject_id AND date = :date
                    ");
                    $checkStmt->execute([
                        'student_id' => $_POST['student_id'],
                        'subject_id' => $_POST['subject_id'],
                        'date' => $_POST['date'],
                    ]);
                    
                    if ($checkStmt->fetch()) {
                        $message = 'Attendance record already exists for this student, subject, and date.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO attendance (student_id, subject_id, semester_id, date, status, remarks)
                            VALUES (:student_id, :subject_id, :semester_id, :date, :status, :remarks)
                        ");
                        $stmt->execute([
                            'student_id' => $_POST['student_id'],
                            'subject_id' => $_POST['subject_id'],
                            'semester_id' => $_POST['semester_id'],
                            'date' => $_POST['date'],
                            'status' => $_POST['status'],
                            'remarks' => $_POST['remarks'] ?? null,
                        ]);
                        $message = 'Attendance record added successfully!';
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
                        UPDATE attendance SET 
                            status = :status,
                            remarks = :remarks
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'status' => $_POST['status'],
                        'remarks' => $_POST['remarks'] ?? null,
                    ]);
                    $message = 'Attendance updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM attendance WHERE id = :id");
                    $stmt->execute(['id' => $_POST['id']]);
                    $message = 'Attendance record deleted successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'bulk_add':
                try {
                    $subjectId = $_POST['subject_id'];
                    $semesterId = $_POST['semester_id'];
                    $date = $_POST['date'];
                    $statuses = $_POST['statuses'] ?? [];
                    $remarks = $_POST['remarks_bulk'] ?? [];
                    
                    $insertCount = 0;
                    foreach ($statuses as $studentId => $status) {
                        if (!$status) continue;
                        
                        // Check if exists
                        $checkStmt = $db->prepare("
                            SELECT id FROM attendance 
                            WHERE student_id = :student_id AND subject_id = :subject_id AND date = :date
                        ");
                        $checkStmt->execute([
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'date' => $date,
                        ]);
                        
                        if ($checkStmt->fetch()) {
                            // Update existing
                            $stmt = $db->prepare("
                                UPDATE attendance SET status = :status, remarks = :remarks 
                                WHERE student_id = :student_id AND subject_id = :subject_id AND date = :date
                            ");
                        } else {
                            // Insert new
                            $stmt = $db->prepare("
                                INSERT INTO attendance (student_id, subject_id, semester_id, date, status, remarks)
                                VALUES (:student_id, :subject_id, :semester_id, :date, :status, :remarks)
                            ");
                        }
                        
                        $params = [
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'date' => $date,
                            'status' => $status,
                            'remarks' => $remarks[$studentId] ?? null,
                        ];
                        
                        if (!$checkStmt->fetch()) {
                            $params['semester_id'] = $semesterId;
                        }
                        
                        $stmt->execute($params);
                        $insertCount++;
                    }
                    
                    $message = "Attendance recorded for $insertCount students!";
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Filter parameters
$selectedSemester = $_GET['semester'] ?? '';
$selectedSubject = $_GET['subject'] ?? '';
$selectedDate = $_GET['date'] ?? '';

// Get all semesters for filter
$semesters = $db->query("SELECT * FROM semesters ORDER BY academic_year DESC, semester_number DESC")->fetchAll();

// Get subjects for filter
$subjects = $db->query("SELECT id, code, name FROM subjects ORDER BY code")->fetchAll();

// Get students for adding
$students = $db->query("SELECT id, student_id, first_name, last_name FROM students WHERE status = 'active' ORDER BY student_id")->fetchAll();

// Build query
$sql = "
    SELECT a.*, 
           st.student_id as student_number, st.first_name, st.last_name,
           sub.code as subject_code, sub.name as subject_name,
           sem.name as semester_name, sem.academic_year
    FROM attendance a
    JOIN students st ON a.student_id = st.id
    JOIN subjects sub ON a.subject_id = sub.id
    JOIN semesters sem ON a.semester_id = sem.id
    WHERE 1=1
";

$params = [];

if ($selectedSemester) {
    $sql .= " AND a.semester_id = :semester_id";
    $params['semester_id'] = $selectedSemester;
}

if ($selectedSubject) {
    $sql .= " AND a.subject_id = :subject_id";
    $params['subject_id'] = $selectedSubject;
}

if ($selectedDate) {
    $sql .= " AND a.date = :date";
    $params['date'] = $selectedDate;
}

$sql .= " ORDER BY a.date DESC, st.student_id ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$attendance = $stmt->fetchAll();

// Get enrolled students for bulk entry
$enrolledStudents = [];
if ($selectedSubject && $selectedSemester) {
    $enrollStmt = $db->prepare("
        SELECT s.id, s.student_id, s.first_name, s.last_name
        FROM students s
        JOIN enrollments e ON s.id = e.student_id
        WHERE e.subject_id = :subject_id AND e.semester_id = :semester_id AND e.status = 'enrolled'
        ORDER BY s.student_id
    ");
    $enrollStmt->execute(['subject_id' => $selectedSubject, 'semester_id' => $selectedSemester]);
    $enrolledStudents = $enrollStmt->fetchAll();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check me-2"></i>Attendance Management</h2>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-wmsu" data-bs-toggle="modal" data-bs-target="#bulkAttendanceModal">
            <i class="bi bi-people me-2"></i>Bulk Entry
        </button>
        <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
            <i class="bi bi-plus-lg me-2"></i>Add Record
        </button>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="attendance">
            <div class="col-md-3">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select" onchange="this.form.submit()">
                    <option value="">All Semesters</option>
                    <?php foreach ($semesters as $semester): ?>
                    <option value="<?= $semester['id'] ?>" <?= $selectedSemester == $semester['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                        <?= $semester['is_current'] ? ' (Current)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject" class="form-select" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                    <option value="<?= $subject['id'] ?>" <?= $selectedSubject == $subject['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subject['code'] . ' - ' . $subject['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="?page=attendance" class="btn btn-outline-secondary w-100">Clear Filters</a>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Summary -->
<?php if ($selectedSubject && $selectedSemester): 
    $summaryStmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
        FROM attendance 
        WHERE subject_id = :subject_id AND semester_id = :semester_id
    ");
    $summaryStmt->execute(['subject_id' => $selectedSubject, 'semester_id' => $selectedSemester]);
    $summary = $summaryStmt->fetch();
?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?= $summary['present'] ?? 0 ?></h3>
                <small>Present</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3><?= $summary['absent'] ?? 0 ?></h3>
                <small>Absent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3><?= $summary['late'] ?? 0 ?></h3>
                <small>Late</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><?= $summary['excused'] ?? 0 ?></h3>
                <small>Excused</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><strong><?= date('M d, Y', strtotime($record['date'])) ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($record['student_number']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($record['subject_code']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($record['subject_name']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= 
                                $record['status'] === 'present' ? 'bg-success' : 
                                ($record['status'] === 'absent' ? 'bg-danger' : 
                                ($record['status'] === 'late' ? 'bg-warning text-dark' : 'bg-info')) 
                            ?>">
                                <?= ucfirst($record['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($record['remarks'] ?? '-') ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editAttendance(<?= json_encode($record) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAttendance(<?= $record['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($attendance)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No attendance records found. Use the filters above or add new records.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Single Attendance Modal -->
<div class="modal fade" id="addAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attendance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student *</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional remarks">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Attendance Modal -->
<div class="modal fade" id="bulkAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Attendance Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="bulk_add">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Select a subject and semester from the filters above to see enrolled students.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Subject *</label>
                            <select name="subject_id" class="form-select" required>
                                <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>" <?= $selectedSubject == $subject['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['code'] . ' - ' . $subject['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester *</label>
                            <select name="semester_id" class="form-select" required>
                                <?php foreach ($semesters as $semester): ?>
                                <option value="<?= $semester['id'] ?>" <?= ($selectedSemester == $semester['id'] || $semester['is_current']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date *</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-success me-2" onclick="setAllStatus('present')">All Present</button>
                        <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="setAllStatus('absent')">All Absent</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAllStatus('')">Clear All</button>
                    </div>
                    
                    <?php if (!empty($enrolledStudents)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th style="width: 150px;">Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrolledStudents as $student): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($student['student_id']) ?></strong>
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </td>
                                    <td>
                                        <select name="statuses[<?= $student['id'] ?>]" class="form-select form-select-sm status-select">
                                            <option value="">-</option>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="remarks_bulk[<?= $student['id'] ?>]" class="form-control form-control-sm" placeholder="Optional">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                        <p class="mt-2">No enrolled students found. Please select a subject and semester with enrollments.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu" <?= empty($enrolledStudents) ? 'disabled' : '' ?>>Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_attendance_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="edit_student_info"></span><br>
                        <strong>Subject:</strong> <span id="edit_subject_info"></span><br>
                        <strong>Date:</strong> <span id="edit_date_info"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" id="edit_remarks" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this attendance record?</p>
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

<style>
.btn-outline-wmsu {
    color: var(--wmsu-crimson);
    border-color: var(--wmsu-crimson);
}
.btn-outline-wmsu:hover {
    background: var(--wmsu-crimson);
    color: white;
}
</style>

<script>
function editAttendance(record) {
    document.getElementById('edit_attendance_id').value = record.id;
    document.getElementById('edit_student_info').textContent = record.student_number + ' - ' + record.first_name + ' ' + record.last_name;
    document.getElementById('edit_subject_info').textContent = record.subject_code + ' - ' + record.subject_name;
    document.getElementById('edit_date_info').textContent = record.date;
    document.getElementById('edit_status').value = record.status;
    document.getElementById('edit_remarks').value = record.remarks || '';
    
    new bootstrap.Modal(document.getElementById('editAttendanceModal')).show();
}

function deleteAttendance(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteAttendanceModal')).show();
}

function setAllStatus(status) {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = status;
    });
}
</script>
