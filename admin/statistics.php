<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// 관리자 권한 확인
//if (!isLoggedIn() || !isAdmin()) {
    //header('Location: login.php');
    //exit;
//}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통계 - 공유재산 플랫폼</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container { padding: 24px; }
        
        /* 대시보드 스타일 상단 카드 */
        .dashboard-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s;
        }
        
        .summary-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-icon.blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .stat-icon.gold { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .stat-content { flex: 1; }
        .stat-label { font-size: 14px; color: #6b7280; margin-bottom: 4px; font-weight: 500; }
        .stat-value { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 2px; font-family: 'JetBrains Mono', monospace; }
        .stat-change { font-size: 13px; color: #9ca3af; }
        
        /* 차트 그리드 */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* 필터 섹션 */
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group label { font-size: 14px; font-weight: 500; color: #374151; }
        .filter-group input, .filter-group select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-filter {
            padding: 10px 20px; background: #667eea; color: white; border: none;
            border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-filter:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-reset {
            padding: 10px 20px; background: #e5e7eb; color: #374151; border: none;
            border-radius: 8px; font-weight: 600; cursor: pointer;
        }
        
        /* 텍스트 통계 그리드 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 18px; font-weight: 600; margin: 0 0 20px 0; color: #1f2937;
        }
        
        /* 리스트 스타일 */
        .category-item, .top-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px; border-bottom: 1px solid #f3f4f6;
        }
        .category-item:last-child, .top-item:last-child { border-bottom: none; }
        .rank { font-weight: 700; color: #667eea; min-width: 30px; }
        
        /* 재산 테이블 */
        .assets-table-container {
            background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;
        }
        .table-header { padding: 20px 24px; border-bottom: 2px solid #f3f4f6; }
        .table-header h3 { margin: 0; font-size: 18px; font-weight: 600; color: #1f2937; }
        .assets-table { width: 100%; border-collapse: collapse; }
        .assets-table th { padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #6b7280; background: #f9fafb; }
        .assets-table td { padding: 12px 16px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        
        /* 배지 스타일 */
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-normal { background: #d1fae5; color: #065f46; }
        .badge-maintenance { background: #fed7aa; color: #92400e; }
        
        /* 선택 통계 */
        .selected-stats {
            background: #fef3c7; padding: 16px; border-radius: 8px; border: 2px solid #fbbf24; margin-bottom: 20px;
        }
        .selected-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; text-align: center; }
        .selected-value { font-size: 20px; font-weight: 700; color: #78350f; }

        /* 페이지네이션 스타일 */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 20px;
        }
        .btn-page {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: #374151;
        }
        .btn-page:hover { background: #f3f4f6; }
        .btn-page.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .btn-page:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
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
            <a href="users.php" class="nav-item"><span class="nav-icon">👥</span><span>사용자 관리</span></a>
            <a href="reviews.php" class="nav-item"><span class="nav-icon">⭐</span><span>리뷰 관리</span></a>
            <a href="statistics.php" class="nav-item active"><span class="nav-icon">📈</span><span>통계</span></a>
        </nav>
        <div class="sidebar-footer">
            <button class="btn-logout" onclick="logout()">로그아웃</button>
        </div>
    </aside>
    
    <main class="main-content">
        <div class="content-header">
            <h2>공유재산 통계</h2>
        </div>
        
        <div class="stats-container">
            <div class="dashboard-stats-grid">
                <div class="summary-stat-card">
                    <div class="stat-icon blue">🏢</div>
                    <div class="stat-content">
                        <div class="stat-label">전체 재산</div>
                        <div class="stat-value" id="totalCount">0</div>
                        <div class="stat-change">관리 중인 재산</div>
                    </div>
                </div>
                
                <div class="summary-stat-card">
                    <div class="stat-icon gold">💰</div>
                    <div class="stat-content">
                        <div class="stat-label">총 재산 금액</div>
                        <div class="stat-value" id="totalPrice">0원</div>
                        <div class="stat-change">전체 평가액</div>
                    </div>
                </div>
                
                <div class="summary-stat-card">
                    <div class="stat-icon green">📐</div>
                    <div class="stat-content">
                        <div class="stat-label">총 면적</div>
                        <div class="stat-value" id="totalArea">0㎡</div>
                        <div class="stat-change">전체 면적</div>
                    </div>
                </div>

                <div class="summary-stat-card">
                    <div class="stat-icon purple">⭐</div>
                    <div class="stat-content">
                        <div class="stat-label">평균 평점</div>
                        <div class="stat-value" id="avgRating">-</div>
                        <div class="stat-change">사용자 리뷰 기반</div>
                    </div>
                </div>
            </div>
            
            <div class="charts-grid">
                <div class="stat-card">
                    <h3>📈 기간별 예약 추이</h3>
                    <div class="chart-wrapper">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>📊 카테고리별 분포</h3>
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>💰 카테고리별 금액</h3>
                    <div class="category-stats" id="categoryPriceStats"></div>
                </div>
                
                <div class="stat-card">
                    <h3>📐 카테고리별 면적</h3>
                    <div class="category-stats" id="categoryAreaStats"></div>
                </div>
                
                <div class="stat-card">
                    <h3>🏆 최고 금액 Top 10</h3>
                    <div class="top-list" id="topPriceList"></div>
                </div>
                
                <div class="stat-card">
                    <h3>💎 최소 금액 Top 10</h3>
                    <div class="top-list" id="bottomPriceList"></div>
                </div>
            </div>
            
            <div class="filters-section">
                <h3 style="margin: 0 0 16px 0;">🔍 상세 검색</h3>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>최소 금액 (억원)</label>
                        <input type="number" id="minPrice" placeholder="예: 100">
                    </div>
                    <div class="filter-group">
                        <label>최대 금액 (억원)</label>
                        <input type="number" id="maxPrice" placeholder="예: 1000">
                    </div>
                    <div class="filter-group">
                        <label>최소 면적 (㎡)</label>
                        <input type="number" id="minArea" placeholder="예: 1000">
                    </div>
                    <div class="filter-group">
                        <label>최대 면적 (㎡)</label>
                        <input type="number" id="maxArea" placeholder="예: 100000">
                    </div>
                    <div class="filter-group">
                        <label>카테고리</label>
                        <select id="categoryFilter">
                            <option value="">전체</option>
                            <option value="건물">건물</option>
                            <option value="시설">시설</option>
                            <option value="공원">공원</option>
                            <option value="토지">토지</option>
                            <option value="장비">장비</option>
                        </select>
                    </div>
                    <div class="filter-group" style="display: flex; align-items: flex-end; gap: 8px;">
                        <button class="btn-filter" onclick="applyFilters()">검색</button>
                        <button class="btn-reset" onclick="resetFilters()">초기화</button>
                    </div>
                </div>
            </div>

            <div class="selected-stats" id="selectedStats" style="display: none;">
                <h4>✅ 선택한 항목 통계</h4>
                <div class="selected-grid">
                    <div class="selected-item">
                        <div class="selected-label">선택 개수</div>
                        <div class="selected-value" id="selectedCount">0개</div>
                    </div>
                    <div class="selected-item">
                        <div class="selected-label">선택 금액</div>
                        <div class="selected-value" id="selectedPrice">0원</div>
                    </div>
                    <div class="selected-item">
                        <div class="selected-label">선택 면적</div>
                        <div class="selected-value" id="selectedArea">0㎡</div>
                    </div>
                </div>
            </div>

            <div class="assets-table-container">
                <div class="table-header">
                    <h3>📋 전체 재산 목록</h3>
                </div>
                <table class="assets-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>재산명</th>
                            <th>카테고리</th>
                            <th>상태</th>
                            <th class="text-right">금액</th>
                            <th class="text-right">면적</th>
                        </tr>
                    </thead>
                    <tbody id="assetsTableBody"></tbody>
                </table>
                <div id="pagination" class="pagination"></div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/config.js"></script>
    <script>
        let allAssets = [];
        let filteredAssets = [];
        
        // 페이지네이션 설정 (15개씩)
        const itemsPerPage = 15; 
        let currentPage = 1;
        
        // 페이지 로드 시 실행
        document.addEventListener('DOMContentLoaded', function() {
            loadAssetsFromAPI();
            initCharts();
        });
        
        function logout() {
            if (confirm('로그아웃하시겠습니까?')) {
                window.location.href = '../api/auth.php?action=logout';
            }
        }

        // 1. API에서 데이터 가져오기
        async function loadAssetsFromAPI() {
            try {
                console.log('API 호출 시작...');
                const response = await fetch('../api/assets.php?limit=10000');
                
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const data = await response.json();
                
                if (data.success && data.assets) { 
                    allAssets = data.assets;
                    filteredAssets = allAssets;
                    console.log('로드된 재산 개수:', allAssets.length);
                    updateAllStats();
                } else {
                    alert('데이터를 불러오는데 실패했습니다.');
                }
            } catch (error) {
                console.error('오류:', error);
            }
        }
        
        // 2. 모든 통계 및 테이블 업데이트
        function updateAllStats() {
            updateSummaryCards();
            updateCategoryStats();
            updateTopLists();
            currentPage = 1; 
            renderAssetsTable(); 
        }
        
        function updateSummaryCards() {
            const totalPrice = filteredAssets.reduce((sum, asset) => sum + (asset.price || 0), 0);
            const totalArea = filteredAssets.reduce((sum, asset) => sum + (asset.area || 0), 0);
            
            // 평균 평점 계산
            let totalRating = 0;
            let ratedCount = 0;
            filteredAssets.forEach(a => {
                if(a.avg_rating) {
                    totalRating += parseFloat(a.avg_rating);
                    ratedCount++;
                }
            });
            const avgRating = ratedCount > 0 ? (totalRating / ratedCount).toFixed(1) : '-';

            document.getElementById('totalPrice').textContent = formatPrice(totalPrice);
            document.getElementById('totalArea').textContent = formatArea(totalArea);
            document.getElementById('totalCount').textContent = filteredAssets.length;
            document.getElementById('avgRating').textContent = avgRating;
        }
        
        function updateCategoryStats() {
            const categories = {};
            filteredAssets.forEach(asset => {
                if (!categories[asset.category]) categories[asset.category] = {price: 0, area: 0, count: 0};
                categories[asset.category].price += asset.price || 0;
                categories[asset.category].area += asset.area || 0;
                categories[asset.category].count += 1;
            });
            
            let priceHTML = '';
            Object.keys(categories).sort((a, b) => categories[b].price - categories[a].price).forEach(cat => {
                priceHTML += `<div class="category-item"><span class="category-name">${cat} (${categories[cat].count})</span><span class="category-value">${formatPrice(categories[cat].price)}</span></div>`;
            });
            document.getElementById('categoryPriceStats').innerHTML = priceHTML || '<div style="text-align:center; padding:10px;">데이터 없음</div>';
            
            let areaHTML = '';
            Object.keys(categories).sort((a, b) => categories[b].area - categories[a].area).forEach(cat => {
                areaHTML += `<div class="category-item"><span class="category-name">${cat} (${categories[cat].count})</span><span class="category-value">${formatArea(categories[cat].area)}</span></div>`;
            });
            document.getElementById('categoryAreaStats').innerHTML = areaHTML || '<div style="text-align:center; padding:10px;">데이터 없음</div>';
        }
        
        function updateTopLists() {
            const topAssets = [...filteredAssets].sort((a, b) => (b.price || 0) - (a.price || 0)).slice(0, 10);
            document.getElementById('topPriceList').innerHTML = topAssets.map((a, i) => 
                `<div class="top-item"><span class="rank">${i+1}</span><span class="name">${a.name}</span><span class="value">${formatPrice(a.price||0)}</span></div>`
            ).join('') || '<div style="text-align:center; padding:10px;">데이터 없음</div>';

            const bottomAssets = [...filteredAssets].sort((a, b) => (a.price || 0) - (b.price || 0)).slice(0, 10);
            document.getElementById('bottomPriceList').innerHTML = bottomAssets.map((a, i) => 
                `<div class="top-item"><span class="rank">${i+1}</span><span class="name">${a.name}</span><span class="value">${formatPrice(a.price||0)}</span></div>`
            ).join('') || '<div style="text-align:center; padding:10px;">데이터 없음</div>';
        }
        
        function renderAssetsTable() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageData = filteredAssets.slice(startIndex, endIndex);
            
            document.getElementById('assetsTableBody').innerHTML = pageData.map(asset => `
                <tr>
                    <td><input type="checkbox" class="asset-checkbox" data-id="${asset.id}" onchange="updateSelectedStats()"></td>
                    <td>${asset.name}</td>
                    <td>${asset.category}</td>
                    <td><span class="badge ${asset.status === '정상' ? 'badge-normal' : 'badge-maintenance'}">${asset.status}</span></td>
                    <td class="text-right">${formatPrice(asset.price || 0)}</td>
                    <td class="text-right">${formatArea(asset.area || 0)}</td>
                </tr>
            `).join('') || '<tr><td colspan="6" style="text-align:center; padding:20px;">데이터가 없습니다.</td></tr>';
            
            renderPagination(); 
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
            const container = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            html += `<button class="btn-page" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>이전</button>`;
            
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
            
            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="btn-page ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            }
            
            html += `<button class="btn-page" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>다음</button>`;
            container.innerHTML = html;
        }
        
        function changePage(page) {
            const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderAssetsTable(); 
        }

        // 3. 차트 초기화
        function initCharts() {
            const bookingCtx = document.getElementById('bookingChart');
            if (bookingCtx) {
                new Chart(bookingCtx, {
                    type: 'line',
                    data: {
                        labels: ['11/20', '11/21', '11/22', '11/23', '11/24', '11/25', '11/26'],
                        datasets: [{
                            label: '예약 건수',
                            data: [12, 19, 3, 5, 2, 3, 15],
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const categoryCtx = document.getElementById('categoryChart');
            if (categoryCtx) {
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['시설', '공원', '건물', '토지', '장비'],
                        datasets: [{
                            data: [30, 20, 15, 25, 10],
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }

        // 유틸리티 함수들
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll').checked;
            document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = selectAll);
            updateSelectedStats();
        }
        
        function updateSelectedStats() {
            const selectedIds = Array.from(document.querySelectorAll('.asset-checkbox:checked')).map(cb => parseInt(cb.dataset.id));
            const container = document.getElementById('selectedStats');
            
            if (selectedIds.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            const selected = allAssets.filter(a => selectedIds.includes(a.id));
            const price = selected.reduce((sum, a) => sum + (a.price || 0), 0);
            const area = selected.reduce((sum, a) => sum + (a.area || 0), 0);
            
            container.style.display = 'block';
            document.getElementById('selectedCount').textContent = selectedIds.length + '개';
            document.getElementById('selectedPrice').textContent = formatPrice(price);
            document.getElementById('selectedArea').textContent = formatArea(area);
        }

        function applyFilters() {
            const minPrice = parseFloat(document.getElementById('minPrice').value) * 100000000 || 0;
            const maxPrice = parseFloat(document.getElementById('maxPrice').value) * 100000000 || Infinity;
            const minArea = parseFloat(document.getElementById('minArea').value) || 0;
            const maxArea = parseFloat(document.getElementById('maxArea').value) || Infinity;
            const category = document.getElementById('categoryFilter').value;
            
            filteredAssets = allAssets.filter(asset => {
                const priceMatch = (asset.price || 0) >= minPrice && (asset.price || 0) <= maxPrice;
                const areaMatch = (asset.area || 0) >= minArea && (asset.area || 0) <= maxArea;
                const categoryMatch = !category || asset.category === category;
                return priceMatch && areaMatch && categoryMatch;
            });
            updateAllStats();
        }

        function resetFilters() {
            ['minPrice', 'maxPrice', 'minArea', 'maxArea', 'categoryFilter'].forEach(id => document.getElementById(id).value = '');
            filteredAssets = allAssets;
            updateAllStats();
        }

        function formatPrice(price) {
            if (price >= 100000000) return (price / 100000000).toFixed(0) + '억원';
            if (price >= 10000) return (price / 10000).toFixed(0) + '만원';
            return price.toLocaleString() + '원';
        }

        // [수정] 면적 포맷팅 (평수 추가)
        function formatArea(area) {
            if (!area) return '-';
            const pyeong = Math.round(area * 0.3025);
            return `${Number(area).toLocaleString()}㎡ (${pyeong.toLocaleString()}평)`;
        }
    </script>
</body>
</html>