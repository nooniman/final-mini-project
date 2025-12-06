<?php
/**
 * Subjects Management Page
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
                        INSERT INTO subjects (code, name, description, units, lecture_hours, lab_hours)
                        VALUES (:code, :name, :description, :units, :lecture_hours, :lab_hours)
                    ");
                    $stmt->execute([
                        'code' => $_POST['code'],
                        'name' => $_POST['name'],
                        'description' => $_POST['description'] ?? '',
                        'units' => $_POST['units'],
                        'lecture_hours' => $_POST['lecture_hours'] ?? 0,
                        'lab_hours' => $_POST['lab_hours'] ?? 0,
                    ]);
                    $message = 'Subject added successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                try {
                    $stmt = $db->prepare("
                        UPDATE subjects SET 
                            code = :code,
                            name = :name,
                            description = :description,
                            units = :units,
                            lecture_hours = :lecture_hours,
                            lab_hours = :lab_hours
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'code' => $_POST['code'],
                        'name' => $_POST['name'],
                        'description' => $_POST['description'] ?? '',
                        'units' => $_POST['units'],
                        'lecture_hours' => $_POST['lecture_hours'] ?? 0,
                        'lab_hours' => $_POST['lab_hours'] ?? 0,
                    ]);
                    $message = 'Subject updated successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM subjects WHERE id = :id");
                    $stmt->execute(['id' => $_POST['id']]);
                    $message = 'Subject deleted successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get all subjects
$subjects = $db->query("SELECT * FROM subjects ORDER BY code ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Subjects Management</h2>
    <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="bi bi-plus-lg me-2"></i>Add Subject
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
                        <th>Description</th>
                        <th>Units</th>
                        <th>Lec Hours</th>
                        <th>Lab Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($subject['code']) ?></strong></td>
                        <td><?= htmlspecialchars($subject['name']) ?></td>
                        <td><?= htmlspecialchars($subject['description'] ?? '-') ?></td>
                        <td><span class="badge bg-primary"><?= $subject['units'] ?></span></td>
                        <td><?= $subject['lecture_hours'] ?? 0 ?></td>
                        <td><?= $subject['lab_hours'] ?? 0 ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editSubject(<?= json_encode($subject) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSubject(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['code']) ?>')">
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

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Subject Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g., CS 201" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Data Structures" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Units *</label>
                            <input type="number" name="units" class="form-control" min="1" max="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lecture Hours</label>
                            <input type="number" name="lecture_hours" class="form-control" min="0" max="6" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lab Hours</label>
                            <input type="number" name="lab_hours" class="form-control" min="0" max="6" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_subject_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Subject Code *</label>
                        <input type="text" name="code" id="edit_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Units *</label>
                            <input type="number" name="units" id="edit_units" class="form-control" min="1" max="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lecture Hours</label>
                            <input type="number" name="lecture_hours" id="edit_lecture_hours" class="form-control" min="0" max="6">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lab Hours</label>
                            <input type="number" name="lab_hours" id="edit_lab_hours" class="form-control" min="0" max="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Update Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete subject <strong id="delete_subject_code"></strong>?</p>
                <p class="text-danger mb-0"><small>This will also delete all related enrollments, grades, and schedules.</small></p>
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
function editSubject(subject) {
    document.getElementById('edit_subject_id').value = subject.id;
    document.getElementById('edit_code').value = subject.code;
    document.getElementById('edit_name').value = subject.name;
    document.getElementById('edit_description').value = subject.description || '';
    document.getElementById('edit_units').value = subject.units;
    document.getElementById('edit_lecture_hours').value = subject.lecture_hours || 0;
    document.getElementById('edit_lab_hours').value = subject.lab_hours || 0;
    
    new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
}

function deleteSubject(id, code) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_subject_code').textContent = code;
    
    new bootstrap.Modal(document.getElementById('deleteSubjectModal')).show();
}
</script>
