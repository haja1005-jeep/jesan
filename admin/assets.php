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
    <style>
        /* 모달 스타일 */
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
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 24px 32px;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .modal-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .modal-close:hover {
            background: #e5e7eb;
            color: #1f2937;
        }
        
        .modal-body {
            padding: 32px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group.full {
            grid-column: span 2;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group label.required::after {
            content: ' *';
            color: #ef4444;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        /* 이미지 업로드 영역 */
        .image-upload-area {
            border: 3px dashed #d1d5db;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f9fafb;
        }
        
        .image-upload-area.dragover {
            border-color: #667eea;
            background: #eef2ff;
        }
        
        .image-upload-area:hover {
            border-color: #667eea;
        }
        
        .upload-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .upload-text {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .upload-hint {
            font-size: 14px;
            color: #6b7280;
        }
        
        /* 이미지 미리보기 */
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-top: 16px;
        }
        
        .image-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
            cursor: move;
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview-item .remove-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ef4444;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .image-preview-item:hover .remove-btn {
            opacity: 1;
        }
        
        .image-preview-item .primary-badge {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: #667eea;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        /* 버튼 */
        .modal-footer {
            padding: 24px 32px;
            border-top: 2px solid #f3f4f6;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 14px;
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .btn-cancel:hover {
            background: #e5e7eb;
        }
        
        .btn-save {
            background: #667eea;
            color: white;
        }
        
        .btn-save:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        /* 테이블 */
        .assets-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .assets-table thead {
            background: #f9fafb;
        }
        
        .assets-table th {
            padding: 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .assets-table td {
            padding: 16px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .assets-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2563eb;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .btn-delete:hover {
            background: #dc2626;
        }
        
        .badge-status {
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
        
        .badge-disabled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #6b7280;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
    </style>
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

        <div class="content-body" style="padding: 24px;">
            <!-- 재산 목록 -->
            <div id="assetsTableContainer">
                <div class="loading">
                    <p>데이터를 불러오는 중...</p>
                </div>
            </div>
        </div>
    </main>

    <!-- 재산 추가/수정 모달 -->
    <div class="modal" id="assetModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">재산 추가</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="assetForm" onsubmit="saveAsset(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <!-- 기본 정보 -->
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
                        
                        <div class="form-group">
                            <label class="required">위도</label>
                            <input type="number" step="any" name="latitude" class="form-input" required placeholder="예: 34.8118">
                        </div>
                        
                        <div class="form-group">
                            <label class="required">경도</label>
                            <input type="number" step="any" name="longitude" class="form-input" required placeholder="예: 126.3922">
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
                        
                        <!-- VR & 유튜브 -->
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
                        
                        <!-- 이미지 업로드 -->
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
        let assets = [];
        let uploadedImages = [];
        let editingAssetId = null;
        
        // 페이지 로드
        document.addEventListener('DOMContentLoaded', function() {
            loadAssets();
            setupImageUpload();
        });
        
        // 재산 목록 로드
        async function loadAssets() {
            try {
                const response = await fetch('../api/assets.php?limit=1000');
                const data = await response.json();
                
                if (data.success && data.data.assets) {
                    assets = data.data.assets;
                    renderAssetsTable();
                } else {
                    showError('데이터를 불러오는데 실패했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('서버와 통신 중 오류가 발생했습니다.');
            }
        }
        
        // 테이블 렌더링
        function renderAssetsTable() {
            const container = document.getElementById('assetsTableContainer');
            
            if (assets.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <p>등록된 재산이 없습니다.</p>
                    </div>
                `;
                return;
            }
            
            let html = `
                <table class="assets-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>재산명</th>
                            <th>카테고리</th>
                            <th>💰 금액</th>
                            <th>📸 이미지</th>
                            <th>상태</th>
                            <th>📅 등록일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            assets.forEach(asset => {
                const price = asset.price ? formatPrice(asset.price) : '-';
                const imageCount = asset.images ? asset.images.length : 0;
                const createdAt = new Date(asset.created_at).toLocaleDateString('ko-KR');
                const statusClass = asset.status === '정상' ? 'badge-normal' : 
                                   asset.status === '점검중' ? 'badge-maintenance' : 'badge-disabled';
                
                html += `
                    <tr>
                        <td>${asset.id}</td>
                        <td><strong>${asset.name}</strong></td>
                        <td>${asset.category}</td>
                        <td><strong>${price}</strong></td>
                        <td>${imageCount}장</td>
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
            const asset = assets.find(a => a.id === id);
            if (!asset) return;
            
            editingAssetId = id;
            document.getElementById('modalTitle').textContent = '재산 수정';
            
            // 폼 채우기
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
            
            // 이미지 로드
            uploadedImages = asset.images || [];
            renderImagePreviews();
            
            document.getElementById('assetModal').classList.add('active');
        }
        
        // 모달 닫기
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
            
            if (editingAssetId) {
                data.id = editingAssetId;
            }
            
            try {
                const response = await fetch('../api/manage_asset.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.data.message);
                    closeModal();
                    loadAssets();
                } else {
                    alert('오류: ' + result.message);
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
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'delete',
                        id: id
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.data.message);
                    loadAssets();
                } else {
                    alert('오류: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('삭제 중 오류가 발생했습니다.');
            }
        }
        
        // 이미지 업로드 설정
        function setupImageUpload() {
            const uploadArea = document.getElementById('imageUploadArea');
            const fileInput = document.getElementById('imageInput');
            
            // 클릭 업로드
            uploadArea.addEventListener('click', () => {
                if (uploadedImages.length >= 5) {
                    alert('최대 5장까지만 업로드할 수 있습니다.');
                    return;
                }
                fileInput.click();
            });
            
            // 파일 선택
            fileInput.addEventListener('change', handleFileSelect);
            
            // 드래그 & 드롭
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                if (uploadedImages.length >= 5) {
                    alert('최대 5장까지만 업로드할 수 있습니다.');
                    return;
                }
                
                const files = Array.from(e.dataTransfer.files);
                uploadFiles(files);
            });
        }
        
        // 파일 선택 처리
        function handleFileSelect(e) {
            const files = Array.from(e.target.files);
            uploadFiles(files);
            e.target.value = ''; // 리셋
        }
        
        // 파일 업로드
        async function uploadFiles(files) {
            for (const file of files) {
                if (uploadedImages.length >= 5) {
                    alert('최대 5장까지만 업로드할 수 있습니다.');
                    break;
                }
                
                if (!file.type.startsWith('image/')) {
                    alert('이미지 파일만 업로드할 수 있습니다.');
                    continue;
                }
                
                if (file.size > 5 * 1024 * 1024) {
                    alert('파일 크기는 5MB 이하여야 합니다.');
                    continue;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    
                    const response = await fetch('../api/upload_image.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        uploadedImages.push(result.data.url);
                        renderImagePreviews();
                    } else {
                        alert('업로드 실패: ' + result.message);
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('업로드 중 오류가 발생했습니다.');
                }
            }
        }
        
        // 이미지 미리보기 렌더링
        function renderImagePreviews() {
            const grid = document.getElementById('imagePreviewGrid');
            
            if (uploadedImages.length === 0) {
                grid.innerHTML = '';
                return;
            }
            
            let html = '';
            uploadedImages.forEach((url, index) => {
                html += `
                    <div class="image-preview-item" draggable="true" 
                         ondragstart="handleDragStart(event, ${index})" 
                         ondragover="handleDragOver(event)" 
                         ondrop="handleDrop(event, ${index})">
                        <img src="${url}" alt="Image ${index + 1}">
                        ${index === 0 ? '<div class="primary-badge">대표</div>' : ''}
                        <button type="button" class="remove-btn" onclick="removeImage(${index})">&times;</button>
                    </div>
                `;
            });
            
            grid.innerHTML = html;
        }
        
        // 이미지 삭제
        function removeImage(index) {
            uploadedImages.splice(index, 1);
            renderImagePreviews();
        }
        
        // 드래그 앤 드롭 순서 변경
        let draggedIndex = null;
        
        function handleDragStart(e, index) {
            draggedIndex = index;
            e.dataTransfer.effectAllowed = 'move';
        }
        
        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }
        
        function handleDrop(e, dropIndex) {
            e.preventDefault();
            
            if (draggedIndex === null || draggedIndex === dropIndex) return;
            
            const draggedImage = uploadedImages[draggedIndex];
            uploadedImages.splice(draggedIndex, 1);
            uploadedImages.splice(dropIndex, 0, draggedImage);
            
            draggedIndex = null;
            renderImagePreviews();
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
        
        // 에러 표시
        function showError(message) {
            const container = document.getElementById('assetsTableContainer');
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <p>${message}</p>
                </div>
            `;
        }
    </script>
</body>
</html>
