<?php
/**
 * Semesters Management Page
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
                        INSERT INTO semesters (name, semester_number, academic_year, start_date, end_date, is_current)
                        VALUES (:name, :semester_number, :academic_year, :start_date, :end_date, :is_current)
                    ");
                    $stmt->execute([
                        'name' => $_POST['name'],
                        'semester_number' => $_POST['semester_number'],
                        'academic_year' => $_POST['academic_year'],
                        'start_date' => $_POST['start_date'] ?: null,
                        'end_date' => $_POST['end_date'] ?: null,
                        'is_current' => isset($_POST['is_current']) ? 1 : 0,
                    ]);
                    
                    // If this is set as current, unset others
                    if (isset($_POST['is_current'])) {
                        $newId = $db->lastInsertId();
                        $db->prepare("UPDATE semesters SET is_current = 0 WHERE id != :id")->execute(['id' => $newId]);
                    }
                    
                    $message = 'Semester added successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $stmt = $db->prepare("
                        UPDATE semesters SET 
                            name = :name,
                            semester_number = :semester_number,
                            academic_year = :academic_year,
                            start_date = :start_date,
                            end_date = :end_date,
                            is_current = :is_current
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'name' => $_POST['name'],
                        'semester_number' => $_POST['semester_number'],
                        'academic_year' => $_POST['academic_year'],
                        'start_date' => $_POST['start_date'] ?: null,
                        'end_date' => $_POST['end_date'] ?: null,
                        'is_current' => isset($_POST['is_current']) ? 1 : 0,
                    ]);
                    
                    // If this is set as current, unset others
                    if (isset($_POST['is_current'])) {
                        $db->prepare("UPDATE semesters SET is_current = 0 WHERE id != :id")->execute(['id' => $_POST['id']]);
                    }
                    
                    $message = 'Semester updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    // Check if semester has enrollments
                    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM enrollments WHERE semester_id = :id");
                    $checkStmt->execute(['id' => $_POST['id']]);
                    $count = $checkStmt->fetch()['count'];
                    
                    if ($count > 0) {
                        $message = 'Cannot delete semester with existing enrollments. Delete enrollments first.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("DELETE FROM semesters WHERE id = :id");
                        $stmt->execute(['id' => $_POST['id']]);
                        $message = 'Semester deleted successfully!';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'set_current':
                try {
                    $db->prepare("UPDATE semesters SET is_current = 0")->execute();
                    $db->prepare("UPDATE semesters SET is_current = 1 WHERE id = :id")->execute(['id' => $_POST['id']]);
                    $message = 'Current semester updated!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get all semesters
$semesters = $db->query("SELECT * FROM semesters ORDER BY academic_year DESC, semester_number DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Semesters Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addSemesterModal">
        <i class="bi bi-plus-lg me-2"></i>Add Semester
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
                        <th>Name</th>
                        <th>Academic Year</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($semesters as $semester): ?>
                    <tr class="<?= $semester['is_current'] ? 'table-success' : '' ?>">
                        <td>
                            <strong><?= htmlspecialchars($semester['name']) ?></strong>
                            <?php if ($semester['is_current']): ?>
                            <span class="badge bg-primary ms-2">Current</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($semester['academic_year']) ?></td>
                        <td><?= $semester['start_date'] ? date('M d, Y', strtotime($semester['start_date'])) : '-' ?></td>
                        <td><?= $semester['end_date'] ? date('M d, Y', strtotime($semester['end_date'])) : '-' ?></td>
                        <td>
                            <?php if (!$semester['is_current']): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="set_current">
                                <input type="hidden" name="id" value="<?= $semester['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-success">Set as Current</button>
                            </form>
                            <?php else: ?>
                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editSemester(<?= json_encode($semester) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSemester(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['name']) ?>')">
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

<!-- Add Semester Modal -->
<div class="modal fade" id="addSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester Name *</label>
                        <select name="name" class="form-select" required>
                            <option value="First Semester">First Semester</option>
                            <option value="Second Semester">Second Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Semester Number *</label>
                            <select name="semester_number" class="form-select" required>
                                <option value="1">1 (First)</option>
                                <option value="2">2 (Second)</option>
                                <option value="3">3 (Summer)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Academic Year *</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g., 2024-2025" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_current" class="form-check-input" id="add_is_current">
                        <label class="form-check-label" for="add_is_current">Set as current semester</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Semester Modal -->
<div class="modal fade" id="editSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_semester_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester Name *</label>
                        <select name="name" id="edit_name" class="form-select" required>
                            <option value="First Semester">First Semester</option>
                            <option value="Second Semester">Second Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Semester Number *</label>
                            <select name="semester_number" id="edit_semester_number" class="form-select" required>
                                <option value="1">1 (First)</option>
                                <option value="2">2 (Second)</option>
                                <option value="3">3 (Summer)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Academic Year *</label>
                            <input type="text" name="academic_year" id="edit_academic_year" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_current" class="form-check-input" id="edit_is_current">
                        <label class="form-check-label" for="edit_is_current">Set as current semester</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete semester <strong id="delete_semester_name"></strong>?</p>
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
function editSemester(semester) {
    document.getElementById('edit_semester_id').value = semester.id;
    document.getElementById('edit_name').value = semester.name;
    document.getElementById('edit_semester_number').value = semester.semester_number;
    document.getElementById('edit_academic_year').value = semester.academic_year;
    document.getElementById('edit_start_date').value = semester.start_date || '';
    document.getElementById('edit_end_date').value = semester.end_date || '';
    document.getElementById('edit_is_current').checked = semester.is_current == 1;
    
    new bootstrap.Modal(document.getElementById('editSemesterModal')).show();
}

function deleteSemester(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_semester_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteSemesterModal')).show();
}
</script>
