<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// 관리자 권한 확인
//if (!isLoggedIn() || !isAdmin()) {
  //  header('Location: login.php');
   // exit;
//}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>재산 관리 - 공유재산 플랫폼</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
	<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=257fdd3647dd6abdb05eae8681106514&libraries=services"></script>
    <style>
        /* 페이지네이션 스타일 */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
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

        /* 검색 필터 바 스타일 */
        .search-filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .search-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            min-width: 120px;
        }

        /* 모달 스타일 (기존 유지) */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            overflow-y: auto;
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            padding: 24px 32px;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 { margin: 0; font-size: 24px; font-weight: 700; color: #1f2937; }
        .modal-close {
            width: 36px; height: 36px; border-radius: 50%; border: none;
            background: #f3f4f6; color: #6b7280; font-size: 24px; cursor: pointer; transition: all 0.2s;
        }
        .modal-close:hover { background: #e5e7eb; color: #1f2937; }
        .modal-body { padding: 32px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: span 2; }
        .form-group label { font-size: 14px; font-weight: 600; color: #374151; }
        .form-group label.required::after { content: ' *'; color: #ef4444; }
        .form-input, .form-select, .form-textarea {
            padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: all 0.2s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none; border-color: #667eea; box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .form-textarea { min-height: 100px; resize: vertical; }
        
        /* 이미지 업로드 스타일 */
        .image-upload-area {
            border: 3px dashed #d1d5db; border-radius: 12px; padding: 32px; text-align: center;
            cursor: pointer; transition: all 0.3s; background: #f9fafb;
        }
        .image-upload-area.dragover { border-color: #667eea; background: #eef2ff; }
        .image-upload-area:hover { border-color: #667eea; }
        .upload-icon { font-size: 48px; margin-bottom: 16px; }
        .image-preview-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-top: 16px; }
        .image-preview-item {
            position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden;
            border: 2px solid #e5e7eb; cursor: move;
        }
        .image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview-item .remove-btn {
            position: absolute; top: 4px; right: 4px; width: 24px; height: 24px;
            border-radius: 50%; background: #ef4444; color: white; border: none; cursor: pointer; opacity: 0; transition: opacity 0.2s;
        }
        .image-preview-item:hover .remove-btn { opacity: 1; }
        .primary-badge {
            position: absolute; bottom: 4px; left: 4px; background: #667eea; color: white;
            font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600;
        }

        .modal-footer {
            padding: 24px 32px; border-top: 2px solid #f3f4f6; display: flex; justify-content: flex-end; gap: 12px;
        }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 14px; }
        .btn-cancel { background: #f3f4f6; color: #6b7280; }
        .btn-save { background: #667eea; color: white; }
        
        /* 테이블 및 배지 */
        .assets-table-container {
            background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;
        }
        .assets-table { width: 100%; border-collapse: collapse; }
        .assets-table th { padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
        .assets-table td { padding: 16px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .badge-status { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-normal { background: #d1fae5; color: #065f46; }
        .badge-maintenance { background: #fed7aa; color: #92400e; }
        .badge-disabled { background: #fee2e2; color: #991b1b; }
        .action-buttons { display: flex; gap: 8px; }
        .btn-edit { background: #3b82f6; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-delete { background: #ef4444; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; }
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
            <a href="index.php" class="nav-item">
                <span class="nav-icon">📊</span><span>대시보드</span>
            </a>
            <a href="assets.php" class="nav-item active">
                <span class="nav-icon">🏢</span><span>재산 관리</span>
            </a>
            <a href="bookings.php" class="nav-item">
                <span class="nav-icon">📅</span><span>예약 관리</span>
            </a>
            <a href="users.php" class="nav-item">
                <span class="nav-icon">👥</span><span>사용자 관리</span>
            </a>
            <a href="reviews.php" class="nav-item">
                <span class="nav-icon">⭐</span><span>리뷰 관리</span>
            </a>
            <a href="statistics.php" class="nav-item">
                <span class="nav-icon">📈</span><span>통계</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <button class="btn-logout" onclick="window.location.href='logout.php'">로그아웃</button>
        </div>
    </aside>

    <main class="main-content">
		 <header class="content-header">
            <h2>재산 관리</h2>
            <div class="header-actions">
			    <button class="btn btn-primary" onclick="openAddModal()">+ 재산 추가</button>
                <button class="btn-icon" title="알림">
                    <span>🔔</span>
                    <span class="badge">3</span>
                </button>
                <div class="user-info">
                    <span>관리자</span>
                    <div class="avatar">👤</div>
                </div>
            </div>
        </header

        <div class="content-body" style="padding: 24px;">
            <div class="search-filter-bar">
                <input type="text" id="searchInput" class="search-input" placeholder="재산명, 주소 검색..." oninput="applyFilters()">
                <select id="categoryFilter" class="filter-select" onchange="applyFilters()">
                    <option value="">전체 카테고리</option>
                    <option value="건물">건물</option>
                    <option value="시설">시설</option>
                    <option value="공원">공원</option>
                    <option value="토지">토지</option>
                    <option value="장비">장비</option>
                </select>
                <select id="statusFilter" class="filter-select" onchange="applyFilters()">
                    <option value="">전체 상태</option>
                    <option value="정상">정상</option>
                    <option value="점검중">점검중</option>
                    <option value="사용불가">사용불가</option>
                </select>
            </div>

            <div class="assets-table-container">
                <div id="assetsTableContainer">
                    <div class="loading">
                        <p>데이터를 불러오는 중...</p>
                    </div>
                </div>
                <div id="pagination" class="pagination"></div>
            </div>
        </div>
    </main>

    <div class="modal" id="assetModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">재산 추가</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="assetForm" onsubmit="saveAsset(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label class="required">재산명</label>
                            <input type="text" name="name" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="required">카테고리</label>
                            <select name="category" class="form-select" required>
                                <option value="">선택하세요</option>
                                <option value="건물">건물</option>
                                <option value="시설">시설</option>
                                <option value="공원">공원</option>
                                <option value="토지">토지</option>
                                <option value="장비">장비</option>
                                <option value="녹지">녹지</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>하위 카테고리</label>
                            <input type="text" name="sub_category" class="form-input" placeholder="예: 문화시설">
                        </div>

                        <div class="form-group full" style="background: #f0fdf4; padding: 16px; border-radius: 8px; border: 1px solid #bbf7d0;">
                            <label>📍 위치 자동 입력</label>
                            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                <input type="text" id="placeSearchInput" class="form-input" placeholder="장소명 또는 주소를 입력하세요 (예: 목포시청)">
                                <button type="button" class="btn" style="background: #10b981; color: white;" onclick="searchPlace()">검색</button>
                            </div>
                            <div id="searchResultList" style="max-height: 150px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 4px; display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label class="required">위도</label>
                            <input type="number" step="any" name="latitude" id="inputLat" class="form-input" required placeholder="자동 입력됩니다">
                        </div>
                        
                        <div class="form-group">
                            <label class="required">경도</label>
                            <input type="number" step="any" name="longitude" id="inputLng" class="form-input" required placeholder="자동 입력됩니다">
                        </div>


                                               
                        <div class="form-group full">
                            <label>주소</label>
                            <input type="text" name="address" class="form-input" placeholder="전라남도 목포시 ...">
                        </div>
                        
                        <div class="form-group">
                            <label class="required">행정동</label>
                            <select name="dong" class="form-select" required>
                                <option value="">선택하세요</option>
                                <option value="용해동">용해동</option>
                                <option value="석현동">석현동</option>
                                <option value="산정동">산정동</option>
                                <option value="죽교동">죽교동</option>
                                <option value="온금동">온금동</option>
                                <option value="연산동">연산동</option>
                                <option value="이로동">이로동</option>
                                <option value="대성동">대성동</option>
                                <option value="달동">달동</option>
                                <option value="죽동">죽동</option>
                                <option value="용당1동">용당1동</option>
                                <option value="용당2동">용당2동</option>
                                <option value="유달동">유달동</option>
                                <option value="만호동">만호동</option>
                                <option value="금화동">금화동</option>
                                <option value="금동">금동</option>
                                <option value="죽암동">죽암동</option>
                                <option value="하당동">하당동</option>
                                <option value="상동">상동</option>
                                <option value="옥암동">옥암동</option>
                                <option value="부주동">부주동</option>
                                <option value="목원동">목원동</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>면적 (㎡)</label>
                            <input type="number" step="any" name="area" class="form-input" placeholder="예: 8500">
                        </div>
                        
                        <div class="form-group">
                            <label>💰 금액 (원)</label>
                            <input type="number" name="price" class="form-input" placeholder="예: 125000000000">
                        </div>
                        
                        <div class="form-group">
                            <label>수용인원</label>
                            <input type="number" name="capacity" class="form-input" placeholder="예: 300">
                        </div>
                        
                        <div class="form-group">
                            <label>상태</label>
                            <select name="status" class="form-select">
                                <option value="정상">정상</option>
                                <option value="점검중">점검중</option>
                                <option value="사용불가">사용불가</option>
                            </select>
                        </div>
                        
                        <div class="form-group full">
                            <label>설명</label>
                            <textarea name="description" class="form-textarea" placeholder="재산에 대한 상세 설명을 입력하세요"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>관리부서</label>
                            <input type="text" name="manager" class="form-input" placeholder="예: 목포시청 총무과">
                        </div>
                        
                        <div class="form-group">
                            <label>연락처</label>
                            <input type="text" name="contact" class="form-input" placeholder="예: 061-270-2000">
                        </div>
                        
                        <div class="form-group full">
                            <label>🌐 360 VR 항공 사진 URL</label>
                            <input type="url" name="vr_aerial_url" class="form-input" placeholder="https://...">
                        </div>
                        
                        <div class="form-group full">
                            <label>🌐 360 VR 지상 사진 URL</label>
                            <input type="url" name="vr_ground_url" class="form-input" placeholder="https://...">
                        </div>
                        
                        <div class="form-group full">
                            <label>🎬 유튜브 동영상 URL</label>
                            <input type="url" name="youtube_url" class="form-input" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        
                        <div class="form-group full">
                            <label>📸 이미지 (최대 5장)</label>
                            <div class="image-upload-area" id="imageUploadArea">
                                <div class="upload-icon">📁</div>
                                <div class="upload-text">클릭하거나 이미지를 드래그하세요</div>
                                <div class="upload-hint">JPG, PNG, GIF, WEBP (최대 5MB, 최대 5장)</div>
                                <input type="file" id="imageInput" accept="image/*" multiple style="display: none;">
                            </div>
                            <div class="image-preview-grid" id="imagePreviewGrid"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">취소</button>
                    <button type="submit" class="btn btn-save">저장</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/config.js"></script>
    <script>
        let allAssets = [];      // 전체 데이터 저장용
        let filteredAssets = []; // 필터링된 데이터 저장용
        let uploadedImages = [];
        let editingAssetId = null;
        
        // [페이지네이션 설정] 15개씩 보기
        const itemsPerPage = 15;
        let currentPage = 1;
        
        // 페이지 로드
        document.addEventListener('DOMContentLoaded', function() {
            loadAssets();
            setupImageUpload();
        });
        
        // 재산 목록 로드 (전체 로드 후 Client-side Pagination)
        async function loadAssets() {
            try {
                // API 호출 시 limit을 충분히 크게 잡아 전체 데이터를 가져옵니다.
                const response = await fetch('../api/assets.php?limit=10000');
                
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const data = await response.json();
                
                if (data.success && data.assets) {
                    allAssets = data.assets;      // 전체 데이터 저장
                    filteredAssets = allAssets;   // 초기엔 전체가 필터 대상
                    applyFilters();               // 필터 적용 및 테이블 렌더링 호출
                } else {
                    assets = [];
                    renderAssetsTable();
                }
            } catch (error) {
                console.error('Error:', error);
                showError('서버와 통신 중 오류가 발생했습니다.');
            }
        }
        
        // 필터 적용 함수
        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            filteredAssets = allAssets.filter(asset => {
                const matchesSearch = !search || 
                    (asset.name && asset.name.toLowerCase().includes(search)) ||
                    (asset.address && asset.address.toLowerCase().includes(search));
                const matchesCategory = !category || asset.category === category;
                const matchesStatus = !status || asset.status === status;
                
                return matchesSearch && matchesCategory && matchesStatus;
            });
            
            currentPage = 1; // 필터 변경 시 1페이지로 리셋
            renderAssetsTable();
        }

       
       // 테이블 렌더링 (페이지네이션 + 역순 번호 적용)
        function renderAssetsTable() {
            const container = document.getElementById('assetsTableContainer');
            
            if (filteredAssets.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <p>데이터가 없습니다.</p>
                    </div>
                `;
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            
            // 데이터 슬라이싱
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageData = filteredAssets.slice(startIndex, endIndex);
            
            // [중요] 전체 개수 (필터링된 기준)
            const totalCount = filteredAssets.length;
            
            let html = `
                <table class="assets-table">
                    <thead>
                        <tr>
                            <th>No.</th> <th>재산명</th>
                            <th>카테고리</th>
                            <th>💰 금액</th>
							<th>📐 면적</th>
                            <th>📸 이미지</th>
                            <th>상태</th>
                            <th>📅 등록일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            pageData.forEach((asset, index) => {
                // [계산식] 역순 번호 = 전체개수 - (현재페이지시작인덱스 + 현재줄인덱스)
                const virtualId = totalCount - (startIndex + index);
                
                const price = asset.price ? formatPrice(asset.price) : '-';
				const area = asset.area ? formatArea(asset.area) : '-';
                const imageCount = asset.images ? asset.images.length : 0;
                const createdAt = asset.created_at ? new Date(asset.created_at).toLocaleDateString('ko-KR') : '-';
                const statusClass = asset.status === '정상' ? 'badge-normal' : 
                                   asset.status === '점검중' ? 'badge-maintenance' : 'badge-disabled';
                
                html += `
                    <tr>
                        <td>${virtualId}</td> <td><strong>${asset.name}</strong></td>
                        <td>${asset.category}</td>
                        <td><strong>${price}</strong></td>
                        <td>${area}</td> <td>${imageCount}장</td>
                        <td><span class="badge-status ${statusClass}">${asset.status}</span></td>
                        <td>${createdAt}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="openEditModal(${asset.id})">수정</button>
                                <button class="btn-delete" onclick="deleteAsset(${asset.id})">삭제</button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            container.innerHTML = html;
            renderPagination();
        }

        // 페이지네이션 버튼 생성
        function renderPagination() {
            const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
            const container = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // 이전 버튼
            html += `<button class="btn-page" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>이전</button>`;
            
            // 페이지 번호 (최대 5개만 표시)
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
            
            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="btn-page ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            }
            
            // 다음 버튼
            html += `<button class="btn-page" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>다음</button>`;
            
            container.innerHTML = html;
        }
        
        // 페이지 변경
        function changePage(page) {
            const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderAssetsTable();
        }
        
        // 모달 열기 (추가)
        function openAddModal() {
            editingAssetId = null;
            document.getElementById('modalTitle').textContent = '재산 추가';
            document.getElementById('assetForm').reset();
            uploadedImages = [];
            renderImagePreviews();
            document.getElementById('assetModal').classList.add('active');
        }
        
        // 모달 열기 (수정)
        function openEditModal(id) {
            const asset = allAssets.find(a => a.id === id);
            if (!asset) return;
            
            editingAssetId = id;
            document.getElementById('modalTitle').textContent = '재산 수정';
            
            const form = document.getElementById('assetForm');
            form.elements.name.value = asset.name || '';
            form.elements.category.value = asset.category || '';
            form.elements.sub_category.value = asset.sub_category || '';
            form.elements.latitude.value = asset.latitude || '';
            form.elements.longitude.value = asset.longitude || '';
            form.elements.address.value = asset.address || '';
            form.elements.dong.value = asset.dong || '';
            form.elements.area.value = asset.area || '';
            form.elements.price.value = asset.price || '';
            form.elements.capacity.value = asset.capacity || '';
            form.elements.status.value = asset.status || '정상';
            form.elements.description.value = asset.description || '';
            form.elements.manager.value = asset.manager || '';
            form.elements.contact.value = asset.contact || '';
            form.elements.vr_aerial_url.value = asset.vr_aerial_url || '';
            form.elements.vr_ground_url.value = asset.vr_ground_url || '';
            form.elements.youtube_url.value = asset.youtube_url || '';
            
            // 이미지 로드 (문자열이면 파싱, 아니면 그대로 사용)
            if (typeof asset.images === 'string') {
                try {
                    uploadedImages = JSON.parse(asset.images);
                } catch(e) { uploadedImages = []; }
            } else {
                uploadedImages = asset.images || [];
            }
            renderImagePreviews();
            
            document.getElementById('assetModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('assetModal').classList.remove('active');
        }
        
        // 재산 저장
        async function saveAsset(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            const data = {
                action: editingAssetId ? 'update' : 'create',
                name: formData.get('name'),
                category: formData.get('category'),
                sub_category: formData.get('sub_category'),
                latitude: formData.get('latitude'),
                longitude: formData.get('longitude'),
                address: formData.get('address'),
                dong: formData.get('dong'),
                area: formData.get('area'),
                price: formData.get('price'),
                capacity: formData.get('capacity'),
                status: formData.get('status'),
                description: formData.get('description'),
                manager: formData.get('manager'),
                contact: formData.get('contact'),
                vr_aerial_url: formData.get('vr_aerial_url'),
                vr_ground_url: formData.get('vr_ground_url'),
                youtube_url: formData.get('youtube_url'),
                images: JSON.stringify(uploadedImages)
            };
            
            if (editingAssetId) data.id = editingAssetId;
            
            try {
                const response = await fetch('../api/manage_asset.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(data)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message || '저장되었습니다.');
                    closeModal();
                    loadAssets();
                } else {
                    alert('오류: ' + (result.message || '알 수 없는 오류'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('저장 중 오류가 발생했습니다.');
            }
        }
        
        // 재산 삭제
        async function deleteAsset(id) {
            if (!confirm('정말 삭제하시겠습니까?')) return;
            try {
                const response = await fetch('../api/manage_asset.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'delete', id: id })
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message || '삭제되었습니다.');
                    loadAssets();
                } else {
                    alert('오류: ' + (result.message || '삭제 실패'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('삭제 중 오류가 발생했습니다.');
            }
        }
        
        // 이미지 관련 함수들 (업로드, 미리보기 등)
        function setupImageUpload() {
            const uploadArea = document.getElementById('imageUploadArea');
            const fileInput = document.getElementById('imageInput');
            
            uploadArea.addEventListener('click', () => {
                if (uploadedImages.length >= 5) return alert('최대 5장까지만 업로드 가능합니다.');
                fileInput.click();
            });
            
            fileInput.addEventListener('change', e => {
                handleFileSelect(e);
                e.target.value = '';
            });
            
            uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
            uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
            uploadArea.addEventListener('drop', e => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                if (uploadedImages.length >= 5) return alert('최대 5장까지만 업로드 가능합니다.');
                uploadFiles(Array.from(e.dataTransfer.files));
            });
        }
        
        function handleFileSelect(e) { uploadFiles(Array.from(e.target.files)); }
        
        async function uploadFiles(files) {
            for (const file of files) {
                if (uploadedImages.length >= 5) break;
                if (!file.type.startsWith('image/')) { alert('이미지 파일만 가능합니다.'); continue; }
                if (file.size > 5 * 1024 * 1024) { alert('파일 크기는 5MB 이하여야 합니다.'); continue; }
                
                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    const response = await fetch('../api/upload_image.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.success) {
                        uploadedImages.push(result.data.url);
                        renderImagePreviews();
                    } else {
                        alert('업로드 실패: ' + result.message);
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('업로드 중 오류 발생');
                }
            }
        }
        
        function renderImagePreviews() {
            const grid = document.getElementById('imagePreviewGrid');
            if (uploadedImages.length === 0) { grid.innerHTML = ''; return; }
            grid.innerHTML = uploadedImages.map((url, index) => `
                <div class="image-preview-item">
                    <img src="${url}" alt="Image ${index + 1}">
                    ${index === 0 ? '<div class="primary-badge">대표</div>' : ''}
                    <button type="button" class="remove-btn" onclick="removeImage(${index})">&times;</button>
                </div>
            `).join('');
        }
        
        function removeImage(index) {
            uploadedImages.splice(index, 1);
            renderImagePreviews();
        }
        
        function formatPrice(price) {
            if (price >= 100000000) return (price / 100000000).toFixed(0) + '억원';
            if (price >= 10000) return (price / 10000).toFixed(0) + '만원';
            return Number(price).toLocaleString() + '원';
        }


         // [수정] 면적 포맷팅 함수 (평수 계산 추가)
        function formatArea(area) {
            if (!area) return '-';
            
            // 평수 계산 (1㎡ = 0.3025평)
            // 소수점 반올림하여 정수로 표시 (필요시 .toFixed(1)로 소수점 표시 가능)
            const pyeong = Math.round(area * 0.3025); 
            
            return `${Number(area).toLocaleString()}㎡ (${pyeong.toLocaleString()}평)`;
        }

        
        function showError(message) {
            document.getElementById('assetsTableContainer').innerHTML = `
                <div class="empty-state"><div class="empty-state-icon">⚠️</div><p>${message}</p></div>
            `;
        }



		// [추가] 장소 검색 및 자동 입력 로직
        function searchPlace() {
            const keyword = document.getElementById('placeSearchInput').value;
            if (!keyword.trim()) {
                alert('장소명이나 주소를 입력해주세요.');
                return;
            }

            // 장소 검색 객체 생성
            const ps = new kakao.maps.services.Places();

            // 키워드로 장소 검색
            ps.keywordSearch(keyword, (data, status, pagination) => {
                const listEl = document.getElementById('searchResultList');
                listEl.innerHTML = '';
                listEl.style.display = 'block';

                if (status === kakao.maps.services.Status.OK) {
                    // 검색 결과 표시
                    data.forEach((place) => {
                        const item = document.createElement('div');
                        item.style.padding = '10px';
                        item.style.borderBottom = '1px solid #eee';
                        item.style.cursor = 'pointer';
                        item.innerHTML = `
                            <div style="font-weight:bold; color:#374151;">${place.place_name}</div>
                            <div style="font-size:12px; color:#6b7280;">${place.road_address_name || place.address_name}</div>
                        `;
                        
                        // 항목 클릭 시 데이터 채우기
                        item.onclick = () => {
                            fillAssetData(place);
                            listEl.style.display = 'none'; // 리스트 닫기
                        };
                        
                        // 마우스 오버 효과
                        item.onmouseover = () => item.style.background = '#f3f4f6';
                        item.onmouseout = () => item.style.background = 'white';
                        
                        listEl.appendChild(item);
                    });
                } else if (status === kakao.maps.services.Status.ZERO_RESULT) {
                    listEl.innerHTML = '<div style="padding:10px; text-align:center; color:#6b7280;">검색 결과가 없습니다.</div>';
                } else {
                    alert('검색 중 오류가 발생했습니다.');
                }
            });
        }

        // 선택한 장소 데이터로 폼 채우기
        function fillAssetData(place) {
            // 1. 위도, 경도, 장소명 채우기
            document.getElementById('inputLat').value = place.y;
            document.getElementById('inputLng').value = place.x;
            document.querySelector('input[name="name"]').value = place.place_name;
            
            // 2. 주소 채우기 (도로명 우선, 없으면 지번)
            const address = place.road_address_name || place.address_name;
            document.querySelector('input[name="address"]').value = address;

            // 3. 행정동 찾기 (좌표 -> 행정동 변환)
            const geocoder = new kakao.maps.services.Geocoder();
            geocoder.coord2RegionCode(place.x, place.y, (result, status) => {
                if (status === kakao.maps.services.Status.OK) {
                    // 행정동(H) 정보 찾기
                    const region = result.find(r => r.region_type === 'H');
                    if (region) {
                        const dongName = region.region_3depth_name; // 예: 용해동
                        
                        // select 박스에서 해당 동 자동 선택
                        const dongSelect = document.querySelector('select[name="dong"]');
                        // 동 이름이 select 옵션에 있는지 확인 후 선택
                        for(let i=0; i<dongSelect.options.length; i++) {
                            if(dongSelect.options[i].value === dongName) {
                                dongSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
            });
            
            alert(`'${place.place_name}' 정보가 입력되었습니다.\n나머지 정보를 확인해주세요.`);
        }


    </script>
</body>
</html>