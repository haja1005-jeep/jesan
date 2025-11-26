// ================================================
// 메인 애플리케이션 실행
// ================================================

/**
 * 앱 초기화
 */
async function initApp() {
    console.log('앱 초기화 시작...');
    
    try {
        // 1. UI 초기화
        UI.init();
        
        // 2. 카카오맵 초기화
        MapManager.init();
        
        // 3. 데이터 로드
        await loadAssets();
        
        console.log('앱 초기화 완료!');
    } catch (error) {
        console.error('앱 초기화 오류:', error);
        alert('앱을 초기화하는 중 오류가 발생했습니다.');
    }
}

/**
 * 재산 데이터 로드
 */
async function loadAssets() {
    UI.showLoading();
    
    try {
        const data = await API.getAssets();
        
        // 응답 구조 확인
        if (data.success && Array.isArray(data.assets)) {
            AppState.assets = data.assets;
            AppState.filteredAssets = data.assets;
        } else if (Array.isArray(data)) {
            // 배열로 직접 반환되는 경우
            AppState.assets = data;
            AppState.filteredAssets = data;
        } else {
            console.warn('예상치 못한 데이터 형식:', data);
            AppState.assets = [];
            AppState.filteredAssets = [];
        }
        
        // UI 업데이트
        UI.updateResultCount();
        UI.renderList();
        MapManager.displayMarkers(AppState.filteredAssets);
        
        console.log(`${AppState.assets.length}개의 재산 데이터 로드 완료`);
    } catch (error) {
        console.error('재산 데이터 로드 오류:', error);
        
        // 개발 중에는 샘플 데이터 사용
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.warn('샘플 데이터를 사용합니다.');
            loadSampleData();
        } else {
            alert('데이터를 불러오는 중 오류가 발생했습니다.');
        }
    } finally {
        UI.hideLoading();
    }
}

/**
 * 샘플 데이터 로드 (개발용)
 */
function loadSampleData() {
    const sampleAssets = [
        {
            id: 1,
            name: '강남구민회관',
            category: '시설',
            sub_category: '문화시설',
            latitude: 37.5172,
            longitude: 127.0473,
            address: '서울특별시 강남구 학동로 426',
            area: 5000,
            capacity: 500,
            status: '정상',
            description: '다양한 문화 행사와 공연이 열리는 구민회관입니다.',
            manager: '강남구청 문화체육과',
            contact: '02-3423-5000'
        },
        {
            id: 2,
            name: '역삼근린공원',
            category: '공원',
            sub_category: '근린공원',
            latitude: 37.5010,
            longitude: 127.0374,
            address: '서울특별시 강남구 역삼동 736',
            area: 12000,
            status: '정상',
            description: '주민들의 휴식 공간으로 활용되는 공원입니다.',
            manager: '강남구청 공원녹지과'
        },
        {
            id: 3,
            name: '강남구청 주민회의실',
            category: '시설',
            sub_category: '회의실',
            latitude: 37.5172,
            longitude: 127.0473,
            address: '서울특별시 강남구 학동로 426',
            capacity: 30,
            status: '정상',
            description: '주민들이 예약하여 사용할 수 있는 회의실입니다.',
            manager: '강남구청 민원봉사과'
        },
        {
            id: 4,
            name: '선릉역 공영주차장',
            category: '시설',
            sub_category: '주차장',
            latitude: 37.5045,
            longitude: 127.0490,
            address: '서울특별시 강남구 선릉로 428',
            capacity: 150,
            status: '점검중',
            description: '24시간 운영되는 공영주차장입니다.',
            manager: '강남구청 주차관리과'
        },
        {
            id: 5,
            name: '대치도서관',
            category: '건물',
            sub_category: '도서관',
            latitude: 37.4957,
            longitude: 127.0619,
            address: '서울특별시 강남구 도곡로 541',
            area: 3500,
            capacity: 200,
            status: '정상',
            description: '지역 주민을 위한 공공도서관입니다.',
            manager: '강남구청 교육지원과'
        }
    ];
    
    AppState.assets = sampleAssets;
    AppState.filteredAssets = sampleAssets;
    
    UI.updateResultCount();
    UI.renderList();
    MapManager.displayMarkers(AppState.filteredAssets);
    
    console.log('샘플 데이터 로드 완료');
}

/**
 * 페이지 로드 시 앱 초기화
 */
window.addEventListener('load', () => {
    // 카카오맵 API 확인
    if (typeof kakao === 'undefined' || !kakao.maps) {
        console.error('카카오맵 API가 로드되지 않았습니다.');
        alert('카카오맵 API 키가 설정되지 않았습니다.\n\nindex.html 파일에서 다음 부분을 수정해주세요:\n\n<script src="//dapi.kakao.com/v2/maps/sdk.js?appkey=실제_API_키&libraries=clusterer"></script>\n\n카카오 개발자 사이트(https://developers.kakao.com)에서 API 키를 발급받을 수 있습니다.');
        return;
    }
    
    // 카카오맵 API가 로드될 때까지 대기
    kakao.maps.load(() => {
        initApp();
    });
});

/**
 * 페이지 언로드 시 정리
 */
window.addEventListener('beforeunload', () => {
    MapManager.clearMarkers();
});
