<?php
/**
 * 사용자 인증 시스템 (실서버용)
 */

require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function login($username, $password) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT * FROM " . table('users') . " WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            logActivity($user['id'], 'login', 'User logged in');
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => '아이디 또는 비밀번호가 올바르지 않습니다.'
            ];
        }
    } catch (Exception $e) {
        error_log("로그인 오류: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '로그인 처리 중 오류가 발생했습니다.'
        ];
    }
}

function logout() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    session_unset();
    session_destroy();
    
    return [
        'success' => true,
        'message' => '로그아웃되었습니다.'
    ];
}

function register($data) {
    try {
        $pdo = getDBConnection();
        
        $required = ['username', 'password', 'name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => "{$field}는 필수 항목입니다."
                ];
            }
        }
        
        $checkSql = "SELECT COUNT(*) as count FROM " . table('users') . " WHERE username = :username";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['username' => $data['username']]);
        $exists = $checkStmt->fetch();
        
        if ($exists['count'] > 0) {
            return [
                'success' => false,
                'message' => '이미 사용 중인 아이디입니다.'
            ];
        }
        
        if (strlen($data['password']) < 8) {
            return [
                'success' => false,
                'message' => '비밀번호는 최소 8자 이상이어야 합니다.'
            ];
        }
        
        $sql = "INSERT INTO " . table('users') . " (username, password, name, phone, email, role) 
                VALUES (:username, :password, :name, :phone, :email, 'user')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'username' => $data['username'],
            'password' => hashPassword($data['password']),
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null
        ]);
        
        $userId = $pdo->lastInsertId();
        
        logActivity($userId, 'register', 'New user registered');
        
        return [
            'success' => true,
            'message' => '회원가입이 완료되었습니다.',
            'user_id' => $userId
        ];
        
    } catch (Exception $e) {
        error_log("회원가입 오류: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '회원가입 처리 중 오류가 발생했습니다.'
        ];
    }
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT id, username, name, email, phone, role, created_at 
                FROM " . table('users') . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $_SESSION['user_id']]);
        
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("사용자 정보 조회 오류: " . $e->getMessage());
        return null;
    }
}

function updateUser($userId, $data) {
    try {
        $pdo = getDBConnection();
        
        $allowedFields = ['name', 'phone', 'email'];
        $updates = [];
        $params = ['id' => $userId];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        
        if (!empty($data['new_password'])) {
            if (strlen($data['new_password']) < 8) {
                return [
                    'success' => false,
                    'message' => '비밀번호는 최소 8자 이상이어야 합니다.'
                ];
            }
            
            $updates[] = "password = :password";
            $params['password'] = hashPassword($data['new_password']);
        }
        
        if (empty($updates)) {
            return [
                'success' => false,
                'message' => '수정할 정보가 없습니다.'
            ];
        }
        
        $sql = "UPDATE " . table('users') . " SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        logActivity($userId, 'update_profile', 'User profile updated');
        
        return [
            'success' => true,
            'message' => '회원 정보가 수정되었습니다.'
        ];
        
    } catch (Exception $e) {
        error_log("회원 정보 수정 오류: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '회원 정보 수정 중 오류가 발생했습니다.'
        ];
    }
}

function checkSessionTimeout($timeout = 3600) {
    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        
        if ($elapsed > $timeout) {
            logout();
            return false;
        }
        
        $_SESSION['login_time'] = time();
    }
    
    return true;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
