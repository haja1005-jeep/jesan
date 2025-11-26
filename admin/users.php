<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>사용자 관리 - 공유재산 플랫폼</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M16 4L4 10V22L16 28L28 22V10L16 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <circle cx="16" cy="16" r="4" fill="currentColor"/>
            </svg>
            <h1>관리자</h1>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item"><span class="nav-icon">📊</span><span>대시보드</span></a>
            <a href="assets.php" class="nav-item"><span class="nav-icon">🏢</span><span>재산 관리</span></a>
            <a href="bookings.php" class="nav-item"><span class="nav-icon">📅</span><span>예약 관리</span></a>
            <a href="users.php" class="nav-item active"><span class="nav-icon">👥</span><span>사용자 관리</span></a>
            <a href="reviews.php" class="nav-item"><span class="nav-icon">⭐</span><span>리뷰 관리</span></a>
            <a href="statistics.php" class="nav-item"><span class="nav-icon">📈</span><span>통계</span></a>
        </nav>
        <div class="sidebar-footer">
            <button class="btn-logout" onclick="window.location.href='logout.php'">로그아웃</button>
        </div>
    </aside>
    <main class="main-content">
        <div class="content-header">
            <h2>사용자 관리</h2>
        </div>
        <div class="content-body">
            <div class="search-filter-bar">
                <input type="text" id="searchInput" placeholder="사용자명, 이메일 검색..." class="search-input">
                <select id="roleFilter" class="filter-select">
                    <option value="">전체 권한</option>
                    <option value="user">일반 사용자</option>
                    <option value="admin">관리자</option>
                </select>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>아이디</th>
                            <th>이름</th>
                            <th>이메일</th>
                            <th>권한</th>
                            <th>가입일</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="6" class="text-center">데이터를 불러오는 중...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
        function logout() {
            if (confirm('로그아웃하시겠습니까?')) {
                window.location.href = '/jesan/api/auth.php?action=logout';
            }
        }
    </script>
</body>
</html>
