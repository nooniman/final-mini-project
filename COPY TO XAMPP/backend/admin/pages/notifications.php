<?php
/**
 * Notifications Management Page
 */

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $studentIds = $_POST['student_ids'] ?? [];
                    
                    if (empty($studentIds)) {
                        $message = 'Please select at least one student.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO notifications (student_id, title, message, type)
                            VALUES (:student_id, :title, :message, :type)
                        ");
                        
                        $count = 0;
                        foreach ($studentIds as $studentId) {
                            $stmt->execute([
                                'student_id' => $studentId,
                                'title' => $_POST['title'],
                                'message' => $_POST['message'],
                                'type' => $_POST['type'],
                            ]);
                            $count++;
                        }
                        
                        $message = "Notification sent to $count student(s)!";
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'broadcast':
                try {
                    // Get all active students
                    $studentsStmt = $db->query("SELECT id FROM students WHERE status = 'active'");
                    $allStudents = $studentsStmt->fetchAll();
                    
                    if (empty($allStudents)) {
                        $message = 'No active students found.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO notifications (student_id, title, message, type)
                            VALUES (:student_id, :title, :message, :type)
                        ");
                        
                        $count = 0;
                        foreach ($allStudents as $student) {
                            $stmt->execute([
                                'student_id' => $student['id'],
                                'title' => $_POST['title'],
                                'message' => $_POST['message'],
                                'type' => $_POST['type'],
                            ]);
                            $count++;
                        }
                        
                        $message = "Broadcast sent to $count student(s)!";
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM notifications WHERE id = :id");
                    $stmt->execute(['id' => $_POST['id']]);
                    $message = 'Notification deleted successfully!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                }
                break;
                
            case 'delete_all_read':
                try {
                    $stmt = $db->query("DELETE FROM notifications WHERE read_at IS NOT NULL");
                    $count = $stmt->rowCount();
                    $message = "Deleted $count read notification(s)!";
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
$selectedType = $_GET['type'] ?? '';
$selectedStatus = $_GET['status'] ?? '';

// Get all students for sending
$students = $db->query("SELECT id, student_id, first_name, last_name FROM students WHERE status = 'active' ORDER BY student_id")->fetchAll();

// Build query
$sql = "
    SELECT n.*, 
           st.student_id as student_number, st.first_name, st.last_name
    FROM notifications n
    JOIN students st ON n.student_id = st.id
    WHERE 1=1
";

$params = [];

if ($selectedType) {
    $sql .= " AND n.type = :type";
    $params['type'] = $selectedType;
}

if ($selectedStatus === 'read') {
    $sql .= " AND n.read_at IS NOT NULL";
} elseif ($selectedStatus === 'unread') {
    $sql .= " AND n.read_at IS NULL";
}

$sql .= " ORDER BY n.created_at DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

// Get notification stats
$stats = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as `read`,
        SUM(CASE WHEN type = 'grade' THEN 1 ELSE 0 END) as grade_count,
        SUM(CASE WHEN type = 'announcement' THEN 1 ELSE 0 END) as announcement_count,
        SUM(CASE WHEN type = 'reminder' THEN 1 ELSE 0 END) as reminder_count,
        SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) as system_count
    FROM notifications
")->fetch();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bell me-2"></i>Notifications Management</h2>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-wmsu" data-bs-toggle="modal" data-bs-target="#broadcastModal">
            <i class="bi bi-megaphone me-2"></i>Broadcast
        </button>
        <button class="btn btn-wmsu" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
            <i class="bi bi-plus-lg me-2"></i>Send Notification
        </button>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= $stats['total'] ?? 0 ?></h3>
                <small>Total Notifications</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3><?= $stats['unread'] ?? 0 ?></h3>
                <small>Unread</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?= $stats['read'] ?? 0 ?></h3>
                <small>Read</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border">
            <div class="card-body text-center">
                <form method="POST" onsubmit="return confirm('Delete all read notifications?')">
                    <input type="hidden" name="action" value="delete_all_read">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i>Clear Read
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="notifications">
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="grade" <?= $selectedType === 'grade' ? 'selected' : '' ?>>Grade (<?= $stats['grade_count'] ?? 0 ?>)</option>
                    <option value="announcement" <?= $selectedType === 'announcement' ? 'selected' : '' ?>>Announcement (<?= $stats['announcement_count'] ?? 0 ?>)</option>
                    <option value="reminder" <?= $selectedType === 'reminder' ? 'selected' : '' ?>>Reminder (<?= $stats['reminder_count'] ?? 0 ?>)</option>
                    <option value="system" <?= $selectedType === 'system' ? 'selected' : '' ?>>System (<?= $stats['system_count'] ?? 0 ?>)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="unread" <?= $selectedStatus === 'unread' ? 'selected' : '' ?>>Unread</option>
                    <option value="read" <?= $selectedStatus === 'read' ? 'selected' : '' ?>>Read</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="?page=notifications" class="btn btn-outline-secondary w-100">Clear Filters</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notif): ?>
                    <tr class="<?= $notif['read_at'] ? '' : 'table-warning' ?>">
                        <td>
                            <strong><?= htmlspecialchars($notif['student_number']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($notif['first_name'] . ' ' . $notif['last_name']) ?></small>
                        </td>
                        <td><strong><?= htmlspecialchars($notif['title']) ?></strong></td>
                        <td>
                            <small><?= htmlspecialchars(strlen($notif['message']) > 50 ? substr($notif['message'], 0, 50) . '...' : $notif['message']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= 
                                $notif['type'] === 'grade' ? 'bg-success' : 
                                ($notif['type'] === 'announcement' ? 'bg-primary' : 
                                ($notif['type'] === 'reminder' ? 'bg-warning text-dark' : 
                                ($notif['type'] === 'alert' ? 'bg-danger' : 'bg-secondary'))) 
                            ?>">
                                <?= ucfirst($notif['type']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($notif['read_at']): ?>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-check2"></i> Read
                            </span>
                            <br><small class="text-muted"><?= date('M d, h:i A', strtotime($notif['read_at'])) ?></small>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark">Unread</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($notif['created_at'])) ?><br>
                            <small class="text-muted"><?= date('h:i A', strtotime($notif['created_at'])) ?></small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick='viewNotification(<?= json_encode($notif) ?>)' title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(<?= $notif['id'] ?>)" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No notifications found.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Students *</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllStudents()">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllStudents()">Deselect All</button>
                        </div>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.5rem;">
                            <?php foreach ($students as $student): ?>
                            <div class="form-check">
                                <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" 
                                       value="<?= $student['id'] ?>" id="student_<?= $student['id'] ?>">
                                <label class="form-check-label" for="student_<?= $student['id'] ?>">
                                    <?= htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="Notification title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Notification message" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="announcement">Announcement</option>
                            <option value="grade">Grade Update</option>
                            <option value="reminder">Reminder</option>
                            <option value="alert">Alert</option>
                            <option value="system">System</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-wmsu">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-megaphone me-2"></i>Broadcast to All Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="broadcast">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will send a notification to <strong>all active students</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="Broadcast title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Broadcast message" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="announcement">Announcement</option>
                            <option value="reminder">Reminder</option>
                            <option value="alert">Alert</option>
                            <option value="system">System</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send me-2"></i>Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>To:</strong> <span id="view_student"></span></p>
                <p><strong>Title:</strong> <span id="view_title"></span></p>
                <p><strong>Type:</strong> <span id="view_type"></span></p>
                <p><strong>Sent:</strong> <span id="view_date"></span></p>
                <p><strong>Status:</strong> <span id="view_status"></span></p>
                <hr>
                <p><strong>Message:</strong></p>
                <p id="view_message" class="bg-light p-3 rounded"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this notification?</p>
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
function selectAllStudents() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllStudents() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
}

function viewNotification(notif) {
    document.getElementById('view_student').textContent = notif.student_number + ' - ' + notif.first_name + ' ' + notif.last_name;
    document.getElementById('view_title').textContent = notif.title;
    document.getElementById('view_type').innerHTML = '<span class="badge bg-primary">' + notif.type.charAt(0).toUpperCase() + notif.type.slice(1) + '</span>';
    document.getElementById('view_date').textContent = notif.created_at;
    document.getElementById('view_status').innerHTML = notif.read_at 
        ? '<span class="badge bg-success">Read on ' + notif.read_at + '</span>' 
        : '<span class="badge bg-warning text-dark">Unread</span>';
    document.getElementById('view_message').textContent = notif.message;
    
    new bootstrap.Modal(document.getElementById('viewNotificationModal')).show();
}

function deleteNotification(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteNotificationModal')).show();
}
</script>
