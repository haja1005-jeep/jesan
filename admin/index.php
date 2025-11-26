<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// 관리자 권한 확인
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
    <title>관리자 페이지 - 공유재산 플랫폼</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- 사이드바 -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M16 4L4 10V22L16 28L28 22V10L16 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <circle cx="16" cy="16" r="4" fill="currentColor"/>
            </svg>
            <h1>관리자</h1>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item active">
                <span class="nav-icon">📊</span>
                <span>대시보드</span>
            </a>
            <a href="assets.php" class="nav-item">
                <span class="nav-icon">🏢</span>
                <span>재산 관리</span>
            </a>
            <a href="bookings.php" class="nav-item">
                <span class="nav-icon">📅</span>
                <span>예약 관리</span>
            </a>
            <a href="users.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span>사용자 관리</span>
            </a>
            <a href="reviews.php" class="nav-item">
                <span class="nav-icon">⭐</span>
                <span>리뷰 관리</span>
            </a>
            <a href="statistics.php" class="nav-item">
                <span class="nav-icon">📈</span>
                <span>통계</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <button class="btn-logout">로그아웃</button>
        </div>
    </aside>
    
    <!-- 메인 컨텐츠 -->
    <main class="main-content">
        <!-- 헤더 -->
        <header class="content-header">
            <h2>대시보드</h2>
            <div class="header-actions">
                <button class="btn-icon" title="알림">
                    <span>🔔</span>
                    <span class="badge">3</span>
                </button>
                <div class="user-info">
                    <span>관리자</span>
                    <div class="avatar">👤</div>
                </div>
            </div>
        </header>
        
        <!-- 통계 카드 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">🏢</div>
                <div class="stat-content">
                    <div class="stat-label">전체 재산</div>
                    <div class="stat-value">248</div>
                    <div class="stat-change positive">+12 이번 달</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">📅</div>
                <div class="stat-content">
                    <div class="stat-label">진행 중인 예약</div>
                    <div class="stat-value">87</div>
                    <div class="stat-change positive">+5 오늘</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">⏳</div>
                <div class="stat-content">
                    <div class="stat-label">승인 대기</div>
                    <div class="stat-value">15</div>
                    <div class="stat-change">처리 필요</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">👥</div>
                <div class="stat-content">
                    <div class="stat-label">전체 사용자</div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-change positive">+28 이번 주</div>
                </div>
            </div>
        </div>
        
        <!-- 최근 활동 -->
        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h3>승인 대기 예약</h3>
                    <a href="bookings.php" class="link">전체보기</a>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>예약자</th>
                                <th>재산명</th>
                                <th>날짜</th>
                                <th>상태</th>
                                <th>액션</th>
                            </tr>
                        </thead>
                        <tbody id="pendingBookings">
                            <!-- 동적으로 생성 -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>최근 등록 재산</h3>
                    <a href="assets.php" class="link">전체보기</a>
                </div>
                <div class="card-body">
                    <div class="asset-list" id="recentAssets">
                        <!-- 동적으로 생성 -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 차트 -->
        <div class="content-grid">
            <div class="card wide">
                <div class="card-header">
                    <h3>예약 통계 (최근 30일)</h3>
                    <select class="select-sm">
                        <option>최근 30일</option>
                        <option>최근 7일</option>
                        <option>이번 달</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="bookingChart"></canvas>
                </div>
            </div>
        </div>
    </main>
    
    <!-- 모달 -->
    <div class="modal" id="bookingModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>예약 상세</h3>
                <button class="modal-close">✕</button>
            </div>
            <div class="modal-body" id="bookingModalBody">
                <!-- 동적으로 생성 -->
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/config.js"></script>
    <script src="../assets/js/api.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>
