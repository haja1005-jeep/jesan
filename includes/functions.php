<?php
/**
 * 공통 유틸리티 함수 (실서버용)
 */

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function getPostData() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

function getParam($key, $default = null) {
    return isset($_GET[$key]) ? sanitizeInput($_GET[$key]) : $default;
}

function getPagination($page = 1, $limit = 20) {
    $page = max(1, intval($page));
    $limit = max(1, min(100, intval($limit)));
    $offset = ($page - 1) * $limit;
    
    return [
        'page' => $page,
        'limit' => $limit,
        'offset' => $offset
    ];
}

function checkAuth() {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        sendErrorResponse('로그인이 필요합니다.', 401);
    }
    
    return $_SESSION['user_id'];
}

function checkAdmin() {
    session_start();
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        sendErrorResponse('관리자 권한이 필요합니다.', 403);
    }
    
    return $_SESSION['user_id'];
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function formatDate($date) {
    if (!$date) return null;
    return date('Y-m-d', strtotime($date));
}

function formatTime($time) {
    if (!$time) return null;
    return date('H:i', strtotime($time));
}

function formatDateTime($datetime) {
    if (!$datetime) return null;
    return date('Y-m-d H:i:s', strtotime($datetime));
}

function uploadFile($file, $uploadDir = 'uploads/') {
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('허용되지 않는 파일 형식입니다.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('파일 크기가 너무 큽니다. (최대 5MB)');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('파일 업로드에 실패했습니다.');
    }
    
    return $filepath;
}

function logActivity($userId, $action, $details = '') {
    try {
        $pdo = getDBConnection();
        
        $sql = "INSERT INTO " . table('activity_logs') . " (user_id, action, details, created_at) 
                VALUES (:user_id, :action, :details, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details
        ]);
    } catch (Exception $e) {
        error_log("로그 기록 오류: " . $e->getMessage());
    }
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    
    $latDiff = deg2rad($lat2 - $lat1);
    $lonDiff = deg2rad($lon2 - $lon1);
    
    $a = sin($latDiff / 2) * sin($latDiff / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDiff / 2) * sin($lonDiff / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
