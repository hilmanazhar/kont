<?php
/**
 * Payment Info API
 * - GET ?user_id=X : Get payment info for a user
 * - POST : Update current user's payment info (FormData or JSON)
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getPaymentInfo();
        break;
    case 'POST':
        updatePaymentInfo();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getPaymentInfo() {
    requireAuth();
    
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) {
        jsonResponse(['error' => 'user_id required'], 400);
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT id, display_name, phone_wa, bank_name, bank_account, ewallet_type, ewallet_number, qris_image
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    jsonResponse(['payment_info' => $user]);
}

function updatePaymentInfo() {
    $userId = requireAuth();
    
    $pdo = getDB();
    
    // Get input - handle both FormData ($_POST) and JSON
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    // Handle QRIS image upload
    $qrisImage = null;
    if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['qris_image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed)) {
            jsonResponse(['error' => 'Invalid file type'], 400);
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            jsonResponse(['error' => 'File too large (max 5MB)'], 400);
        }
        
        $uploadDir = '../uploads/qris/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'qris_' . $userId . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $qrisImage = 'uploads/qris/' . $filename;
        }
    }
    
    // Build update query
    $updates = [];
    $params = [];
    
    if (isset($input['bank_name'])) {
        $updates[] = "bank_name = ?";
        $params[] = $input['bank_name'] ?: null;
    }
    if (isset($input['bank_account'])) {
        $updates[] = "bank_account = ?";
        $params[] = $input['bank_account'] ?: null;
    }
    if (isset($input['ewallet_type'])) {
        $updates[] = "ewallet_type = ?";
        $params[] = $input['ewallet_type'] ?: null;
    }
    if (isset($input['ewallet_number'])) {
        $updates[] = "ewallet_number = ?";
        $params[] = $input['ewallet_number'] ?: null;
    }
    if (isset($input['phone_wa'])) {
        $updates[] = "phone_wa = ?";
        $params[] = $input['phone_wa'] ?: null;
    }
    if ($qrisImage) {
        $updates[] = "qris_image = ?";
        $params[] = $qrisImage;
    }
    
    if (empty($updates)) {
        jsonResponse(['error' => 'No fields to update'], 400);
    }
    
    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    jsonResponse(['success' => true, 'message' => 'Payment info updated']);
}
