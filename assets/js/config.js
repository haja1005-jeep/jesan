// ================================================
// 공유재산 관리 플랫폼 - 설정 파일
// ================================================

const CONFIG = {
    // API 엔드포인트
    API_BASE_URL: '/jesan/api',
    
    // 카카오맵 설정
    KAKAO_MAP: {
        DEFAULT_CENTER: {
            lat: 37.5665, // 서울시청 기본 좌표
            lng: 126.9780
        },
        DEFAULT_LEVEL: 5
    },
    
    // 마커 아이콘 설정
    MARKER_ICONS: {
        '시설': '🏛️',
        '공원': '🌳',
        '건물': '🏗️',
        '토지': '🗺️',
        '장비': '⚙️',
        'default': '📍'
    },
    
    // 상태 색상
    STATUS_COLORS: {
        '정상': '#10b981',
        '점검중': '#f59e0b',
        '사용불가': '#ef4444'
    },
    
    // 페이지네이션
    ITEMS_PER_PAGE: 20,
    
    // 디바운스 시간 (ms)
    DEBOUNCE_DELAY: 300
};

// 전역 상태 관리
const AppState = {
    map: null,
    markers: [],
    clusterer: null,
    assets: [],
    filteredAssets: [],
    filters: {
        search: '',
        region: '',
        category: '',
        status: ['정상', '점검중']
    },
    selectedAsset: null
};
