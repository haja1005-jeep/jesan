// ================================================
// 카카오맵 관련 기능
// ================================================

const MapManager = {
    /**
     * 지도 초기화
     */
    init() {
        const container = document.getElementById('map');
        const options = {
            center: new kakao.maps.LatLng(
                CONFIG.KAKAO_MAP.DEFAULT_CENTER.lat,
                CONFIG.KAKAO_MAP.DEFAULT_CENTER.lng
            ),
            level: CONFIG.KAKAO_MAP.DEFAULT_LEVEL
        };
        
        AppState.map = new kakao.maps.Map(container, options);
        
        // 클러스터러 초기화
        AppState.clusterer = new kakao.maps.MarkerClusterer({
            map: AppState.map,
            averageCenter: true,
            minLevel: 7,
            disableClickZoom: true,
            styles: [{
                width: '50px',
                height: '50px',
                background: 'rgba(37, 99, 235, 0.8)',
                borderRadius: '25px',
                color: '#fff',
                textAlign: 'center',
                fontWeight: 'bold',
                lineHeight: '50px'
            }]
        });
        
        // 지도 클릭 이벤트
        kakao.maps.event.addListener(AppState.map, 'click', () => {
            this.closeAllInfoWindows();
        });
        
        console.log('지도 초기화 완료');
    },
    
    /**
     * 마커 생성
     */
    createMarker(asset) {
        const position = new kakao.maps.LatLng(asset.latitude, asset.longitude);
        
        // 커스텀 오버레이 컨텐츠
        const content = `
            <div class="custom-marker" data-id="${asset.id}">
                <div class="marker-icon">${CONFIG.MARKER_ICONS[asset.category] || CONFIG.MARKER_ICONS.default}</div>
                <div class="marker-label">${asset.name}</div>
            </div>
        `;
        
        const customOverlay = new kakao.maps.CustomOverlay({
            position: position,
            content: content,
            yAnchor: 1
        });
        
        // 마커 클릭 이벤트를 위한 일반 마커도 생성 (투명)
        const marker = new kakao.maps.Marker({
            position: position,
            clickable: true
        });
        
        // 클릭 이벤트
        kakao.maps.event.addListener(marker, 'click', () => {
            this.showInfoWindow(asset, marker);
            AppState.selectedAsset = asset;
        });
        
        return {
            marker: marker,
            overlay: customOverlay,
            asset: asset
        };
    },
    
    /**
     * 모든 마커 표시
     */
    displayMarkers(assets) {
        // 기존 마커 제거
        this.clearMarkers();
        
        // 새 마커 생성
        assets.forEach(asset => {
            const markerData = this.createMarker(asset);
            AppState.markers.push(markerData);
            markerData.overlay.setMap(AppState.map);
        });
        
        // 클러스터러에 마커 추가
        const markers = AppState.markers.map(m => m.marker);
        AppState.clusterer.addMarkers(markers);
        
        // 마커들이 모두 보이도록 지도 범위 조정
        if (assets.length > 0) {
            const bounds = new kakao.maps.LatLngBounds();
            assets.forEach(asset => {
                bounds.extend(new kakao.maps.LatLng(asset.latitude, asset.longitude));
            });
            AppState.map.setBounds(bounds);
        }
    },
    
    /**
     * 마커 제거
     */
    clearMarkers() {
        AppState.markers.forEach(markerData => {
            markerData.overlay.setMap(null);
        });
        AppState.clusterer.clear();
        AppState.markers = [];
    },
    
    /**
     * 인포윈도우 표시
     */
    showInfoWindow(asset, marker) {
        this.closeAllInfoWindows();
        
        const statusClass = asset.status === '정상' ? 'normal' : 
                           asset.status === '점검중' ? 'maintenance' : 'unavailable';
        
        const content = `
            <div class="info-window">
                <div class="info-header">
                    <h3>${asset.name}</h3>
                    <span class="info-status ${statusClass}">${asset.status}</span>
                </div>
                <div class="info-body">
                    <p class="info-category">${asset.category} · ${asset.sub_category || ''}</p>
                    <p class="info-address">${asset.address}</p>
                    ${asset.area ? `<p class="info-area">면적: ${formatNumber(asset.area)}㎡</p>` : ''}
                    ${asset.capacity ? `<p class="info-capacity">수용인원: ${asset.capacity}명</p>` : ''}
                </div>
                <button class="info-btn" onclick="UI.showAssetDetail(${asset.id})">
                    상세보기
                </button>
            </div>
        `;
        
        const infowindow = new kakao.maps.InfoWindow({
            content: content,
            removable: false
        });
        
        infowindow.open(AppState.map, marker);
        AppState.currentInfoWindow = infowindow;
    },
    
    /**
     * 모든 인포윈도우 닫기
     */
    closeAllInfoWindows() {
        if (AppState.currentInfoWindow) {
            AppState.currentInfoWindow.close();
            AppState.currentInfoWindow = null;
        }
    },
    
    /**
     * 특정 위치로 이동
     */
    moveTo(lat, lng, level = 3) {
        const moveLatLon = new kakao.maps.LatLng(lat, lng);
        AppState.map.setCenter(moveLatLon);
        AppState.map.setLevel(level);
    },
    
    /**
     * 현재 위치로 이동
     */
    moveToCurrentLocation() {
        if (navigator.geolocation) {
            UI.showLoading();
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    this.moveTo(lat, lng, 5);
                    UI.hideLoading();
                },
                (error) => {
                    console.error('위치 정보를 가져올 수 없습니다:', error);
                    alert('위치 정보를 가져올 수 없습니다.');
                    UI.hideLoading();
                }
            );
        } else {
            alert('이 브라우저에서는 위치 정보를 지원하지 않습니다.');
        }
    },
    
    /**
     * 특정 재산에 지도 포커스
     */
    focusAsset(assetId) {
        const asset = AppState.assets.find(a => a.id === assetId);
        if (!asset) {
            console.error('재산을 찾을 수 없습니다:', assetId);
            return;
        }
        
        // 지도 중심 이동 및 확대
        const position = new kakao.maps.LatLng(asset.latitude, asset.longitude);
        AppState.map.setCenter(position);
        AppState.map.setLevel(3);
        
        // 모달 닫기
        if (window.UI && typeof window.UI.closeAssetModal === 'function') {
            window.UI.closeAssetModal();
        }
        
        // 정보창 표시
        const markerData = AppState.markers.find(m => m.asset.id === assetId);
        if (markerData) {
            this.showInfoWindow(asset, markerData.marker);
        }
        
        console.log('✅ 재산으로 포커스:', asset.name);
    },
    
    /**
     * 모든 인포윈도우 닫기
     */
    closeAllInfoWindows() {
        if (AppState.currentInfoWindow) {
            AppState.currentInfoWindow.close();
            AppState.currentInfoWindow = null;
        }
    }
};

// CSS 스타일 추가 (인포윈도우 및 커스텀 마커용)
const mapStyles = document.createElement('style');
mapStyles.textContent = `
    .custom-marker {
        background: white;
        padding: 8px 12px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
        font-weight: 500;
    }
    
    .custom-marker:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .marker-icon {
        font-size: 18px;
    }
    
    .marker-label {
        white-space: nowrap;
    }
    
    .info-window {
        padding: 16px;
        min-width: 250px;
    }
    
    .info-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 12px;
    }
    
    .info-header h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        color: #111827;
    }
    
    .info-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .info-status.normal {
        background: #d1fae5;
        color: #065f46;
    }
    
    .info-status.maintenance {
        background: #fef3c7;
        color: #92400e;
    }
    
    .info-status.unavailable {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .info-body {
        margin-bottom: 12px;
    }
    
    .info-body p {
        margin: 4px 0;
        font-size: 13px;
        color: #6b7280;
    }
    
    .info-category {
        font-weight: 500;
        color: #374151 !important;
    }
    
    .info-btn {
        width: 100%;
        padding: 8px;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .info-btn:hover {
        background: #1e40af;
    }
`;
document.head.appendChild(mapStyles);
