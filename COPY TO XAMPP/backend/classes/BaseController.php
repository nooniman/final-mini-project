<?php
/**
 * Base Controller
 * Parent class for all controllers
 */

class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = getConnection();
    }
    
    /**
     * Handle incoming request
     */
    public function handleRequest($method, $action, $id = null) {
        // Override in child classes
        Response::error('Method not implemented', 501);
    }
    
    /**
     * Get JSON input from request body
     */
    protected function getInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Get query parameters
     */
    protected function getQueryParams() {
        return $_GET;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $fields) {
        $errors = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The $field field is required."];
            }
        }
        
        if (!empty($errors)) {
            Response::error('Validation failed', 422, $errors);
        }
        
        return true;
    }
}
