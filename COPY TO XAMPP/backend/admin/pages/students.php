<?php
/**
 * Students Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $hashedPassword = Auth::hashPassword($_POST['password']);
                    $stmt = $db->prepare("
                        INSERT INTO students (student_id, email, password, first_name, last_name, middle_name, course, year_level, section, status)
                        VALUES (:student_id, :email, :password, :first_name, :last_name, :middle_name, :course, :year_level, :section, :status)
                    ");
                    $stmt->execute([
                        'student_id' => $_POST['student_id'],
                        'email' => $_POST['email'],
                        'password' => $hashedPassword,
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'middle_name' => $_POST['middle_name'] ?? '',
                        'course' => $_POST['course'],
                        'year_level' => $_POST['year_level'],
                        'section' => $_POST['section'] ?? '',
                        'status' => $_POST['status'],
                    ]);
                    $message = 'Student added successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $sql = "UPDATE students SET 
                            student_id = :student_id,
                            email = :email,
                            first_name = :first_name,
                            last_name = :last_name,
                            middle_name = :middle_name,
                            course = :course,
                            year_level = :year_level,
                            section = :section,
                            status = :status
                            WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'student_id' => $_POST['student_id'],
                        'email' => $_POST['email'],
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'middle_name' => $_POST['middle_name'] ?? '',
                        'course' => $_POST['course'],
                        'year_level' => $_POST['year_level'],
                        'section' => $_POST['section'] ?? '',
                        'status' => $_POST['status'],
                    ]);
                    $message = 'Student updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM students WHERE id = :id");
                    $stmt->execute(['id' => $_POST['id']]);
                    $message = 'Student deleted successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get all students
$students = $db->query("SELECT * FROM students ORDER BY student_id ASC")->fetchAll();

// Get all courses for dropdown
$courses = $db->query("SELECT * FROM courses ORDER BY code ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Students Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="bi bi-plus-lg me-2"></i>Add Student
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
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($student['student_id']) ?></strong></td>
                        <td><?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'][0] . '. ' : '') . $student['last_name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= htmlspecialchars($student['course']) ?></td>
                        <td><?= htmlspecialchars($student['year_level']) ?></td>
                        <td><?= htmlspecialchars($student['section'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $student['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                <?= ucfirst($student['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editStudent(<?= json_encode($student) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['student_id']) ?>')">
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student ID *</label>
                            <input type="text" name="student_id" class="form-control" placeholder="e.g., 2024-00001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course *</label>
                            <select name="course" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['code']) ?>">
                                    <?= htmlspecialchars($course['code'] . ' - ' . $course['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year Level *</label>
                            <select name="year_level" class="form-select" required>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" class="form-control" placeholder="e.g., A">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student ID *</label>
                            <input type="text" name="student_id" id="edit_student_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" id="edit_middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course *</label>
                            <select name="course" id="edit_course" class="form-select" required>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['code']) ?>">
                                    <?= htmlspecialchars($course['code'] . ' - ' . $course['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year Level *</label>
                            <select name="year_level" id="edit_year_level" class="form-select" required>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" id="edit_section" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete student <strong id="delete_student_id"></strong>?</p>
                <p class="text-danger mb-0"><small>This action cannot be undone. All related records (enrollments, grades, attendance) will also be deleted.</small></p>
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
function editStudent(student) {
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_student_id').value = student.student_id;
    document.getElementById('edit_email').value = student.email;
    document.getElementById('edit_first_name').value = student.first_name;
    document.getElementById('edit_middle_name').value = student.middle_name || '';
    document.getElementById('edit_last_name').value = student.last_name;
    document.getElementById('edit_course').value = student.course;
    document.getElementById('edit_year_level').value = student.year_level;
    document.getElementById('edit_section').value = student.section || '';
    document.getElementById('edit_status').value = student.status;
    
    new bootstrap.Modal(document.getElementById('editStudentModal')).show();
}

function deleteStudent(id, studentId) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_student_id').textContent = studentId;
    
    new bootstrap.Modal(document.getElementById('deleteStudentModal')).show();
}
</script>
