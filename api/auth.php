<?php
/**
 * 인증 API (실서버용)
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

setCorsHeaders();

$action = getParam('action', '');

try {
    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'register':
            handleRegister();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'check':
            handleCheck();
            break;
        case 'update':
            handleUpdate();
            break;
        default:
            sendErrorResponse('유효하지 않은 액션입니다.', 400);
    }
} catch (Exception $e) {
    error_log("인증 API 오류: " . $e->getMessage());
    sendErrorResponse('인증 처리 중 오류가 발생했습니다.', 500);
}

function handleLogin() {
    $data = getPostData();
    
    if (empty($data['username']) || empty($data['password'])) {
        sendErrorResponse('아이디와 비밀번호를 입력해주세요.');
    }
    
    $result = login($data['username'], $data['password']);
    
    if ($result['success']) {
        sendSuccessResponse($result);
    } else {
        sendErrorResponse($result['message'], 401);
    }
}

function handleRegister() {
    $data = getPostData();
    
    $result = register($data);
    
    if ($result['success']) {
        sendSuccessResponse($result);
    } else {
        sendErrorResponse($result['message']);
    }
}

function handleLogout() {
    $result = logout();
    sendSuccessResponse($result);
}

function handleCheck() {
    if (!checkSessionTimeout()) {
        sendErrorResponse('세션이 만료되었습니다.', 401);
    }
    
    if (isLoggedIn()) {
        $user = getCurrentUser();
        sendSuccessResponse([
            'logged_in' => true,
            'user' => $user
        ]);
    } else {
        sendSuccessResponse([
            'logged_in' => false
        ]);
    }
}

function handleUpdate() {
    $userId = checkAuth();
    $data = getPostData();
    
    if (!empty($data['new_password'])) {
        if (empty($data['current_password'])) {
            sendErrorResponse('현재 비밀번호를 입력해주세요.');
        }
        
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT password FROM " . table('users') . " WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        
        if (!verifyPassword($data['current_password'], $user['password'])) {
            sendErrorResponse('현재 비밀번호가 올바르지 않습니다.');
        }
    }
    
    $result = updateUser($userId, $data);
    
    if ($result['success']) {
        sendSuccessResponse($result);
    } else {
        sendErrorResponse($result['message']);
    }
}
