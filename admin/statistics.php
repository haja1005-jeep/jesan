<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

//if (!isLoggedIn() || !isAdmin()) {
  //  header('Location: login.php');
  //  exit;
//}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통계 - 공유재산 플랫폼</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .stats-container {
            padding: 24px;
        }
        
        /* 전체 통계 카드 */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .summary-card.gold {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .summary-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .summary-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .summary-card h3 {
            font-size: 14px;
            font-weight: 500;
            margin: 0 0 8px 0;
            opacity: 0.9;
        }
        
        .summary-card .value {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        
        .summary-card .subvalue {
            font-size: 14px;
            margin-top: 4px;
            opacity: 0.8;
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
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-filter {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-filter:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-reset {
            padding: 10px 20px;
            background: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* 통계 그리드 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
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
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
            color: #1f2937;
        }
        
        /* 카테고리 통계 */
        .category-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .category-name {
            font-weight: 500;
            color: #374151;
        }
        
        .category-value {
            font-weight: 600;
            color: #667eea;
        }
        
        /* Top 10 리스트 */
        .top-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .top-item:last-child {
            border-bottom: none;
        }
        
        .top-item .rank {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
            min-width: 40px;
        }
        
        .top-item .name {
            flex: 1;
            font-weight: 500;
            color: #374151;
        }
        
        .top-item .value {
            font-weight: 600;
            color: #1f2937;
        }
        
        /* 체크박스 통계 */
        .selected-stats {
            background: #fef3c7;
            padding: 16px;
            border-radius: 8px;
            border: 2px solid #fbbf24;
            margin-bottom: 20px;
        }
        
        .selected-stats h4 {
            margin: 0 0 12px 0;
            color: #92400e;
        }
        
        .selected-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        
        .selected-item {
            text-align: center;
        }
        
        .selected-label {
            font-size: 12px;
            color: #92400e;
            margin-bottom: 4px;
        }
        
        .selected-value {
            font-size: 20px;
            font-weight: 700;
            color: #78350f;
        }
        
        /* 재산 테이블 */
        .assets-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px 24px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .table-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .assets-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .assets-table thead {
            background: #f9fafb;
        }
        
        .assets-table th {
            padding: 12px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .assets-table td {
            padding: 12px 16px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .assets-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .assets-table input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-normal {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-maintenance {
            background: #fed7aa;
            color: #92400e;
        }
        
        .text-right {
            text-align: right;
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
            <button class="btn-logout" onclick="window.location.href='logout.php'">로그아웃</button>
        </div>
    </aside>
    
    <main class="main-content">
        <div class="content-header">
            <h2>공유재산 통계</h2>
        </div>
        
        <div class="stats-container">
            <!-- 전체 통계 -->
            <div class="summary-cards" id="summaryCards">
                <div class="summary-card gold">
                    <h3>💰 총 재산 금액</h3>
                    <div class="value" id="totalPrice">0원</div>
                    <div class="subvalue">전체 공유재산 가액</div>
                </div>
                <div class="summary-card green">
                    <h3>📐 총 면적</h3>
                    <div class="value" id="totalArea">0㎡</div>
                    <div class="subvalue">전체 공유재산 면적</div>
                </div>
                <div class="summary-card blue">
                    <h3>📊 전체 개수</h3>
                    <div class="value" id="totalCount">0개</div>
                    <div class="subvalue">관리 중인 재산</div>
                </div>
            </div>
            
            <!-- 체크박스 선택 통계 -->
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
            
            <!-- 필터 섹션 -->
            <div class="filters-section">
                <h3 style="margin: 0 0 16px 0;">🔍 검색 필터</h3>
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
                        </select>
                    </div>
                    <div class="filter-group" style="display: flex; align-items: flex-end; gap: 8px;">
                        <button class="btn-filter" onclick="applyFilters()">검색</button>
                        <button class="btn-reset" onclick="resetFilters()">초기화</button>
                    </div>
                </div>
            </div>
            
            <!-- 통계 그리드 -->
            <div class="stats-grid">
                <!-- 카테고리별 금액 -->
                <div class="stat-card">
                    <h3>📈 카테고리별 금액</h3>
                    <div class="category-stats" id="categoryPriceStats"></div>
                </div>
                
                <!-- 카테고리별 면적 -->
                <div class="stat-card">
                    <h3>📊 카테고리별 면적</h3>
                    <div class="category-stats" id="categoryAreaStats"></div>
                </div>
                
                <!-- 최고 금액 Top 10 -->
                <div class="stat-card">
                    <h3>🏆 최고 금액 Top 10</h3>
                    <div class="top-list" id="topPriceList"></div>
                </div>
                
                <!-- 최소 금액 Top 10 -->
                <div class="stat-card">
                    <h3>💎 최소 금액 Top 10</h3>
                    <div class="top-list" id="bottomPriceList"></div>
                </div>
            </div>
            
            <!-- 재산 목록 테이블 -->
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
            </div>
        </div>
    </main>
    
    <script src="../assets/js/config.js"></script>
    <script>
        let allAssets = [];
        let filteredAssets = [];
        
        // 페이지 로드
        document.addEventListener('DOMContentLoaded', function() {
            loadAssetsFromAPI();
        });
        
        // API에서 재산 데이터 가져오기
        async function loadAssetsFromAPI() {
            try {
                console.log('API 호출 시작...');
                const response = await fetch('../api/assets.php?limit=1000');
                console.log('API 응답 상태:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const text = await response.text();
                console.log('API 응답 텍스트:', text.substring(0, 200));
                
                const data = JSON.parse(text);
                console.log('파싱된 데이터:', data);
                
                if (data.success && data.data && data.data.assets) {
                    allAssets = data.data.assets;
                    filteredAssets = allAssets;
                    console.log('로드된 재산 개수:', allAssets.length);
                    updateAllStats();
                } else {
                    console.error('데이터 구조 문제:', data);
                    alert('재산 데이터를 불러오는데 실패했습니다.\n콘솔을 확인해주세요. (F12)');
                }
            } catch (error) {
                console.error('상세 오류:', error);
                alert('서버와 통신 중 오류가 발생했습니다.\n\n에러: ' + error.message + '\n\n콘솔을 확인해주세요. (F12)');
            }
        }
        
        // 전체 통계 업데이트
        function updateAllStats() {
            updateSummaryCards();
            updateCategoryStats();
            updateTopLists();
            renderAssetsTable();
        }
        
        // 요약 카드 업데이트
        function updateSummaryCards() {
            const totalPrice = filteredAssets.reduce((sum, asset) => sum + (asset.price || 0), 0);
            const totalArea = filteredAssets.reduce((sum, asset) => sum + (asset.area || 0), 0);
            const totalCount = filteredAssets.length;
            
            document.getElementById('totalPrice').textContent = formatPrice(totalPrice);
            document.getElementById('totalArea').textContent = formatArea(totalArea);
            document.getElementById('totalCount').textContent = totalCount + '개';
        }
        
        // 카테고리별 통계
        function updateCategoryStats() {
            const categories = {};
            
            filteredAssets.forEach(asset => {
                if (!categories[asset.category]) {
                    categories[asset.category] = {price: 0, area: 0, count: 0};
                }
                categories[asset.category].price += asset.price || 0;
                categories[asset.category].area += asset.area || 0;
                categories[asset.category].count += 1;
            });
            
            // 금액 통계
            let priceHTML = '';
            Object.keys(categories).sort((a, b) => categories[b].price - categories[a].price).forEach(category => {
                priceHTML += `
                    <div class="category-item">
                        <span class="category-name">${category} (${categories[category].count}개)</span>
                        <span class="category-value">${formatPrice(categories[category].price)}</span>
                    </div>
                `;
            });
            document.getElementById('categoryPriceStats').innerHTML = priceHTML;
            
            // 면적 통계
            let areaHTML = '';
            Object.keys(categories).sort((a, b) => categories[b].area - categories[a].area).forEach(category => {
                areaHTML += `
                    <div class="category-item">
                        <span class="category-name">${category} (${categories[category].count}개)</span>
                        <span class="category-value">${formatArea(categories[category].area)}</span>
                    </div>
                `;
            });
            document.getElementById('categoryAreaStats').innerHTML = areaHTML;
        }
        
        // Top 10 리스트
        function updateTopLists() {
            // 최고 금액 Top 10
            const topAssets = [...filteredAssets].sort((a, b) => (b.price || 0) - (a.price || 0)).slice(0, 10);
            let topHTML = '';
            topAssets.forEach((asset, index) => {
                topHTML += `
                    <div class="top-item">
                        <span class="rank">${index + 1}</span>
                        <span class="name">${asset.name}</span>
                        <span class="value">${formatPrice(asset.price || 0)}</span>
                    </div>
                `;
            });
            document.getElementById('topPriceList').innerHTML = topHTML;
            
            // 최소 금액 Top 10
            const bottomAssets = [...filteredAssets].sort((a, b) => (a.price || 0) - (b.price || 0)).slice(0, 10);
            let bottomHTML = '';
            bottomAssets.forEach((asset, index) => {
                bottomHTML += `
                    <div class="top-item">
                        <span class="rank">${index + 1}</span>
                        <span class="name">${asset.name}</span>
                        <span class="value">${formatPrice(asset.price || 0)}</span>
                    </div>
                `;
            });
            document.getElementById('bottomPriceList').innerHTML = bottomHTML;
        }
        
        // 재산 테이블 렌더링
        function renderAssetsTable() {
            let html = '';
            filteredAssets.forEach(asset => {
                const statusClass = asset.status === '정상' ? 'badge-normal' : 'badge-maintenance';
                html += `
                    <tr>
                        <td><input type="checkbox" class="asset-checkbox" data-id="${asset.id}" onchange="updateSelectedStats()"></td>
                        <td>${asset.name}</td>
                        <td>${asset.category}</td>
                        <td><span class="badge ${statusClass}">${asset.status}</span></td>
                        <td class="text-right">${formatPrice(asset.price || 0)}</td>
                        <td class="text-right">${formatArea(asset.area || 0)}</td>
                    </tr>
                `;
            });
            document.getElementById('assetsTableBody').innerHTML = html;
        }
        
        // 전체 선택/해제
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll').checked;
            document.querySelectorAll('.asset-checkbox').forEach(checkbox => {
                checkbox.checked = selectAll;
            });
            updateSelectedStats();
        }
        
        // 선택 항목 통계 업데이트
        function updateSelectedStats() {
            const selectedIds = Array.from(document.querySelectorAll('.asset-checkbox:checked'))
                .map(cb => parseInt(cb.dataset.id));
            
            if (selectedIds.length === 0) {
                document.getElementById('selectedStats').style.display = 'none';
                return;
            }
            
            const selectedAssets = allAssets.filter(asset => selectedIds.includes(asset.id));
            const selectedPrice = selectedAssets.reduce((sum, asset) => sum + (asset.price || 0), 0);
            const selectedArea = selectedAssets.reduce((sum, asset) => sum + (asset.area || 0), 0);
            
            document.getElementById('selectedStats').style.display = 'block';
            document.getElementById('selectedCount').textContent = selectedIds.length + '개';
            document.getElementById('selectedPrice').textContent = formatPrice(selectedPrice);
            document.getElementById('selectedArea').textContent = formatArea(selectedArea);
        }
        
        // 필터 적용
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
        
        // 필터 초기화
        function resetFilters() {
            document.getElementById('minPrice').value = '';
            document.getElementById('maxPrice').value = '';
            document.getElementById('minArea').value = '';
            document.getElementById('maxArea').value = '';
            document.getElementById('categoryFilter').value = '';
            filteredAssets = allAssets;
            updateAllStats();
        }
        
        // 금액 포맷팅
        function formatPrice(price) {
            if (price >= 100000000) {
                return (price / 100000000).toFixed(0) + '억원';
            } else if (price >= 10000) {
                return (price / 10000).toFixed(0) + '만원';
            }
            return price.toLocaleString() + '원';
        }
        
        // 면적 포맷팅
        function formatArea(area) {
            if (!area) return '-';
            return area.toLocaleString() + '㎡';
        }
    </script>
</body>
</html>
