<?php
/**
 * Admin Password Change Tool
 * Use this to reset any student's password
 * 
 * Access via: http://localhost/backend/admin/change_password.php
 */

// Include database config
require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';
$students = [];

// Get database connection
try {
    $db = getConnection();
    
    // Fetch all students for dropdown
    $stmt = $db->query("SELECT id, student_id, first_name, last_name, email FROM students ORDER BY student_id");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "Database error: " . $e->getMessage();
    $messageType = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($studentId)) {
        $message = "Please select a student.";
        $messageType = 'error';
    } elseif (empty($newPassword)) {
        $message = "Please enter a new password.";
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = 'error';
    } else {
        try {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update the password
            $stmt = $db->prepare("UPDATE students SET password = :password WHERE student_id = :student_id");
            $stmt->execute([
                'password' => $hashedPassword,
                'student_id' => $studentId,
            ]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Password updated successfully for student: $studentId";
                $messageType = 'success';
            } else {
                $message = "No student found with ID: $studentId";
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = "Error updating password: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Student Password - WMSU Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #8B0000 0%, #6B0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #8B0000;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        select, input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #8B0000;
        }
        
        select {
            cursor: pointer;
            background: white;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: #8B0000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: #6B0000;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .student-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 13px;
            color: #666;
            display: none;
        }
        
        .hint {
            font-size: 12px;
            color: #888;
            margin-top: 6px;
        }
        
        .divider {
            height: 1px;
            background: #e0e0e0;
            margin: 24px 0;
        }
        
        .quick-actions h3 {
            color: #333;
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .quick-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #f0f0f0;
            color: #333;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            border: none;
            margin-right: 8px;
            margin-bottom: 8px;
            transition: background 0.2s;
        }
        
        .quick-btn:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Change Student Password</h1>
            <p>WMSU Grading System - Admin Tool</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="student_id">Select Student</label>
                <select name="student_id" id="student_id" required onchange="showStudentInfo()">
                    <option value="">-- Choose a student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo htmlspecialchars($student['student_id']); ?>"
                                data-name="<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>"
                                data-email="<?php echo htmlspecialchars($student['email']); ?>">
                            <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="student-info" id="student-info"></div>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" 
                       placeholder="Enter new password" required minlength="6">
                <p class="hint">Minimum 6 characters</p>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       placeholder="Confirm new password" required>
            </div>
            
            <button type="submit" class="btn">Update Password</button>
        </form>
        
        <div class="divider"></div>
        
        <div class="quick-actions">
            <h3>Quick Set Password:</h3>
            <button type="button" class="quick-btn" onclick="setPassword('password123')">password123</button>
            <button type="button" class="quick-btn" onclick="setPassword('wmsu2024')">wmsu2024</button>
            <button type="button" class="quick-btn" onclick="setPassword('student123')">student123</button>
            <button type="button" class="quick-btn" onclick="generatePassword()">🎲 Random</button>
        </div>
    </div>
    
    <script>
        function showStudentInfo() {
            const select = document.getElementById('student_id');
            const info = document.getElementById('student-info');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                const name = option.getAttribute('data-name');
                const email = option.getAttribute('data-email');
                info.innerHTML = `<strong>${name}</strong><br>Email: ${email}`;
                info.style.display = 'block';
            } else {
                info.style.display = 'none';
            }
        }
        
        function setPassword(password) {
            document.getElementById('new_password').value = password;
            document.getElementById('confirm_password').value = password;
        }
        
        function generatePassword() {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let password = '';
            for (let i = 0; i < 10; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            setPassword(password);
            
            // Show the generated password briefly
            const pwField = document.getElementById('new_password');
            pwField.type = 'text';
            setTimeout(() => { pwField.type = 'password'; }, 3000);
        }
    </script>
</body>
</html>
