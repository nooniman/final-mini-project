<?php
/**
 * Courses Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $stmt = $db->prepare("
                        INSERT INTO courses (code, name, department)
                        VALUES (:code, :name, :department)
                    ");
                    $stmt->execute([
                        'code' => strtoupper($_POST['code']),
                        'name' => $_POST['name'],
                        'department' => $_POST['department'] ?? '',
                    ]);
                    $message = 'Course added successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $stmt = $db->prepare("
                        UPDATE courses SET 
                            code = :code,
                            name = :name,
                            department = :department
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'code' => strtoupper($_POST['code']),
                        'name' => $_POST['name'],
                        'department' => $_POST['department'] ?? '',
                    ]);
                    $message = 'Course updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    // Check if course has students
                    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE course = (SELECT code FROM courses WHERE id = :id)");
                    $checkStmt->execute(['id' => $_POST['id']]);
                    $count = $checkStmt->fetch()['count'];
                    
                    if ($count > 0) {
                        $message = 'Cannot delete course with enrolled students.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("DELETE FROM courses WHERE id = :id");
                        $stmt->execute(['id' => $_POST['id']]);
                        $message = 'Course deleted successfully!';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get all courses with student count
$courses = $db->query("
    SELECT c.*, COUNT(s.id) as student_count
    FROM courses c
    LEFT JOIN students s ON c.code = s.course
    GROUP BY c.id
    ORDER BY c.code ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Courses Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addCourseModal">
        <i class="bi bi-plus-lg me-2"></i>Add Course
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($course['code']) ?></strong></td>
                        <td><?= htmlspecialchars($course['name']) ?></td>
                        <td><?= htmlspecialchars($course['department'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $course['student_count'] ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editCourse(<?= json_encode($course) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(<?= $course['id'] ?>, '<?= htmlspecialchars($course['code']) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No courses found. Add your first course!
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g., BSCS" required>
                        <small class="text-muted">Will be converted to uppercase</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Bachelor of Science in Computer Science" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g., College of Computing Studies">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_course_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" name="code" id="edit_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" id="edit_department" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete course <strong id="delete_course_code"></strong>?</p>
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
function editCourse(course) {
    document.getElementById('edit_course_id').value = course.id;
    document.getElementById('edit_code').value = course.code;
    document.getElementById('edit_name').value = course.name;
    document.getElementById('edit_department').value = course.department || '';
    
    new bootstrap.Modal(document.getElementById('editCourseModal')).show();
}

function deleteCourse(id, code) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_course_code').textContent = code;
    
    new bootstrap.Modal(document.getElementById('deleteCourseModal')).show();
}
</script>
