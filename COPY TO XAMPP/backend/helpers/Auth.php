<?php
/**
 * Auth Helper
 * JWT Token handling and authentication
 */

class Auth {
    /**
     * Generate JWT token
     */
    public static function generateToken($userId, $expiry = ACCESS_TOKEN_EXPIRY) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + $expiry,
        ]);
        
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        $base64Signature = self::base64UrlEncode($signature);
        
        return "$base64Header.$base64Payload.$base64Signature";
    }
    
    /**
     * Validate JWT token
     */
    public static function validateToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        
        // Verify signature
        $signature = self::base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Get authenticated user from request
     */
    public static function getAuthenticatedUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader)) {
            return null;
        }
        
        // Extract token from "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            return self::validateToken($token);
        }
        
        return null;
    }
    
    /**
     * Require authentication - returns user or sends 401
     */
    public static function requireAuth() {
        $user = self::getAuthenticatedUser();
        
        if (!$user) {
            Response::error('Unauthorized. Please login.', 401);
        }
        
        return $user;
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
