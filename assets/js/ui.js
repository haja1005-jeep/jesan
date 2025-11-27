// ================================================
// UI 컨트롤 및 인터랙션 관리
// ================================================

const UI = {
    /**
     * 초기화
     */
    init() {
        this.bindEvents();
        console.log('✅ UI 초기화 완료');
    },
    
    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        // 검색
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                AppState.filters.search = e.target.value;
                this.applyFilters();
            }, CONFIG.DEBOUNCE_DELAY));
        }
        
        // 지역 필터
        const regionFilter = document.getElementById('regionFilter');
        if (regionFilter) {
            regionFilter.addEventListener('change', (e) => {
                AppState.filters.region = e.target.value;
                this.applyFilters();
            });
        }
        
        // 카테고리 버튼
        const categoryBtns = document.querySelectorAll('.category-btn');
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                AppState.filters.category = btn.dataset.category || '';
                this.applyFilters();
            });
        });
        
        // 상태 체크박스
        const statusNormal = document.getElementById('statusNormal');
        const statusMaintenance = document.getElementById('statusMaintenance');
        const statusUnavailable = document.getElementById('statusUnavailable');
        
        [statusNormal, statusMaintenance, statusUnavailable].forEach(checkbox => {
            if (checkbox) {
                checkbox.addEventListener('change', () => {
                    this.updateStatusFilter();
                });
            }
        });
        
        // 초기화 버튼
        const btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', () => {
                this.resetFilters();
            });
        }
        
        console.log('✅ UI 이벤트 바인딩 완료');
    },
    
    /**
     * 상태 필터 업데이트
     */
    updateStatusFilter() {
        const statuses = [];
        
        if (document.getElementById('statusNormal')?.checked) {
            statuses.push('정상');
        }
        if (document.getElementById('statusMaintenance')?.checked) {
            statuses.push('점검중');
        }
        if (document.getElementById('statusUnavailable')?.checked) {
            statuses.push('사용불가');
        }
        
        AppState.filters.status = statuses;
        this.applyFilters();
    },
    
    /**
     * 필터 적용
     */
    applyFilters() {
        let filtered = AppState.assets;
        
        // 검색어 필터
        if (AppState.filters.search) {
            const search = AppState.filters.search.toLowerCase();
            filtered = filtered.filter(asset => 
                asset.name.toLowerCase().includes(search) ||
                (asset.address && asset.address.toLowerCase().includes(search))
            );
        }
        
        // 지역 필터
        if (AppState.filters.region) {
            filtered = filtered.filter(asset =>
                asset.dong && asset.dong.includes(AppState.filters.region)
            );
        }
        
        // 카테고리 필터
        if (AppState.filters.category) {
            filtered = filtered.filter(asset =>
                asset.category === AppState.filters.category
            );
        }
        
        // 상태 필터
        if (AppState.filters.status.length > 0) {
            filtered = filtered.filter(asset =>
                AppState.filters.status.includes(asset.status)
            );
        }
        
        AppState.filteredAssets = filtered;
        
        // UI 업데이트
        this.updateResultCount();
        this.renderList();
        MapManager.displayMarkers(filtered);
        
        // 모바일 목록 업데이트
        if (window.MobileUI && typeof window.MobileUI.updateMobileAssetList === 'function') {
            window.MobileUI.updateMobileAssetList(filtered);
        }
    },
    
    /**
     * 필터 초기화
     */
    resetFilters() {
        // 검색어 초기화
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // 지역 초기화
        const regionFilter = document.getElementById('regionFilter');
        if (regionFilter) {
            regionFilter.value = '';
        }
        
        // 카테고리 초기화
        const categoryBtns = document.querySelectorAll('.category-btn');
        categoryBtns.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.category === '') {
                btn.classList.add('active');
            }
        });
        
        // 상태 초기화
        const statusNormal = document.getElementById('statusNormal');
        const statusMaintenance = document.getElementById('statusMaintenance');
        const statusUnavailable = document.getElementById('statusUnavailable');
        
        if (statusNormal) statusNormal.checked = true;
        if (statusMaintenance) statusMaintenance.checked = true;
        if (statusUnavailable) statusUnavailable.checked = false;
        
        // 필터 상태 초기화
        AppState.filters = {
            search: '',
            region: '',
            category: '',
            status: ['정상', '점검중']
        };
        
        this.applyFilters();
    },
    
    /**
     * 결과 개수 업데이트
     */
    updateResultCount() {
        const countEl = document.getElementById('assetCount');
        if (countEl) {
            countEl.textContent = `(${AppState.filteredAssets.length})`;
        }
    },
    
    /**
     * 목록 렌더링
     */
    renderList() {
        // 모바일 목록 업데이트
        if (window.MobileUI && typeof window.MobileUI.updateMobileAssetList === 'function') {
            window.MobileUI.updateMobileAssetList(AppState.filteredAssets);
        }
    },
    
    /**
     * 로딩 표시
     */
    showLoading() {
        const loadingEl = document.getElementById('loading');
        if (loadingEl) {
            loadingEl.style.display = 'flex';
        }
    },
    
    /**
     * 로딩 숨김
     */
    hideLoading() {
        const loadingEl = document.getElementById('loading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    },
    
    /**
     * 재산 상세 정보 표시
     */
    showAssetDetail(assetId) {
        const asset = AppState.assets.find(a => a.id === assetId);
        if (!asset) {
            console.error('재산을 찾을 수 없습니다:', assetId);
            return;
        }
        
        // 모바일에서는 하단 카드 표시
        if (window.innerWidth <= 768 && window.MobileUI) {
            window.MobileUI.openAssetDetailCard(asset);
            return;
        }
        
        // 데스크톱에서는 모달 표시
        this.showAssetModal(asset);
    },
    
    /**
     * 재산 상세 모달 표시 (데스크톱)
     */
    showAssetModal(asset) {
        // 모달이 없으면 생성
        let modal = document.getElementById('assetModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'assetModal';
            modal.className = 'asset-modal';
            modal.innerHTML = `
                <div class="modal-overlay" onclick="UI.closeAssetModal()"></div>
                <div class="modal-content">
                    <button class="modal-close" onclick="UI.closeAssetModal()">✕</button>
                    <div id="modalBody"></div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        // 모달 내용 생성
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = this.generateAssetDetailHTML(asset);
        
        // 모달 표시
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    },
    
    /**
     * 재산 상세 모달 닫기
     */
    closeAssetModal() {
        const modal = document.getElementById('assetModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    },
    
    /**
     * 재산 상세 정보 HTML 생성
     */
    generateAssetDetailHTML(asset) {
        const statusClass = asset.status === '정상' ? 'normal' : 
                           asset.status === '점검중' ? 'maintenance' : 'unavailable';
        
        // 이미지 갤러리 HTML
        let imageGalleryHTML = '';
        if (asset.images && asset.images.length > 0) {
            imageGalleryHTML = `
                <div class="asset-image-gallery">
                    <div class="main-image">
                        <img src="${asset.images[0]}" alt="${asset.name}" onclick="UI.showImageLightbox('${asset.images[0]}')">
                    </div>
                    ${asset.images.length > 1 ? `
                    <div class="thumbnail-list">
                        ${asset.images.map((img, index) => `
                            <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="UI.changeMainImage(this, '${img}')">
                                <img src="${img}" alt="${asset.name} ${index + 1}">
                            </div>
                        `).join('')}
                    </div>
                    ` : ''}
                </div>
            `;
        }
        
        return `
            ${imageGalleryHTML}
            
            <div class="asset-detail-header">
                <div class="asset-detail-title">
                    <span class="asset-icon">${CONFIG.MARKER_ICONS[asset.category] || CONFIG.MARKER_ICONS.default}</span>
                    <h2>${asset.name}</h2>
                </div>
                <span class="status-badge status-${statusClass}">${asset.status}</span>
            </div>
            
            <div class="asset-detail-body">
                <div class="detail-section">
                    <h3>기본 정보</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">카테고리</span>
                            <span class="detail-value">${asset.category}${asset.sub_category ? ' · ' + asset.sub_category : ''}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">주소</span>
                            <span class="detail-value">${asset.address || '-'}</span>
                        </div>
                        ${asset.area ? `
                        <div class="detail-item">
                            <span class="detail-label">면적</span>
                            <span class="detail-value">${this.formatNumber(asset.area)}㎡</span>
                        </div>
                        ` : ''}
                        ${asset.capacity ? `
                        <div class="detail-item">
                            <span class="detail-label">수용인원</span>
                            <span class="detail-value">${asset.capacity}명</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                ${asset.description ? `
                <div class="detail-section">
                    <h3>설명</h3>
                    <p class="detail-description">${asset.description}</p>
                </div>
                ` : ''}
                
                <div class="detail-section">
                    <h3>관리 정보</h3>
                    <div class="detail-grid">
                        ${asset.manager ? `
                        <div class="detail-item">
                            <span class="detail-label">관리부서</span>
                            <span class="detail-value">${asset.manager}</span>
                        </div>
                        ` : ''}
                        ${asset.contact ? `
                        <div class="detail-item">
                            <span class="detail-label">연락처</span>
                            <span class="detail-value">${asset.contact}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <div class="asset-detail-footer">
                <button class="btn btn-secondary" onclick="MapManager.focusAsset(${asset.id})">지도에서 보기</button>
                <button class="btn btn-primary" onclick="alert('예약 기능은 개발 중입니다')">예약하기</button>
            </div>
        `;
    },
    
    /**
     * 메인 이미지 변경
     */
    changeMainImage(thumbnail, imageSrc) {
        const mainImage = document.querySelector('.main-image img');
        if (mainImage) {
            mainImage.src = imageSrc;
        }
        
        // 썸네일 활성화 상태 변경
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
    },
    
    /**
     * 이미지 라이트박스 표시
     */
    showImageLightbox(imageSrc) {
        // 라이트박스가 없으면 생성
        let lightbox = document.getElementById('imageLightbox');
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.id = 'imageLightbox';
            lightbox.className = 'image-lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-overlay" onclick="UI.closeImageLightbox()"></div>
                <div class="lightbox-content">
                    <button class="lightbox-close" onclick="UI.closeImageLightbox()">✕</button>
                    <img src="" alt="확대 이미지">
                </div>
            `;
            document.body.appendChild(lightbox);
        }
        
        // 이미지 설정 및 표시
        const img = lightbox.querySelector('img');
        img.src = imageSrc;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    },
    
    /**
     * 이미지 라이트박스 닫기
     */
    closeImageLightbox() {
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox) {
            lightbox.style.display = 'none';
            document.body.style.overflow = '';
        }
    },
    
    /**
     * 숫자 포맷팅
     */
    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
};

/**
 * 디바운스 함수
 */
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
