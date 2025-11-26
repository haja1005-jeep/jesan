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
                asset.address && asset.address.includes(AppState.filters.region)
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
