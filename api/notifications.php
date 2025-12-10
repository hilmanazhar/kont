<?php
/**
 * Notifications API
 * - GET : Get user notifications
 * - PUT ?action=read : Mark notification(s) as read
 * - PUT ?action=read-all : Mark all as read
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$userId = requireAuth();

switch ($method) {
    case 'GET':
        getNotifications($userId);
        break;
    case 'PUT':
        markAsRead($userId);
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getNotifications($userId) {
    $pdo = getDB();
    $unreadOnly = isset($_GET['unread']);
    
    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    if ($unreadOnly) {
        $sql .= " AND is_read = FALSE";
    }
    $sql .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();
    
    // Count unread
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$userId]);
    $unreadCount = $stmt->fetch()['count'];
    
    jsonResponse([
        'notifications' => $notifications,
        'unread_count' => (int)$unreadCount
    ]);
}

function markAsRead($userId) {
    $action = $_GET['action'] ?? '';
    $pdo = getDB();
    
    if ($action === 'read-all') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->execute([$userId]);
        jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        $input = getInput();
        $notifId = (int)($input['id'] ?? 0);
        
        if (!$notifId) {
            jsonResponse(['error' => 'Notification ID required'], 400);
        }
        
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
        $stmt->execute([$notifId, $userId]);
        jsonResponse(['success' => true]);
    }
}
