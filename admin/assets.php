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
    <title>재산 관리 - 공유재산 플랫폼</title>
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
            <a href="assets.php" class="nav-item active">
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
            <button class="btn-logout" onclick="window.location.href='logout.php'">로그아웃</button>
        </div>
    </aside>

    <!-- 메인 컨텐츠 -->
    <main class="main-content">
        <div class="content-header">
            <h2>재산 관리</h2>
            <button class="btn btn-primary" onclick="openAddModal()">+ 재산 추가</button>
        </div>

        <div class="content-body">
            <!-- 검색 및 필터 -->
            <div class="search-filter-bar">
                <input type="text" id="searchInput" placeholder="재산명, 주소 검색..." class="search-input">
                <select id="categoryFilter" class="filter-select">
                    <option value="">전체 카테고리</option>
                    <option value="시설">시설</option>
                    <option value="토지">토지</option>
                    <option value="장비">장비</option>
                    <option value="공원">공원</option>
                    <option value="녹지">녹지</option>
                    <option value="건물">건물</option>
                </select>
                <select id="statusFilter" class="filter-select">
                    <option value="">전체 상태</option>
                    <option value="정상">정상</option>
                    <option value="점검중">점검중</option>
                    <option value="사용불가">사용불가</option>
                </select>
            </div>

            <!-- 재산 목록 테이블 -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>재산명</th>
                            <th>카테고리</th>
                            <th>주소</th>
                            <th>상태</th>
                            <th>등록일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody id="assetsTableBody">
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

        // 재산 목록 로드
        async function loadAssets() {
            try {
                const search = document.getElementById('searchInput').value;
                const category = document.getElementById('categoryFilter').value;
                const status = document.getElementById('statusFilter').value;
                
                let url = `${API_BASE_URL}/assets.php?`;
                if (search) url += `search=${search}&`;
                if (category) url += `category=${category}&`;
                if (status) url += `status=${status}&`;
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    displayAssets(data.assets);
                }
            } catch (error) {
                console.error('재산 목록 로드 오류:', error);
                alert('재산 목록을 불러오는데 실패했습니다.');
            }
        }

        // 재산 목록 표시
        function displayAssets(assets) {
            const tbody = document.getElementById('assetsTableBody');
            
            if (assets.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">등록된 재산이 없습니다.</td></tr>';
                return;
            }
            
            tbody.innerHTML = assets.map(asset => `
                <tr>
                    <td>${asset.id}</td>
                    <td>${asset.name}</td>
                    <td><span class="badge">${asset.category}</span></td>
                    <td>${asset.address || '-'}</td>
                    <td><span class="status-badge status-${asset.status}">${asset.status}</span></td>
                    <td>${new Date(asset.created_at).toLocaleDateString('ko-KR')}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editAsset(${asset.id})">수정</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteAsset(${asset.id})">삭제</button>
                    </td>
                </tr>
            `).join('');
        }

        // 재산 삭제
        async function deleteAsset(id) {
            if (!confirm('정말 삭제하시겠습니까?')) return;
            
            try {
                // 실제로는 DELETE API를 호출해야 합니다
                alert('재산 삭제 기능은 API 구현이 필요합니다.');
            } catch (error) {
                console.error('삭제 오류:', error);
                alert('삭제에 실패했습니다.');
            }
        }

        // 로그아웃
        function logout() {
            if (confirm('로그아웃하시겠습니까?')) {
                window.location.href = `${API_BASE_URL}/auth.php?action=logout`;
            }
        }

        // 필터 이벤트
        document.getElementById('searchInput').addEventListener('input', debounce(loadAssets, 500));
        document.getElementById('categoryFilter').addEventListener('change', loadAssets);
        document.getElementById('statusFilter').addEventListener('change', loadAssets);

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // 초기 로드
        loadAssets();
    </script>
</body>
</html>
