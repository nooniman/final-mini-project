<?php
/**
 * Auth Controller
 * Handles login, logout, and token refresh
 */

class AuthController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($action) {
            case 'login':
                if ($method === 'POST') {
                    $this->login();
                }
                break;
                
            case 'logout':
                if ($method === 'POST') {
                    $this->logout();
                }
                break;
                
            case 'refresh':
                if ($method === 'POST') {
                    $this->refresh();
                }
                break;
                
            case 'profile':
                if ($method === 'GET') {
                    $this->getProfile();
                }
                break;
                
            case 'change-password':
                if ($method === 'POST') {
                    $this->changePassword();
                }
                break;
                
            default:
                Response::error('Invalid auth action', 404);
        }
    }
    
    /**
     * Login user
     */
    private function login() {
        $input = $this->getInput();
        $this->validateRequired($input, ['student_id', 'password']);
        
        $studentId = $input['student_id'];
        $password = $input['password'];
        
        // Find user by student ID
        $stmt = $this->db->prepare("
            SELECT s.id, s.student_id, s.email, s.first_name, s.last_name, s.middle_name, s.password, s.status, s.year_level, s.section, s.course as program_code, c.name as program, c.department FROM students s LEFT JOIN courses c ON s.course = c.code WHERE s.student_id = :student_id
        ");
        $stmt->execute(['student_id' => $studentId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            Response::error('Invalid student ID or password', 401);
        }
        
        // Verify password
        if (!Auth::verifyPassword($password, $user['password'])) {
            Response::error('Invalid student ID or password', 401);
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            Response::error('Account is not active. Please contact admin.', 403);
        }
        
        // Generate tokens
        $accessToken = Auth::generateToken($user['id'], ACCESS_TOKEN_EXPIRY);
        $refreshToken = Auth::generateToken($user['id'], REFRESH_TOKEN_EXPIRY);
        
        // Remove password from response
        unset($user['password']);
        
        Response::success([
            'user' => $user,
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expiresIn' => ACCESS_TOKEN_EXPIRY,
        ], 'Login successful');
    }
    
    /**
     * Logout user
     */
    private function logout() {
        Auth::requireAuth();
        
        // In a real app, you might want to blacklist the token
        Response::success(null, 'Logged out successfully');
    }
    
    /**
     * Refresh access token
     */
    private function refresh() {
        $input = $this->getInput();
        $this->validateRequired($input, ['refreshToken']);
        
        $payload = Auth::validateToken($input['refreshToken']);
        
        if (!$payload) {
            Response::error('Invalid or expired refresh token', 401);
        }
        
        // Generate new access token
        $accessToken = Auth::generateToken($payload['user_id'], ACCESS_TOKEN_EXPIRY);
        
        Response::success([
            'accessToken' => $accessToken,
            'expiresIn' => ACCESS_TOKEN_EXPIRY,
        ], 'Token refreshed');
    }
    
    /**
     * Get current user profile
     */
    private function getProfile() {
        $auth = Auth::requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT id, student_id, email, first_name, last_name, middle_name,
                   course, year_level, section, contact_number, address,
                   profile_image, created_at
            FROM students 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $auth['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            Response::error('User not found', 404);
        }
        
        Response::success($user);
    }
    
    /**
     * Change password
     */
    private function changePassword() {
        $auth = Auth::requireAuth();
        $input = $this->getInput();
        $this->validateRequired($input, ['current_password', 'new_password']);
        
        // Get current password hash
        $stmt = $this->db->prepare("SELECT password FROM students WHERE id = :id");
        $stmt->execute(['id' => $auth['user_id']]);
        $user = $stmt->fetch();
        
        // Verify current password
        if (!Auth::verifyPassword($input['current_password'], $user['password'])) {
            Response::error('Current password is incorrect', 400);
        }
        
        // Update password
        $newHash = Auth::hashPassword($input['new_password']);
        $stmt = $this->db->prepare("UPDATE students SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $newHash, 'id' => $auth['user_id']]);
        
        Response::success(null, 'Password changed successfully');
    }
}

