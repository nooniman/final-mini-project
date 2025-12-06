<?php
/**
 * WMSU Grading System - Admin Dashboard
 * Main admin panel for managing the grading system
 * 
 * Access via: http://localhost/backend/admin/
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include database config
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/Auth.php';

// Simple admin authentication (you can enhance this later)
$adminPassword = 'admin123'; // Change this in production!

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $loginError = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Check if logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Database connection
$db = null;
$stats = [];

if ($isLoggedIn) {
    try {
        $db = getConnection();
        
        // Get statistics
        $stats['students'] = $db->query("SELECT COUNT(*) as count FROM students")->fetch()['count'];
        $stats['active_students'] = $db->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch()['count'];
        $stats['subjects'] = $db->query("SELECT COUNT(*) as count FROM subjects")->fetch()['count'];
        $stats['instructors'] = $db->query("SELECT COUNT(*) as count FROM instructors")->fetch()['count'];
        $stats['enrollments'] = $db->query("SELECT COUNT(*) as count FROM enrollments WHERE status = 'enrolled'")->fetch()['count'];
        $stats['grades'] = $db->query("SELECT COUNT(*) as count FROM grades WHERE final_grade IS NOT NULL")->fetch()['count'];
        $stats['semesters'] = $db->query("SELECT COUNT(*) as count FROM semesters")->fetch()['count'];
        $stats['courses'] = $db->query("SELECT COUNT(*) as count FROM courses")->fetch()['count'];
        $stats['unread_notifications'] = $db->query("SELECT COUNT(*) as count FROM notifications WHERE read_at IS NULL")->fetch()['count'];
        
        // Get current semester
        $currentSemester = $db->query("SELECT * FROM semesters WHERE is_current = 1 LIMIT 1")->fetch();
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WMSU Grading System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --wmsu-crimson: #8B0000;
            --wmsu-crimson-dark: #6B0000;
            --wmsu-gold: #FFD700;
            --sidebar-width: 260px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #1d1d1f;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--wmsu-crimson) 0%, var(--wmsu-crimson-dark) 100%);
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-brand-icon {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .sidebar-brand h4 { margin: 0; font-weight: 700; font-size: 1.1rem; }
        .sidebar-brand small { opacity: 0.8; font-size: 0.75rem; }
        
        .sidebar-nav { padding: 1rem 0; }
        
        .nav-section {
            padding: 0.5rem 1.5rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.85) !important;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--wmsu-gold);
        }
        
        .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.15);
            border-left-color: var(--wmsu-gold);
            font-weight: 500;
        }
        
        .nav-link i { width: 22px; font-size: 1.1rem; opacity: 0.9; }
        .nav-link .badge { margin-left: auto; background: var(--wmsu-gold); color: #333; font-size: 0.7rem; }
        
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; background: #f8f9fa; }
        
        .content-header {
            background: white;
            padding: 1.25rem 2rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .content-header h4 { margin: 0; font-weight: 600; }
        .content-body { padding: 1.5rem 2rem; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        
        .stat-value { font-size: 2rem; font-weight: 700; color: #1d1d1f; line-height: 1; }
        .stat-label { color: #6c757d; font-size: 0.85rem; margin-top: 0.25rem; }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .card-body { padding: 1.5rem; }
        .table { margin-bottom: 0; }
        
        .table th {
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td { vertical-align: middle; padding: 1rem; border-color: #f1f3f4; }
        
        .btn-wmsu {
            background: var(--wmsu-crimson);
            color: white;
            border: none;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .btn-wmsu:hover { background: var(--wmsu-crimson-dark); color: white; transform: translateY(-1px); }
        
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--wmsu-crimson) 0%, var(--wmsu-crimson-dark) 100%);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
        }
        
        .login-card .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--wmsu-crimson), var(--wmsu-crimson-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: white;
        }
        
        .login-card h3 { font-weight: 700; margin-bottom: 0.5rem; }
        .form-control:focus { border-color: var(--wmsu-crimson); box-shadow: 0 0 0 0.2rem rgba(139, 0, 0, 0.15); }
        
        .modal-header { background: var(--wmsu-crimson); color: white; border-radius: 0; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .modal-content { border-radius: 16px; overflow: hidden; }
        
        .quick-action-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.2s;
            border: 2px solid transparent;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .quick-action-card:hover {
            border-color: var(--wmsu-crimson);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 0, 0, 0.1);
            color: inherit;
        }
        
        .quick-action-card i { font-size: 2rem; margin-bottom: 0.75rem; display: block; }
        .quick-action-card span { font-weight: 500; font-size: 0.9rem; }
        
        .semester-badge {
            background: linear-gradient(135deg, var(--wmsu-gold), #FFA500);
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <div class="login-container">
        <div class="login-card">
            <div class="text-center">
                <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <h3>WMSU Grading System</h3>
                <p class="text-muted mb-4">Admin Dashboard</p>
            </div>
            
            <?php if (isset($loginError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Admin Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter password" required autofocus>
                </div>
                <button type="submit" name="admin_login" class="btn btn-wmsu btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
            
            <p class="text-muted text-center mt-4 mb-0">
                <small><i class="bi bi-info-circle me-1"></i>Default password: admin123</small>
            </p>
        </div>
    </div>
    
    <?php else: ?>
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <h4>WMSU Admin</h4>
                <small>Grading System</small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">Main</div>
            <a class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="?page=dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <div class="nav-section">Academic</div>
            <a class="nav-link <?= $page === 'students' ? 'active' : '' ?>" href="?page=students">
                <i class="bi bi-people"></i> Students
                <span class="badge"><?= $stats['active_students'] ?? 0 ?></span>
            </a>
            <a class="nav-link <?= $page === 'instructors' ? 'active' : '' ?>" href="?page=instructors">
                <i class="bi bi-person-badge"></i> Instructors
            </a>
            <a class="nav-link <?= $page === 'courses' ? 'active' : '' ?>" href="?page=courses">
                <i class="bi bi-diagram-3"></i> Courses
            </a>
            <a class="nav-link <?= $page === 'subjects' ? 'active' : '' ?>" href="?page=subjects">
                <i class="bi bi-book"></i> Subjects
            </a>
            
            <div class="nav-section">Enrollment</div>
            <a class="nav-link <?= $page === 'semesters' ? 'active' : '' ?>" href="?page=semesters">
                <i class="bi bi-calendar3"></i> Semesters
            </a>
            <a class="nav-link <?= $page === 'enrollments' ? 'active' : '' ?>" href="?page=enrollments">
                <i class="bi bi-clipboard-check"></i> Enrollments
            </a>
            <a class="nav-link <?= $page === 'schedules' ? 'active' : '' ?>" href="?page=schedules">
                <i class="bi bi-clock"></i> Schedules
            </a>
            
            <div class="nav-section">Records</div>
            <a class="nav-link <?= $page === 'grades' ? 'active' : '' ?>" href="?page=grades">
                <i class="bi bi-trophy"></i> Grades
            </a>
            <a class="nav-link <?= $page === 'attendance' ? 'active' : '' ?>" href="?page=attendance">
                <i class="bi bi-calendar-check"></i> Attendance
            </a>
            <a class="nav-link <?= $page === 'notifications' ? 'active' : '' ?>" href="?page=notifications">
                <i class="bi bi-bell"></i> Notifications
                <?php if (($stats['unread_notifications'] ?? 0) > 0): ?>
                <span class="badge bg-danger"><?= $stats['unread_notifications'] ?></span>
                <?php endif; ?>
            </a>
            
            <div class="nav-section">Settings</div>
            <a class="nav-link" href="change_password.php">
                <i class="bi bi-key"></i> Reset Password
            </a>
            <a class="nav-link" href="?logout=1">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <h4>
                <?php
                $pageTitles = [
                    'dashboard' => 'Dashboard', 'students' => 'Students', 'instructors' => 'Instructors',
                    'courses' => 'Courses', 'subjects' => 'Subjects', 'semesters' => 'Semesters',
                    'enrollments' => 'Enrollments', 'schedules' => 'Schedules', 'grades' => 'Grades',
                    'attendance' => 'Attendance', 'notifications' => 'Notifications',
                ];
                echo $pageTitles[$page] ?? 'Dashboard';
                ?>
            </h4>
            <div class="d-flex align-items-center gap-3">
                <?php if ($currentSemester): ?>
                <span class="semester-badge">
                    <i class="bi bi-calendar-event me-1"></i>
                    <?= htmlspecialchars($currentSemester['name'] . ' ' . $currentSemester['academic_year']) ?>
                </span>
                <?php endif; ?>
                <span class="text-muted"><i class="bi bi-clock me-1"></i><?= date('M d, Y') ?></span>
            </div>
        </div>
        
        <div class="content-body">
            <?php if (isset($dbError)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Database Error: <?= htmlspecialchars($dbError) ?></div>
            <?php endif; ?>
            
            <?php
            switch ($page) {
                case 'students': include 'pages/students.php'; break;
                case 'instructors': include 'pages/instructors.php'; break;
                case 'subjects': include 'pages/subjects.php'; break;
                case 'enrollments': include 'pages/enrollments.php'; break;
                case 'grades': include 'pages/grades.php'; break;
                case 'semesters': include 'pages/semesters.php'; break;
                case 'courses': include 'pages/courses.php'; break;
                case 'schedules': include 'pages/schedules.php'; break;
                case 'attendance': include 'pages/attendance.php'; break;
                case 'notifications': include 'pages/notifications.php'; break;
                default:
                    ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #E3F2FD; color: #1976D2;"><i class="bi bi-people"></i></div>
                            <div class="stat-value"><?= $stats['students'] ?? 0 ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #E8F5E9; color: #388E3C;"><i class="bi bi-person-badge"></i></div>
                            <div class="stat-value"><?= $stats['instructors'] ?? 0 ?></div>
                            <div class="stat-label">Instructors</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #FFF3E0; color: #F57C00;"><i class="bi bi-book"></i></div>
                            <div class="stat-value"><?= $stats['subjects'] ?? 0 ?></div>
                            <div class="stat-label">Subjects</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #FCE4EC; color: #C2185B;"><i class="bi bi-clipboard-check"></i></div>
                            <div class="stat-value"><?= $stats['enrollments'] ?? 0 ?></div>
                            <div class="stat-label">Active Enrollments</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #E8EAF6; color: #3F51B5;"><i class="bi bi-trophy"></i></div>
                            <div class="stat-value"><?= $stats['grades'] ?? 0 ?></div>
                            <div class="stat-label">Grades Posted</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #E0F7FA; color: #00ACC1;"><i class="bi bi-diagram-3"></i></div>
                            <div class="stat-value"><?= $stats['courses'] ?? 0 ?></div>
                            <div class="stat-label">Courses</div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="?page=students" class="quick-action-card"><i class="bi bi-person-plus text-primary"></i><span>Add Student</span></a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="?page=instructors" class="quick-action-card"><i class="bi bi-person-badge text-success"></i><span>Add Instructor</span></a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="?page=subjects" class="quick-action-card"><i class="bi bi-journal-plus text-info"></i><span>Add Subject</span></a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="?page=enrollments" class="quick-action-card"><i class="bi bi-clipboard-plus text-warning"></i><span>Enroll Student</span></a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="?page=grades" class="quick-action-card"><i class="bi bi-pencil-square text-danger"></i><span>Enter Grades</span></a>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6">
                                    <a href="?page=notifications" class="quick-action-card"><i class="bi bi-megaphone text-secondary"></i><span>Broadcast</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-people me-2"></i>Recent Students</span>
                                    <a href="?page=students" class="btn btn-sm btn-wmsu">View All</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead><tr><th>Student ID</th><th>Name</th><th>Course</th><th>Status</th></tr></thead>
                                            <tbody>
                                                <?php if ($db) { $stmt = $db->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5"); foreach ($stmt->fetchAll() as $student): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($student['student_id']) ?></strong></td>
                                                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($student['course']) ?></span></td>
                                                    <td><span class="badge <?= $student['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>"><?= ucfirst($student['status']) ?></span></td>
                                                </tr>
                                                <?php endforeach; } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-trophy me-2"></i>Recent Grades</span>
                                    <a href="?page=grades" class="btn btn-sm btn-wmsu">View All</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead><tr><th>Student</th><th>Subject</th><th>Final</th><th>Status</th></tr></thead>
                                            <tbody>
                                                <?php if ($db) { $stmt = $db->query("SELECT g.*, s.student_id as sid, s.first_name, s.last_name, sub.code FROM grades g JOIN students s ON g.student_id = s.id JOIN subjects sub ON g.subject_id = sub.id WHERE g.final_grade IS NOT NULL ORDER BY g.updated_at DESC LIMIT 5"); foreach ($stmt->fetchAll() as $grade): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($grade['sid']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?></small></td>
                                                    <td><span class="badge bg-info"><?= htmlspecialchars($grade['code']) ?></span></td>
                                                    <td><strong class="<?= $grade['final_grade'] <= 3.0 ? 'text-success' : 'text-danger' ?>"><?= $grade['final_grade'] ?></strong></td>
                                                    <td><span class="badge <?= $grade['remarks'] === 'Passed' ? 'bg-success' : 'bg-danger' ?>"><?= $grade['remarks'] ?></span></td>
                                                </tr>
                                                <?php endforeach; } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bell me-2"></i>Recent Notifications</span>
                            <a href="?page=notifications" class="btn btn-sm btn-wmsu">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead><tr><th>Title</th><th>Type</th><th>Recipient</th><th>Date</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php if ($db) { $stmt = $db->query("SELECT n.*, s.student_id, s.first_name, s.last_name FROM notifications n JOIN students s ON n.student_id = s.id ORDER BY n.created_at DESC LIMIT 5"); foreach ($stmt->fetchAll() as $notif): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($notif['title']) ?></strong></td>
                                            <td><span class="badge <?= $notif['type'] === 'grade' ? 'bg-success' : ($notif['type'] === 'announcement' ? 'bg-primary' : 'bg-secondary') ?>"><?= ucfirst($notif['type']) ?></span></td>
                                            <td><?= htmlspecialchars($notif['student_id'] . ' - ' . $notif['first_name']) ?></td>
                                            <td><small><?= date('M d, h:i A', strtotime($notif['created_at'])) ?></small></td>
                                            <td><?php if ($notif['read_at']): ?><span class="badge bg-light text-dark"><i class="bi bi-check2"></i> Read</span><?php else: ?><span class="badge bg-warning text-dark">Unread</span><?php endif; ?></td>
                                        </tr>
                                        <?php endforeach; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php break;
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
