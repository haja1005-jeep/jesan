/**
 * 모바일 UI 제어
 */

(function() {
    'use strict';

    // DOM 요소
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const filterPanel = document.getElementById('filterPanel');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const btnCloseMenu = document.getElementById('btnCloseMenu');
    const floatingFilterBtn = document.getElementById('floatingFilterBtn');
    const btnApplyFilter = document.getElementById('btnApplyFilter');
    const assetDetailCard = document.getElementById('assetDetailCard');
    const btnCloseCard = document.getElementById('btnCloseCard');

    // 모바일 여부 확인
    const isMobile = () => window.innerWidth <= 768;

    /**
     * 사이드바 메뉴 열기
     */
    function openSidebar() {
        if (!isMobile()) return;
        
        filterPanel.classList.add('active');
        mobileOverlay.classList.add('active');
        hamburgerBtn.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * 사이드바 메뉴 닫기
     */
    function closeSidebar() {
        filterPanel.classList.remove('active');
        mobileOverlay.classList.remove('active');
        hamburgerBtn.classList.remove('active');
        document.body.style.overflow = '';
    }

    /**
     * 사이드바 토글
     */
    function toggleSidebar() {
        if (filterPanel.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    /**
     * 재산 상세 카드 열기
     */
    function openAssetDetailCard(assetData) {
        if (!isMobile()) return;
        
        // 카드 내용 업데이트
        const content = document.getElementById('assetDetailContent');
        if (content && assetData) {
            content.innerHTML = generateAssetDetailHTML(assetData);
        }
        
        assetDetailCard.classList.add('active');
        assetDetailCard.style.display = 'block';
    }

    /**
     * 재산 상세 카드 닫기
     */
    function closeAssetDetailCard() {
        assetDetailCard.classList.remove('active');
        setTimeout(() => {
            assetDetailCard.style.display = 'none';
        }, 300);
    }

    /**
     * 재산 상세 정보 HTML 생성
     */
    function generateAssetDetailHTML(asset) {
        return `
            <div class="asset-detail-content">
                <div class="asset-header">
                    <h3>${asset.name}</h3>
                    <span class="status-badge status-${asset.status}">${asset.status}</span>
                </div>
                
                <div class="asset-info">
                    <div class="info-item">
                        <span class="info-label">카테고리</span>
                        <span class="info-value">${asset.category}</span>
                    </div>
                    
                    ${asset.address ? `
                    <div class="info-item">
                        <span class="info-label">주소</span>
                        <span class="info-value">${asset.address}</span>
                    </div>
                    ` : ''}
                    
                    ${asset.area ? `
                    <div class="info-item">
                        <span class="info-label">면적</span>
                        <span class="info-value">${asset.area}㎡</span>
                    </div>
                    ` : ''}
                    
                    ${asset.capacity ? `
                    <div class="info-item">
                        <span class="info-label">수용인원</span>
                        <span class="info-value">${asset.capacity}명</span>
                    </div>
                    ` : ''}
                    
                    ${asset.manager ? `
                    <div class="info-item">
                        <span class="info-label">관리부서</span>
                        <span class="info-value">${asset.manager}</span>
                    </div>
                    ` : ''}
                    
                    ${asset.contact ? `
                    <div class="info-item">
                        <span class="info-label">연락처</span>
                        <span class="info-value">${asset.contact}</span>
                    </div>
                    ` : ''}
                </div>
                
                ${asset.description ? `
                <div class="asset-description">
                    <h4>설명</h4>
                    <p>${asset.description}</p>
                </div>
                ` : ''}
                
                <div class="asset-actions">
                    <button class="btn-primary" onclick="bookAsset(${asset.id})">
                        예약하기
                    </button>
                    <button class="btn-secondary" onclick="showOnMap(${asset.id})">
                        지도에서 보기
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * 스와이프 제스처 감지
     */
    let touchStartX = 0;
    let touchEndX = 0;

    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchEndX - touchStartX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff < 0 && filterPanel.classList.contains('active')) {
                // 왼쪽으로 스와이프 - 메뉴 닫기
                closeSidebar();
            }
        }
    }

    // 이벤트 리스너 등록
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', toggleSidebar);
    }

    if (btnCloseMenu) {
        btnCloseMenu.addEventListener('click', closeSidebar);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeSidebar);
    }

    if (floatingFilterBtn) {
        floatingFilterBtn.addEventListener('click', openSidebar);
    }

    if (btnApplyFilter) {
        btnApplyFilter.addEventListener('click', () => {
            closeSidebar();
            // 필터 적용 로직은 main.js에서 처리
        });
    }

    if (btnCloseCard) {
        btnCloseCard.addEventListener('click', closeAssetDetailCard);
    }

    // 스와이프 제스처
    if (filterPanel) {
        filterPanel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        filterPanel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }

    // 화면 크기 변경 시 처리
    window.addEventListener('resize', () => {
        if (!isMobile() && filterPanel.classList.contains('active')) {
            closeSidebar();
        }
    });

    // ESC 키로 메뉴 닫기
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (filterPanel.classList.contains('active')) {
                closeSidebar();
            }
            if (assetDetailCard.classList.contains('active')) {
                closeAssetDetailCard();
            }
        }
    });

    /**
     * 모바일 재산 목록 업데이트
     */
    function updateMobileAssetList(assets) {
        const container = document.getElementById('assetListContainer');
        const countEl = document.getElementById('assetCount');
        
        if (!container || !isMobile()) return;

        countEl.textContent = `(${assets.length})`;

        if (assets.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #718096; padding: 20px;">검색 결과가 없습니다.</p>';
            return;
        }

        container.innerHTML = assets.map(asset => `
            <div class="asset-list-item" onclick="selectAssetFromList(${asset.id})">
                <h4>${asset.category} ${asset.name}</h4>
                <p>${asset.address || '주소 정보 없음'}</p>
                <span class="status-badge status-${asset.status}" style="font-size: 11px; margin-top: 4px;">${asset.status}</span>
            </div>
        `).join('');
    }

    /**
     * 목록에서 재산 선택
     */
    window.selectAssetFromList = function(assetId) {
        closeSidebar();
        // 지도에서 해당 마커로 이동하고 정보창 표시
        if (window.AppState && window.AppState.assets) {
            const asset = window.AppState.assets.find(a => a.id === assetId);
            if (asset) {
                // 지도 중심 이동
                if (window.AppState.map) {
                    const position = new kakao.maps.LatLng(asset.latitude, asset.longitude);
                    window.AppState.map.setCenter(position);
                    window.AppState.map.setLevel(3);
                }
                
                // 상세 정보 표시
                setTimeout(() => {
                    openAssetDetailCard(asset);
                }, 300);
            }
        }
    };

    /**
     * 예약하기
     */
    window.bookAsset = function(assetId) {
        alert('예약 기능은 로그인 후 이용 가능합니다.');
        // 실제 구현 시 예약 페이지로 이동 또는 모달 표시
    };

    /**
     * 지도에서 보기
     */
    window.showOnMap = function(assetId) {
        closeAssetDetailCard();
        // 지도에서 해당 위치로 이동
        if (window.AppState && window.AppState.assets) {
            const asset = window.AppState.assets.find(a => a.id === assetId);
            if (asset && window.AppState.map) {
                const position = new kakao.maps.LatLng(asset.latitude, asset.longitude);
                window.AppState.map.setCenter(position);
                window.AppState.map.setLevel(3);
            }
        }
    };

    // 전역 함수로 export
    window.MobileUI = {
        openSidebar,
        closeSidebar,
        openAssetDetailCard,
        closeAssetDetailCard,
        updateMobileAssetList
    };

    console.log('✅ Mobile UI initialized');
})();
