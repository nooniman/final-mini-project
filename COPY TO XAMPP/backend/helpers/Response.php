<?php
/**
 * Response Helper
 * Standardized JSON responses
 */

class Response {
    /**
     * Send success response
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Send error response
     */
    public static function error($message = 'An error occurred', $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Send paginated response
     */
    public static function paginated($data, $page, $perPage, $total, $message = 'Success') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => (int) $total,
                'total_pages' => ceil($total / $perPage),
            ],
        ], JSON_PRETTY_PRINT);
        exit();
    }
}
