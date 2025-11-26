<?php
/**
 * 데이터베이스 설정
 */

// 에러 리포팅 설정 (운영 환경)
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// 데이터베이스 설정
define('DB_HOST', 'localhost');
define('DB_USER', 'im4u798');  // 실제 DB 사용자명으로 변경 필요
define('DB_PASS', 'dbi73043365k!!');      // 실제 DB 비밀번호로 변경 필요
define('DB_NAME', 'im4u798');
define('DB_CHARSET', 'utf8mb4');

// 테이블 프리픽스
define('TABLE_PREFIX', 'jesan_');

// 타임존 설정
date_default_timezone_set('Asia/Seoul');

/**
 * 데이터베이스 연결
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("데이터베이스 연결 오류: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '데이터베이스 연결에 실패했습니다.'
        ]);
        exit;
    }
}

/**
 * 테이블명 반환 (프리픽스 포함)
 */
function table($name) {
    return TABLE_PREFIX . $name;
}

/**
 * CORS 헤더 설정
 */
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    
    // OPTIONS 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * JSON 응답 전송
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * 에러 응답 전송
 */
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * 성공 응답 전송
 */
function sendSuccessResponse($data = [], $message = '') {
    $response = ['success' => true];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    sendJsonResponse($response);
}
