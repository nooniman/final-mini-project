<?php
/**
 * Instructors Management Page
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
                        INSERT INTO instructors (employee_id, email, first_name, last_name, department, office, consultation_hours)
                        VALUES (:employee_id, :email, :first_name, :last_name, :department, :office, :consultation_hours)
                    ");
                    $stmt->execute([
                        'employee_id' => $_POST['employee_id'],
                        'email' => $_POST['email'],
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'department' => $_POST['department'] ?? '',
                        'office' => $_POST['office'] ?? '',
                        'consultation_hours' => $_POST['consultation_hours'] ?? '',
                    ]);
                    $message = 'Instructor added successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $stmt = $db->prepare("
                        UPDATE instructors SET 
                            employee_id = :employee_id,
                            email = :email,
                            first_name = :first_name,
                            last_name = :last_name,
                            department = :department,
                            office = :office,
                            consultation_hours = :consultation_hours
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'employee_id' => $_POST['employee_id'],
                        'email' => $_POST['email'],
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'department' => $_POST['department'] ?? '',
                        'office' => $_POST['office'] ?? '',
                        'consultation_hours' => $_POST['consultation_hours'] ?? '',
                    ]);
                    $message = 'Instructor updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    // Check if instructor has assigned subjects
                    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM subjects WHERE instructor_id = :id");
                    $checkStmt->execute(['id' => $_POST['id']]);
                    $count = $checkStmt->fetch()['count'];
                    
                    if ($count > 0) {
                        $message = 'Cannot delete instructor with assigned subjects. Reassign subjects first.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("DELETE FROM instructors WHERE id = :id");
                        $stmt->execute(['id' => $_POST['id']]);
                        $message = 'Instructor deleted successfully!';
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

// Get all instructors with subject count
$instructors = $db->query("
    SELECT i.*, COUNT(s.id) as subject_count
    FROM instructors i
    LEFT JOIN subjects s ON i.id = s.instructor_id
    GROUP BY i.id
    ORDER BY i.employee_id ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-badge me-2"></i>Instructors Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addInstructorModal">
        <i class="bi bi-plus-lg me-2"></i>Add Instructor
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
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Office</th>
                        <th>Subjects</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($instructors as $instructor): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($instructor['employee_id']) ?></strong></td>
                        <td><?= htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']) ?></td>
                        <td><?= htmlspecialchars($instructor['email']) ?></td>
                        <td><?= htmlspecialchars($instructor['department'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($instructor['office'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $instructor['subject_count'] ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editInstructor(<?= json_encode($instructor) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInstructor(<?= $instructor['id'] ?>, '<?= htmlspecialchars($instructor['employee_id']) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($instructors)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No instructors found. Add your first instructor!
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Instructor Modal -->
<div class="modal fade" id="addInstructorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Instructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee ID *</label>
                            <input type="text" name="employee_id" class="form-control" placeholder="e.g., EMP001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" placeholder="instructor@wmsu.edu.ph" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" placeholder="e.g., Computer Science Department">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Office</label>
                            <input type="text" name="office" class="form-control" placeholder="e.g., CCS Building, Room 301">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Consultation Hours</label>
                            <input type="text" name="consultation_hours" class="form-control" placeholder="e.g., MWF 2:00 PM - 4:00 PM">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Instructor Modal -->
<div class="modal fade" id="editInstructorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Instructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_instructor_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee ID *</label>
                            <input type="text" name="employee_id" id="edit_employee_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" id="edit_department" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Office</label>
                            <input type="text" name="office" id="edit_office" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Consultation Hours</label>
                            <input type="text" name="consultation_hours" id="edit_consultation_hours" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteInstructorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete instructor <strong id="delete_instructor_id"></strong>?</p>
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
function editInstructor(instructor) {
    document.getElementById('edit_instructor_id').value = instructor.id;
    document.getElementById('edit_employee_id').value = instructor.employee_id;
    document.getElementById('edit_email').value = instructor.email;
    document.getElementById('edit_first_name').value = instructor.first_name;
    document.getElementById('edit_last_name').value = instructor.last_name;
    document.getElementById('edit_department').value = instructor.department || '';
    document.getElementById('edit_office').value = instructor.office || '';
    document.getElementById('edit_consultation_hours').value = instructor.consultation_hours || '';
    
    new bootstrap.Modal(document.getElementById('editInstructorModal')).show();
}

function deleteInstructor(id, employeeId) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_instructor_id').textContent = employeeId;
    
    new bootstrap.Modal(document.getElementById('deleteInstructorModal')).show();
}
</script>
