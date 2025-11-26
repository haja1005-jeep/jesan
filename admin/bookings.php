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
    <title>예약 관리 - 공유재산 플랫폼</title>
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
            <a href="index.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>대시보드</span>
            </a>
            <a href="assets.php" class="nav-item">
                <span class="nav-icon">🏢</span>
                <span>재산 관리</span>
            </a>
            <a href="bookings.php" class="nav-item active">
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
            <button class="btn-logout" onclick="window.location.href='logout.php'">로그아웃</button>
        </div>
    </aside>

    <!-- 메인 컨텐츠 -->
    <main class="main-content">
        <div class="content-header">
            <h2>예약 관리</h2>
        </div>

        <div class="content-body">
            <!-- 필터 -->
            <div class="search-filter-bar">
                <select id="statusFilter" class="filter-select">
                    <option value="">전체 상태</option>
                    <option value="신청">신청</option>
                    <option value="승인">승인</option>
                    <option value="거부">거부</option>
                    <option value="취소">취소</option>
                    <option value="완료">완료</option>
                </select>
                <input type="date" id="dateFilter" class="filter-input">
            </div>

            <!-- 예약 목록 테이블 -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>재산명</th>
                            <th>신청자</th>
                            <th>예약일</th>
                            <th>시간</th>
                            <th>상태</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
                        <tr>
                            <td colspan="7" class="text-center">데이터를 불러오는 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 페이지네이션 -->
            <div id="pagination" class="pagination"></div>
        </div>
    </main>

    <script>
        const API_BASE_URL = '/jesan/api';

        // 예약 목록 로드
        async function loadBookings() {
            try {
                const status = document.getElementById('statusFilter').value;
                const date = document.getElementById('dateFilter').value;
                
                let url = `${API_BASE_URL}/booking.php?`;
                if (status) url += `status=${status}&`;
                if (date) url += `date_from=${date}&date_to=${date}&`;
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    displayBookings(data.bookings);
                }
            } catch (error) {
                console.error('예약 목록 로드 오류:', error);
                alert('예약 목록을 불러오는데 실패했습니다.');
            }
        }

        // 예약 목록 표시
        function displayBookings(bookings) {
            const tbody = document.getElementById('bookingsTableBody');
            
            if (bookings.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">예약이 없습니다.</td></tr>';
                return;
            }
            
            tbody.innerHTML = bookings.map(booking => `
                <tr>
                    <td>${booking.id}</td>
                    <td>${booking.asset_name}</td>
                    <td>${booking.user_name}</td>
                    <td>${booking.booking_date}</td>
                    <td>${booking.start_time || '-'} ~ ${booking.end_time || '-'}</td>
                    <td><span class="status-badge status-${booking.status}">${booking.status}</span></td>
                    <td>
                        ${booking.status === '신청' ? `
                            <button class="btn btn-sm btn-success" onclick="approveBooking(${booking.id})">승인</button>
                            <button class="btn btn-sm btn-danger" onclick="rejectBooking(${booking.id})">거부</button>
                        ` : '-'}
                    </td>
                </tr>
            `).join('');
        }

        // 예약 승인
        async function approveBooking(id) {
            if (!confirm('예약을 승인하시겠습니까?')) return;
            
            try {
                const response = await fetch(`${API_BASE_URL}/booking.php`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, status: '승인' })
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('승인되었습니다.');
                    loadBookings();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('승인 오류:', error);
                alert('승인에 실패했습니다.');
            }
        }

        // 예약 거부
        async function rejectBooking(id) {
            if (!confirm('예약을 거부하시겠습니까?')) return;
            
            try {
                const response = await fetch(`${API_BASE_URL}/booking.php`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, status: '거부' })
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('거부되었습니다.');
                    loadBookings();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('거부 오류:', error);
                alert('거부에 실패했습니다.');
            }
        }

        // 로그아웃
        function logout() {
            if (confirm('로그아웃하시겠습니까?')) {
                window.location.href = `${API_BASE_URL}/auth.php?action=logout`;
            }
        }

        // 필터 이벤트
        document.getElementById('statusFilter').addEventListener('change', loadBookings);
        document.getElementById('dateFilter').addEventListener('change', loadBookings);

        // 초기 로드
        loadBookings();
    </script>
</body>
</html>
