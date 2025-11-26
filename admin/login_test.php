<?php
// 에러 표시
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 경로 확인
echo "<!-- 현재 디렉토리: " . __DIR__ . " -->\n";
echo "<!-- config.php 경로: " . realpath('../includes/config.php') . " -->\n";

// config.php 존재 확인
if (!file_exists('../includes/config.php')) {
    die('Error: config.php 파일을 찾을 수 없습니다. 경로: ../includes/config.php');
}

require_once '../includes/config.php';

// auth.php 존재 확인
if (!file_exists('../includes/auth.php')) {
    die('Error: auth.php 파일을 찾을 수 없습니다. 경로: ../includes/auth.php');
}

require_once '../includes/auth.php';

// 함수 확인
if (!function_exists('isLoggedIn')) {
    die('Error: isLoggedIn 함수가 정의되지 않았습니다.');
}

if (!function_exists('isAdmin')) {
    die('Error: isAdmin 함수가 정의되지 않았습니다.');
}

if (!function_exists('login')) {
    die('Error: login 함수가 정의되지 않았습니다.');
}

// 이미 로그인되어 있으면 대시보드로 리다이렉션
if (isLoggedIn() && isAdmin()) {
    header('Location: index.php');
    exit;
}

// 로그인 처리
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if ($username && $password) {
        try {
            $result = login($username, $password);
            
            if (isset($result['success']) && $result['success']) {
                // 관리자 권한 확인
                if (isAdmin()) {
                    header('Location: index.php');
                    exit;
                } else {
                    $error = '관리자 권한이 없습니다.';
                    logout();
                }
            } else {
                $error = isset($result['message']) ? $result['message'] : '로그인에 실패했습니다.';
            }
        } catch (Exception $e) {
            $error = '오류: ' . $e->getMessage();
        }
    } else {
        $error = '아이디와 비밀번호를 입력해주세요.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 - 공유재산 플랫폼</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #718096;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .error-message {
            background: #fee;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            border-left: 4px solid #fc8181;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .default-info {
            background: #f7fafc;
            padding: 16px;
            border-radius: 10px;
            margin-top: 24px;
            font-size: 13px;
            color: #4a5568;
            border-left: 4px solid #4299e1;
        }
        
        .default-info strong {
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 관리자 로그인</h1>
            <p>공유재산 관리 플랫폼</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">아이디</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="아이디를 입력하세요"
                    required
                    autofocus
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="비밀번호를 입력하세요"
                    required
                >
            </div>
            
            <button type="submit" class="btn-login">로그인</button>
        </form>
        
        <div class="default-info">
            <strong>기본 관리자 계정</strong><br>
            아이디: admin<br>
            비밀번호: admin123
        </div>
        
        <div class="back-link">
            <a href="../index.html">← 메인 페이지로 돌아가기</a>
        </div>
    </div>
</body>
</html>
