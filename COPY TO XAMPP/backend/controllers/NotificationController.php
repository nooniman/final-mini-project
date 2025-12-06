<?php
/**
 * Notification Controller
 * Handles notification operations
 */

class NotificationController extends BaseController {
    
    public function handleRequest($method, $action, $id = null) {
        switch ($action) {
            case '':
            case 'list':
                if ($method === 'GET') {
                    $this->listNotifications();
                }
                break;
                
            case 'read-all':
                if ($method === 'PUT' || $method === 'POST') {
                    $this->markAllRead();
                }
                break;
                
            default:
                // Check if action is an ID with 'read' suffix
                if ($id === 'read' && $method === 'PUT') {
                    $this->markAsRead($action);
                } elseif (is_numeric($action) && $method === 'GET') {
                    $this->getNotification($action);
                } else {
                    Response::error('Invalid notification action', 404);
                }
        }
    }
    
    /**
     * List notifications
     */
    private function listNotifications() {
        $auth = Auth::requireAuth();
        $params = $this->getQueryParams();
        $unreadOnly = isset($params['unread']) && $params['unread'] === 'true';
        
        $sql = "
            SELECT * FROM notifications 
            WHERE student_id = :student_id
        ";
        
        if ($unreadOnly) {
            $sql .= " AND read_at IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $auth['user_id']]);
        $notifications = $stmt->fetchAll();
        
        // Get unread count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM notifications 
            WHERE student_id = :student_id AND read_at IS NULL
        ");
        $stmt->execute(['student_id' => $auth['user_id']]);
        $unread = $stmt->fetch();
        
        Response::success([
            'notifications' => $notifications,
            'unreadCount' => intval($unread['count']),
        ]);
    }
    
    /**
     * Get single notification
     */
    private function getNotification($id) {
        $auth = Auth::requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE id = :id AND student_id = :student_id
        ");
        $stmt->execute([
            'id' => $id,
            'student_id' => $auth['user_id'],
        ]);
        $notification = $stmt->fetch();
        
        if (!$notification) {
            Response::error('Notification not found', 404);
        }
        
        Response::success($notification);
    }
    
    /**
     * Mark notification as read
     */
    private function markAsRead($id) {
        $auth = Auth::requireAuth();
        
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET read_at = NOW() 
            WHERE id = :id AND student_id = :student_id AND read_at IS NULL
        ");
        $stmt->execute([
            'id' => $id,
            'student_id' => $auth['user_id'],
        ]);
        
        Response::success(null, 'Notification marked as read');
    }
    
    /**
     * Mark all notifications as read
     */
    private function markAllRead() {
        $auth = Auth::requireAuth();
        
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET read_at = NOW() 
            WHERE student_id = :student_id AND read_at IS NULL
        ");
        $stmt->execute(['student_id' => $auth['user_id']]);
        
        Response::success(null, 'All notifications marked as read');
    }
}
