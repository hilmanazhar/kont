<?php
/**
 * Info Kontrakan API
 * - GET : List all info
 * - POST : Add new info (supports image upload)
 * - DELETE ?id=X : Delete info
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        listInfo();
        break;
    case 'POST':
        addInfo();
        break;
    case 'DELETE':
        deleteInfo();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function listInfo() {
    requireAuth();
    
    $pdo = getDB();
    $limit = (int)($_GET['limit'] ?? 50);
    
    $stmt = $pdo->prepare("
        SELECT i.*, u.display_name as author_name 
        FROM info_kontrakan i 
        JOIN users u ON i.user_id = u.id 
        ORDER BY i.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $info = $stmt->fetchAll();
    
    jsonResponse(['info' => $info]);
}

function addInfo() {
    $userId = requireAuth();
    
    // Check if multipart form data (with image)
    if (isset($_POST['title'])) {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $imagePath = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                jsonResponse(['error' => 'Invalid image type'], 400);
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                jsonResponse(['error' => 'Image too large (max 5MB)'], 400);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'info_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/info/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $imagePath = 'uploads/info/' . $filename;
            }
        }
    } else {
        // JSON input
        $input = getInput();
        $title = $input['title'] ?? '';
        $content = $input['content'] ?? '';
        $imagePath = $input['image_path'] ?? null;
    }
    
    if (empty($title)) {
        jsonResponse(['error' => 'Title is required'], 400);
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO info_kontrakan (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $content, $imagePath]);
    
    jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()], 201);
}

function deleteInfo() {
    $userId = requireAuth();
    $infoId = (int)($_GET['id'] ?? 0);
    
    if (!$infoId) {
        jsonResponse(['error' => 'Info ID required'], 400);
    }
    
    $pdo = getDB();
    
    // Check ownership or admin
    $stmt = $pdo->prepare("SELECT user_id, image_path FROM info_kontrakan WHERE id = ?");
    $stmt->execute([$infoId]);
    $info = $stmt->fetch();
    
    if (!$info) {
        jsonResponse(['error' => 'Info not found'], 404);
    }
    
    if ($info['user_id'] !== $userId && $_SESSION['user_role'] !== 'admin') {
        jsonResponse(['error' => 'Not authorized'], 403);
    }
    
    // Delete image if exists
    if ($info['image_path'] && file_exists(__DIR__ . '/../' . $info['image_path'])) {
        unlink(__DIR__ . '/../' . $info['image_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM info_kontrakan WHERE id = ?");
    $stmt->execute([$infoId]);
    
    jsonResponse(['success' => true]);
}
