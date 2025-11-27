<?php
/**
 * 이미지 업로드 API
 * POST /api/upload_image.php
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

setCorsHeaders();

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('잘못된 요청 방식입니다.', 405);
}

try {
    // 파일 업로드 확인
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        sendErrorResponse('이미지를 업로드해주세요.', 400);
    }
    
    $file = $_FILES['image'];
    
    // 파일 크기 확인 (최대 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        sendErrorResponse('이미지 크기는 5MB 이하여야 합니다.', 400);
    }
    
    // 파일 확장자 확인
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        sendErrorResponse('허용되지 않는 파일 형식입니다. (jpg, jpeg, png, gif, webp만 가능)', 400);
    }
    
    // 이미지 타입 확인
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        sendErrorResponse('올바른 이미지 파일이 아닙니다.', 400);
    }
    
    // 업로드 디렉토리 생성
    $uploadDir = dirname(__DIR__) . '/uploads/assets/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 파일명 생성 (유니크)
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // 파일 이동
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        sendErrorResponse('파일 업로드 중 오류가 발생했습니다.', 500);
    }
    
    // URL 생성
    $fileUrl = '/jesan/uploads/assets/' . $fileName;
    
    // [수정] 프론트엔드가 result.data.url로 찾을 수 있도록 'data' 배열로 감싸서 전송 - 제미나이
    sendSuccessResponse([
        'data' => [
            'url' => $fileUrl,
            'filename' => $fileName,
            'size' => $file['size'],
            'width' => $imageInfo[0],
            'height' => $imageInfo[1]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("이미지 업로드 오류: " . $e->getMessage());
    sendErrorResponse('이미지 업로드 중 오류가 발생했습니다.', 500);
}
