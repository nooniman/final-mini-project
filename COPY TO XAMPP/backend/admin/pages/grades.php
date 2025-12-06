<?php
/**
 * Grades Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                try {
                    $finalGrade = $_POST['final_grade'] !== '' ? $_POST['final_grade'] : null;
                    
                    $stmt = $db->prepare("
                        UPDATE grades SET 
                            prelim_grade = :prelim_grade,
                            midterm_grade = :midterm_grade,
                            prefinal_grade = :prefinal_grade,
                            final_grade = :final_grade,
                            remarks = :remarks
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $_POST['id'],
                        'prelim_grade' => $_POST['prelim_grade'] !== '' ? $_POST['prelim_grade'] : null,
                        'midterm_grade' => $_POST['midterm_grade'] !== '' ? $_POST['midterm_grade'] : null,
                        'prefinal_grade' => $_POST['prefinal_grade'] !== '' ? $_POST['prefinal_grade'] : null,
                        'final_grade' => $finalGrade,
                        'remarks' => $finalGrade ? ($_POST['remarks'] ?: ($finalGrade <= 3.0 ? 'Passed' : 'Failed')) : 'In Progress',
                    ]);
                    $message = 'Grade updated successfully!';
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

// Get all semesters for filter
$semesters = $db->query("SELECT * FROM semesters ORDER BY academic_year DESC, semester_number DESC")->fetchAll();

// Build query
$sql = "
    SELECT g.*, 
           st.student_id as student_number, st.first_name, st.last_name,
           sub.code as subject_code, sub.name as subject_name, sub.units,
           sem.name as semester_name, sem.academic_year
    FROM grades g
    JOIN students st ON g.student_id = st.id
    JOIN subjects sub ON g.subject_id = sub.id
    JOIN semesters sem ON g.semester_id = sem.id
";

if ($selectedSemester) {
    $sql .= " WHERE g.semester_id = :semester_id";
}

$sql .= " ORDER BY sem.academic_year DESC, st.student_id ASC, sub.code ASC";

$stmt = $db->prepare($sql);
if ($selectedSemester) {
    $stmt->execute(['semester_id' => $selectedSemester]);
} else {
    $stmt->execute();
}
$grades = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Grades Management</h2>
    <div class="d-flex gap-2">
        <select class="form-select" style="width: 300px;" onchange="window.location.href='?page=grades&semester=' + this.value">
            <option value="">All Semesters</option>
            <?php foreach ($semesters as $semester): ?>
            <option value="<?= $semester['id'] ?>" <?= $selectedSemester == $semester['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($semester['name'] . ' ' . $semester['academic_year']) ?>
                <?= $semester['is_current'] ? ' (Current)' : '' ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
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
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Prefinal</th>
                        <th>Final</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($grade['student_number']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($grade['subject_code']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($grade['subject_name']) ?> (<?= $grade['units'] ?> units)</small>
                        </td>
                        <td>
                            <?= htmlspecialchars($grade['semester_name']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($grade['academic_year']) ?></small>
                        </td>
                        <td class="text-center"><?= $grade['prelim_grade'] ?? '-' ?></td>
                        <td class="text-center"><?= $grade['midterm_grade'] ?? '-' ?></td>
                        <td class="text-center"><?= $grade['prefinal_grade'] ?? '-' ?></td>
                        <td class="text-center">
                            <?php if ($grade['final_grade']): ?>
                            <strong class="<?= $grade['final_grade'] <= 3.0 ? 'text-success' : 'text-danger' ?>">
                                <?= $grade['final_grade'] ?>
                            </strong>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= 
                                $grade['remarks'] === 'Passed' ? 'bg-success' : 
                                ($grade['remarks'] === 'Failed' ? 'bg-danger' : 
                                ($grade['remarks'] === 'INC' ? 'bg-warning' : 'bg-secondary')) 
                            ?>">
                                <?= htmlspecialchars($grade['remarks'] ?? 'In Progress') ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editGrade(<?= json_encode($grade) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($grades)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No grades found. Enroll students first to add grades.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Grade Scale Reference -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">Grade Scale Reference</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr><td><strong>1.00</strong></td><td>Excellent (97-100%)</td></tr>
                    <tr><td><strong>1.25</strong></td><td>Very Good (94-96%)</td></tr>
                    <tr><td><strong>1.50</strong></td><td>Very Good (91-93%)</td></tr>
                    <tr><td><strong>1.75</strong></td><td>Good (88-90%)</td></tr>
                    <tr><td><strong>2.00</strong></td><td>Good (85-87%)</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr><td><strong>2.25</strong></td><td>Satisfactory (82-84%)</td></tr>
                    <tr><td><strong>2.50</strong></td><td>Satisfactory (79-81%)</td></tr>
                    <tr><td><strong>2.75</strong></td><td>Fair (76-78%)</td></tr>
                    <tr><td><strong>3.00</strong></td><td>Passing (75%)</td></tr>
                    <tr><td><strong>5.00</strong></td><td>Failed (Below 75%)</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Grade Modal -->
<div class="modal fade" id="editGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_grade_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="edit_student_info"></span><br>
                        <strong>Subject:</strong> <span id="edit_subject_info"></span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prelim Grade</label>
                            <input type="number" name="prelim_grade" id="edit_prelim" class="form-control" 
                                   step="0.25" min="1.00" max="5.00" placeholder="e.g., 1.50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Midterm Grade</label>
                            <input type="number" name="midterm_grade" id="edit_midterm" class="form-control" 
                                   step="0.25" min="1.00" max="5.00" placeholder="e.g., 1.50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prefinal Grade</label>
                            <input type="number" name="prefinal_grade" id="edit_prefinal" class="form-control" 
                                   step="0.25" min="1.00" max="5.00" placeholder="e.g., 1.50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Final Grade</label>
                            <input type="number" name="final_grade" id="edit_final" class="form-control" 
                                   step="0.25" min="1.00" max="5.00" placeholder="e.g., 1.50">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <select name="remarks" id="edit_remarks" class="form-select">
                            <option value="">Auto-detect from Final Grade</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="INC">Incomplete (INC)</option>
                            <option value="DRP">Dropped (DRP)</option>
                            <option value="In Progress">In Progress</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGrade(grade) {
    document.getElementById('edit_grade_id').value = grade.id;
    document.getElementById('edit_student_info').textContent = grade.student_number + ' - ' + grade.first_name + ' ' + grade.last_name;
    document.getElementById('edit_subject_info').textContent = grade.subject_code + ' - ' + grade.subject_name;
    document.getElementById('edit_prelim').value = grade.prelim_grade || '';
    document.getElementById('edit_midterm').value = grade.midterm_grade || '';
    document.getElementById('edit_prefinal').value = grade.prefinal_grade || '';
    document.getElementById('edit_final').value = grade.final_grade || '';
    document.getElementById('edit_remarks').value = grade.remarks || '';
    
    new bootstrap.Modal(document.getElementById('editGradeModal')).show();
}
</script>
