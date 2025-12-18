<?php
/**
 * Users API
 * - GET : List all users
 * - PUT : Update current user profile
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        listUsers();
        break;
    case 'PUT':
        updateProfile();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function listUsers() {
    requireAuth();
    
    $pdo = getDB();
    
    // If admin is requesting, show all users (for admin panel)
    // Otherwise, exclude admin users - they should not participate in transactions
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $stmt = $pdo->query("SELECT id, username, display_name, role FROM users ORDER BY role DESC, display_name");
    } else {
        $stmt = $pdo->query("SELECT id, username, display_name, role FROM users WHERE role != 'admin' ORDER BY display_name");
    }
    $users = $stmt->fetchAll();
    
    jsonResponse(['users' => $users]);
}

function updateProfile() {
    $userId = requireAuth();
    $input = getInput();
    
    $pdo = getDB();
    
    // Update display name if provided
    if (isset($input['display_name'])) {
        $stmt = $pdo->prepare("UPDATE users SET display_name = ? WHERE id = ?");
        $stmt->execute([$input['display_name'], $userId]);
    }
    
    // Update phone if provided
    if (isset($input['phone_wa'])) {
        $stmt = $pdo->prepare("UPDATE users SET phone_wa = ? WHERE id = ?");
        $stmt->execute([$input['phone_wa'], $userId]);
    }
    
    // Update password if provided
    if (isset($input['new_password']) && !empty($input['new_password'])) {
        $hash = password_hash($input['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
    }
    
    jsonResponse(['success' => true, 'message' => 'Profile updated']);
}
