<?php
/**
 * WMSU Grading System API
 * Main entry point - Routes all requests
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers - Allow requests from Expo app
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/classes/' . $class . '.php',
        __DIR__ . '/controllers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Auth.php';

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove base path and query string
$basePath = '/backend'; // Adjust if needed
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace($basePath, '', $uri);
$uri = trim($uri, '/');

// Split URI into segments
$segments = explode('/', $uri);
$resource = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? null;

// Route the request
try {
    switch ($resource) {
        case 'auth':
            $controller = new AuthController();
            break;
            
        case 'students':
            $controller = new StudentController();
            break;
            
        case 'grades':
            $controller = new GradeController();
            break;
            
        case 'subjects':
            $controller = new SubjectController();
            break;
            
        case 'attendance':
            $controller = new AttendanceController();
            break;
            
        case 'notifications':
            $controller = new NotificationController();
            break;
            
        case 'semesters':
            $controller = new SemesterController();
            break;
            
        case 'courses':
            $controller = new CourseController();
            break;
            
        case 'instructors':
            $controller = new InstructorController();
            break;
            
        case 'schedules':
            $controller = new ScheduleController();
            break;
            
        case 'enrollments':
            $controller = new EnrollmentController();
            break;
            
        case '':
            // API root - return status
            Response::success([
                'name' => 'WMSU Grading System API',
                'version' => '1.0.0',
                'status' => 'running',
                'timestamp' => date('Y-m-d H:i:s'),
                'endpoints' => [
                    'auth' => '/backend/auth',
                    'students' => '/backend/students',
                    'grades' => '/backend/grades',
                    'subjects' => '/backend/subjects',
                    'attendance' => '/backend/attendance',
                    'notifications' => '/backend/notifications',
                    'semesters' => '/backend/semesters',
                    'courses' => '/backend/courses',
                    'instructors' => '/backend/instructors',
                    'schedules' => '/backend/schedules',
                    'enrollments' => '/backend/enrollments',
                ],
            ], 'API is running');
            exit();
            
        default:
            Response::error('Endpoint not found', 404);
            exit();
    }
    
    // Handle the request
    $controller->handleRequest($requestMethod, $action, $id);
    
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
