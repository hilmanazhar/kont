<?php
/**
 * Payment Info API - Supports Multiple Payment Methods
 * - GET ?user_id=X : Get payment info for a user
 * - POST : Add/Update/Remove payment methods
 *   - action: add|remove|update
 *   - type: bank|ewallet|qris
 *   - data: the payment method data
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getPaymentInfo();
        break;
    case 'POST':
        handlePaymentAction();
        break;
    case 'DELETE':
        removePaymentMethod();
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
        SELECT id, display_name, phone_wa, payment_methods,
               bank_name, bank_account, ewallet_type, ewallet_number, qris_image
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    // Parse payment_methods JSON or use legacy fields
    $paymentMethods = null;
    if ($user['payment_methods']) {
        $paymentMethods = json_decode($user['payment_methods'], true);
    } else {
        // Fallback to legacy single fields
        $paymentMethods = [
            'banks' => [],
            'ewallets' => [],
            'qris' => []
        ];
        if ($user['bank_name']) {
            $paymentMethods['banks'][] = [
                'name' => $user['bank_name'],
                'account' => $user['bank_account'] ?: ''
            ];
        }
        if ($user['ewallet_type']) {
            $paymentMethods['ewallets'][] = [
                'type' => $user['ewallet_type'],
                'number' => $user['ewallet_number'] ?: ''
            ];
        }
        if ($user['qris_image']) {
            $paymentMethods['qris'][] = $user['qris_image'];
        }
    }
    
    // Also return legacy format for backwards compatibility
    $user['payment_methods_parsed'] = $paymentMethods;
    
    jsonResponse(['payment_info' => $user]);
}

function handlePaymentAction() {
    $userId = requireAuth();
    $pdo = getDB();
    
    // Handle FormData or JSON
    $action = $_POST['action'] ?? null;
    $type = $_POST['type'] ?? null;
    
    if (!$action && !$type) {
        // Legacy update - single payment info
        return legacyUpdatePaymentInfo($userId);
    }
    
    // Get current payment_methods
    $stmt = $pdo->prepare("SELECT payment_methods FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    
    $methods = $row['payment_methods'] ? json_decode($row['payment_methods'], true) : [
        'banks' => [],
        'ewallets' => [],
        'qris' => []
    ];
    
    switch ($action) {
        case 'add':
            if ($type === 'bank') {
                $methods['banks'][] = [
                    'name' => $_POST['name'] ?? '',
                    'account' => $_POST['account'] ?? ''
                ];
            } elseif ($type === 'ewallet') {
                $methods['ewallets'][] = [
                    'type' => $_POST['ewallet_type'] ?? '',
                    'number' => $_POST['number'] ?? ''
                ];
            } elseif ($type === 'qris') {
                // Handle QRIS image upload
                if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] === UPLOAD_ERR_OK) {
                    $qrisPath = uploadQrisImage($_FILES['qris_image'], $userId);
                    if ($qrisPath) {
                        $methods['qris'][] = $qrisPath;
                    }
                }
            }
            break;
            
        case 'remove':
            $index = intval($_POST['index'] ?? -1);
            if ($index >= 0) {
                if ($type === 'bank' && isset($methods['banks'][$index])) {
                    array_splice($methods['banks'], $index, 1);
                } elseif ($type === 'ewallet' && isset($methods['ewallets'][$index])) {
                    array_splice($methods['ewallets'], $index, 1);
                } elseif ($type === 'qris' && isset($methods['qris'][$index])) {
                    // Optionally delete the file
                    $qrisPath = $methods['qris'][$index];
                    if (file_exists('../' . $qrisPath)) {
                        unlink('../' . $qrisPath);
                    }
                    array_splice($methods['qris'], $index, 1);
                }
            }
            break;
    }
    
    // Save updated payment_methods
    $stmt = $pdo->prepare("UPDATE users SET payment_methods = ? WHERE id = ?");
    $stmt->execute([json_encode($methods), $userId]);
    
    jsonResponse(['success' => true, 'payment_methods' => $methods]);
}

function uploadQrisImage($file, $userId) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowed)) {
        return null;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    
    $uploadDir = '../uploads/qris/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'qris_' . $userId . '_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/qris/' . $filename;
    }
    
    return null;
}

function legacyUpdatePaymentInfo($userId) {
    $pdo = getDB();
    
    // Get input
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    // Handle QRIS image upload
    $qrisImage = null;
    if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] === UPLOAD_ERR_OK) {
        $qrisImage = uploadQrisImage($_FILES['qris_image'], $userId);
    }
    
    // Build update for legacy fields
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

function removePaymentMethod() {
    $userId = requireAuth();
    $pdo = getDB();
    
    $input = getInput();
    $type = $input['type'] ?? null;
    $index = intval($input['index'] ?? -1);
    
    if (!$type || $index < 0) {
        jsonResponse(['error' => 'type and index required'], 400);
    }
    
    // Get current methods
    $stmt = $pdo->prepare("SELECT payment_methods FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    
    $methods = $row['payment_methods'] ? json_decode($row['payment_methods'], true) : [];
    
    if ($type === 'bank' && isset($methods['banks'][$index])) {
        array_splice($methods['banks'], $index, 1);
    } elseif ($type === 'ewallet' && isset($methods['ewallets'][$index])) {
        array_splice($methods['ewallets'], $index, 1);
    } elseif ($type === 'qris' && isset($methods['qris'][$index])) {
        array_splice($methods['qris'], $index, 1);
    }
    
    $stmt = $pdo->prepare("UPDATE users SET payment_methods = ? WHERE id = ?");
    $stmt->execute([json_encode($methods), $userId]);
    
    jsonResponse(['success' => true, 'payment_methods' => $methods]);
}
