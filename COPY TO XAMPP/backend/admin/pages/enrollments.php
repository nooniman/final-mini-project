<?php
/**
 * Enrollments Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    // Check if already enrolled
                    $checkStmt = $db->prepare("
                        SELECT id FROM enrollments 
                        WHERE student_id = :student_id AND subject_id = :subject_id AND semester_id = :semester_id
                    ");
                    $checkStmt->execute([
                        'student_id' => $_POST['student_id'],
                        'subject_id' => $_POST['subject_id'],
                        'semester_id' => $_POST['semester_id'],
                    ]);
                    
                    if ($checkStmt->fetch()) {
                        $message = 'Student is already enrolled in this subject for this semester.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO enrollments (student_id, subject_id, semester_id, status)
                            VALUES (:student_id, :subject_id, :semester_id, :status)
                        ");
                        $stmt->execute([
                            'student_id' => $_POST['student_id'],
                            'subject_id' => $_POST['subject_id'],
                            'semester_id' => $_POST['semester_id'],
                            'status' => $_POST['status'],
                        ]);
                        
                        // Also create a grade entry
                        $gradeStmt = $db->prepare("
                            INSERT INTO grades (student_id, subject_id, semester_id)
                            VALUES (:student_id, :subject_id, :semester_id)
                        ");
                        $gradeStmt->execute([
                            'student_id' => $_POST['student_id'],
                            'subject_id' => $_POST['subject_id'],
                            'semester_id' => $_POST['semester_id'],
                        ]);
                        
                        $message = 'Enrollment added successfully!';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $stmt = $db->prepare("UPDATE enrollments SET status = :status WHERE id = :id");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'status' => $_POST['status'],
                    ]);
                    $message = 'Enrollment updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    // Get enrollment info first
                    $infoStmt = $db->prepare("SELECT student_id, subject_id, semester_id FROM enrollments WHERE id = :id");
                    $infoStmt->execute(['id' => $_POST['id']]);
                    $enrollInfo = $infoStmt->fetch();
                    
                    if ($enrollInfo) {
                        // Delete related grades
                        $stmt = $db->prepare("DELETE FROM grades WHERE student_id = :student_id AND subject_id = :subject_id AND semester_id = :semester_id");
                        $stmt->execute([
                            'student_id' => $enrollInfo['student_id'],
                            'subject_id' => $enrollInfo['subject_id'],
                            'semester_id' => $enrollInfo['semester_id'],
                        ]);
                    }
                    
                    // Then delete enrollment
                    $stmt = $db->prepare("DELETE FROM enrollments WHERE id = :id");
                    $stmt->execute(['id' => $_POST['id']]);
                    $message = 'Enrollment deleted successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get all enrollments with related data
$enrollments = $db->query("
    SELECT e.*, 
           st.student_id as student_number, st.first_name, st.last_name,
           sub.code as subject_code, sub.name as subject_name,
           sem.name as semester_name, sem.academic_year
    FROM enrollments e
    JOIN students st ON e.student_id = st.id
    JOIN subjects sub ON e.subject_id = sub.id
    JOIN semesters sem ON e.semester_id = sem.id
    ORDER BY sem.academic_year DESC, st.student_id ASC
")->fetchAll();

// Get data for dropdowns
$students = $db->query("SELECT id, student_id, first_name, last_name FROM students WHERE status = 'active' ORDER BY student_id")->fetchAll();
$subjects = $db->query("SELECT id, code, name FROM subjects ORDER BY code")->fetchAll();
$semesters = $db->query("SELECT id, name, academic_year, is_current FROM semesters ORDER BY academic_year DESC, semester_number DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Enrollments Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
        <i class="bi bi-plus-lg me-2"></i>Add Enrollment
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Enrolled At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($enrollment['student_number']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($enrollment['subject_code']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($enrollment['subject_name']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($enrollment['semester_name']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($enrollment['academic_year']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $enrollment['status'] === 'enrolled' ? 'bg-success' : ($enrollment['status'] === 'dropped' ? 'bg-danger' : 'bg-secondary') ?>">
                                <?= ucfirst($enrollment['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($enrollment['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editEnrollment(<?= json_encode($enrollment) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteEnrollment(<?= $enrollment['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Enrollment Modal -->
<div class="modal fade" id="addEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Enrollment</h5>
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
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                            <option value="<?= $semester['id'] ?>" <?= $semester['is_current'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                                <?= $semester['is_current'] ? ' (Current)' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="enrolled">Enrolled</option>
                            <option value="dropped">Dropped</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Enrollment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Enrollment Modal -->
<div class="modal fade" id="editEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Enrollment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_enrollment_id">
                <div class="modal-body">
                    <p><strong>Student:</strong> <span id="edit_student_info"></span></p>
                    <p><strong>Subject:</strong> <span id="edit_subject_info"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="enrolled">Enrolled</option>
                            <option value="dropped">Dropped</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Enrollment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this enrollment?</p>
                <p class="text-danger mb-0"><small>This will also delete any associated grades.</small></p>
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
function editEnrollment(enrollment) {
    document.getElementById('edit_enrollment_id').value = enrollment.id;
    document.getElementById('edit_student_info').textContent = enrollment.student_number + ' - ' + enrollment.first_name + ' ' + enrollment.last_name;
    document.getElementById('edit_subject_info').textContent = enrollment.subject_code + ' - ' + enrollment.subject_name;
    document.getElementById('edit_status').value = enrollment.status;
    
    new bootstrap.Modal(document.getElementById('editEnrollmentModal')).show();
}

function deleteEnrollment(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteEnrollmentModal')).show();
}
</script>
