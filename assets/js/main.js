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
            name: '목포시청',
            category: '건물',
            sub_category: '행정시설',
            latitude: 34.8118,
            longitude: 126.3922,
            address: '전라남도 목포시 번화로 15',
            area: 8500,
            capacity: 300,
            status: '정상',
            price: 125000000000, // 1,250억원
            description: '목포시 행정 업무를 총괄하는 시청사입니다.',
            manager: '목포시청 총무과',
            contact: '061-270-2000',
            images: [
                'https://www.mokpo.go.kr/images/www/content/intro/cityhall_01.jpg'
            ]
        },
        {
            id: 2,
            name: '목포문화예술회관',
            category: '시설',
            sub_category: '문화시설',
            latitude: 34.7868,
            longitude: 126.3850,
            address: '전라남도 목포시 남농로 152',
            area: 12000,
            capacity: 1200,
            status: '정상',
            price: 85000000000, // 850억원
            description: '다양한 공연과 문화행사가 열리는 복합문화공간입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-270-8501',
            images: [
                'https://via.placeholder.com/800x600/667eea/ffffff?text=목포문화예술회관',
                'https://via.placeholder.com/800x600/764ba2/ffffff?text=대공연장'
            ]
        },
        {
            id: 3,
            name: '목포자연사박물관',
            category: '시설',
            sub_category: '박물관',
            latitude: 34.7751,
            longitude: 126.3901,
            address: '전라남도 목포시 남농로 135',
            area: 5600,
            capacity: 500,
            status: '정상',
            price: 35000000000, // 350억원
            description: '자연사 관련 전시와 체험 프로그램을 제공하는 박물관입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-274-3655',
            images: [
                'https://via.placeholder.com/800x600/10b981/ffffff?text=자연사박물관'
            ]
        },
        {
            id: 4,
            name: '유달산 공원',
            category: '공원',
            sub_category: '도시공원',
            latitude: 34.7780,
            longitude: 126.3822,
            address: '전라남도 목포시 유달로 187',
            area: 2340000,
            status: '정상',
            price: 120000000000, // 1,200억원
            description: '목포의 상징인 유달산 일대의 근린공원입니다.',
            manager: '목포시청 공원녹지과',
            contact: '061-270-8331',
            images: [
                'https://via.placeholder.com/800x600/059669/ffffff?text=유달산',
                'https://via.placeholder.com/800x600/047857/ffffff?text=유달산+전망대'
            ]
        },
        {
            id: 5,
            name: '갓바위문화타운',
            category: '시설',
            sub_category: '문화시설',
            latitude: 34.7691,
            longitude: 126.3815,
            address: '전라남도 목포시 갓바위로 249',
            area: 15000,
            capacity: 800,
            status: '정상',
            price: 45000000000, // 450억원
            description: '전통문화와 현대문화가 어우러진 복합문화공간입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-270-8501',
            images: [
                'https://via.placeholder.com/800x600/8b5cf6/ffffff?text=갓바위문화타운'
            ]
        },
        {
            id: 6,
            name: '평화광장',
            category: '공원',
            sub_category: '광장',
            latitude: 34.7842,
            longitude: 126.3755,
            address: '전라남도 목포시 평화로 82',
            area: 45000,
            status: '정상',
            price: 28000000000, // 280억원
            description: '각종 행사와 시민들의 휴식공간으로 활용되는 광장입니다.',
            manager: '목포시청 공원녹지과',
            contact: '061-270-8330',
            images: [
                'https://via.placeholder.com/800x600/3b82f6/ffffff?text=평화광장'
            ]
        },
        {
            id: 7,
            name: '목포시민체육관',
            category: '시설',
            sub_category: '체육시설',
            latitude: 34.8045,
            longitude: 126.3912,
            address: '전라남도 목포시 삼향천로 28',
            area: 8900,
            capacity: 3000,
            status: '정상',
            price: 65000000000, // 650억원
            description: '각종 체육행사와 생활체육 프로그램을 운영하는 체육관입니다.',
            manager: '목포시청 체육진흥과',
            contact: '061-270-8671',
            images: [
                'https://via.placeholder.com/800x600/ef4444/ffffff?text=시민체육관'
            ]
        },
        {
            id: 8,
            name: '목포실내수영장',
            category: '시설',
            sub_category: '체육시설',
            latitude: 34.8038,
            longitude: 126.3895,
            address: '전라남도 목포시 삼향천로 20',
            area: 5200,
            capacity: 200,
            status: '정상',
            price: 42000000000, // 420억원
            description: '시민들이 수영을 즐길 수 있는 실내수영장입니다.',
            manager: '목포시청 체육진흥과',
            contact: '061-270-8681',
            images: [
                'https://via.placeholder.com/800x600/06b6d4/ffffff?text=실내수영장'
            ]
        },
        {
            id: 9,
            name: '목포공공도서관',
            category: '건물',
            sub_category: '도서관',
            latitude: 34.8095,
            longitude: 126.4012,
            address: '전라남도 목포시 산정로 119',
            area: 4800,
            capacity: 350,
            status: '정상',
            price: 38000000000, // 380억원
            description: '지역주민을 위한 공공도서관입니다.',
            manager: '목포시청 도서관운영팀',
            contact: '061-270-3652',
            images: [
                'https://via.placeholder.com/800x600/f59e0b/ffffff?text=공공도서관'
            ]
        },
        {
            id: 10,
            name: '삼학도 공원',
            category: '공원',
            sub_category: '해양공원',
            latitude: 34.7643,
            longitude: 126.3698,
            address: '전라남도 목포시 삼학로 92',
            area: 180000,
            status: '정상',
            price: 95000000000, // 950억원
            description: '바다를 접한 아름다운 해양공원입니다.',
            manager: '목포시청 공원녹지과',
            contact: '061-270-8332',
            images: [
                'https://via.placeholder.com/800x600/14b8a6/ffffff?text=삼학도공원',
                'https://via.placeholder.com/800x600/0d9488/ffffff?text=삼학도+전망'
            ]
        },
        {
            id: 11,
            name: '목포청소년수련관',
            category: '시설',
            sub_category: '교육시설',
            latitude: 34.7925,
            longitude: 126.4055,
            address: '전라남도 목포시 영산로 629',
            area: 6500,
            capacity: 400,
            status: '정상',
            price: 48000000000, // 480억원
            description: '청소년들의 건전한 활동을 지원하는 수련관입니다.',
            manager: '목포시청 청소년과',
            contact: '061-270-8451',
            images: [
                'https://via.placeholder.com/800x600/ec4899/ffffff?text=청소년수련관'
            ]
        },
        {
            id: 12,
            name: '목포생활도자박물관',
            category: '시설',
            sub_category: '박물관',
            latitude: 34.7740,
            longitude: 126.3888,
            address: '전라남도 목포시 남농로 135',
            area: 3200,
            capacity: 300,
            status: '정상',
            price: 22000000000, // 220억원
            description: '생활도자기의 역사와 문화를 전시하는 박물관입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-274-7330',
            images: [
                'https://via.placeholder.com/800x600/a855f7/ffffff?text=도자박물관'
            ]
        },
        {
            id: 13,
            name: '목포근대역사관',
            category: '건물',
            sub_category: '박물관',
            latitude: 34.7768,
            longitude: 126.3889,
            address: '전라남도 목포시 영산로29번길 6',
            area: 2800,
            capacity: 200,
            status: '정상',
            price: 18000000000, // 180억원
            description: '목포의 근대역사를 한눈에 볼 수 있는 역사관입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-270-8728',
            images: [
                'https://via.placeholder.com/800x600/6366f1/ffffff?text=근대역사관'
            ]
        },
        {
            id: 14,
            name: '북항 회의실',
            category: '시설',
            sub_category: '회의실',
            latitude: 34.7855,
            longitude: 126.3812,
            address: '전라남도 목포시 해안로 249',
            capacity: 50,
            status: '정상',
            price: 5000000000, // 50억원
            description: '시민들이 예약하여 사용할 수 있는 회의실입니다.',
            manager: '목포시청 민원봉사과',
            contact: '061-270-2100',
            images: [
                'https://via.placeholder.com/800x600/64748b/ffffff?text=회의실'
            ]
        },
        {
            id: 15,
            name: '목포문화원',
            category: '시설',
            sub_category: '문화시설',
            latitude: 34.7888,
            longitude: 126.3932,
            address: '전라남도 목포시 영산로 128',
            area: 4200,
            capacity: 250,
            status: '정상',
            price: 25000000000, // 250억원
            description: '지역 문화 보존과 전승을 위한 문화원입니다.',
            manager: '목포문화원',
            contact: '061-242-1195',
            images: [
                'https://via.placeholder.com/800x600/84cc16/ffffff?text=목포문화원'
            ]
        },
        {
            id: 16,
            name: '목포진 역사공원',
            category: '공원',
            sub_category: '역사공원',
            latitude: 34.7725,
            longitude: 126.3852,
            address: '전라남도 목포시 수문로 27',
            area: 25000,
            status: '정상',
            price: 15000000000, // 150억원
            description: '목포진 성터를 보존한 역사공원입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-270-8501',
            images: [
                'https://via.placeholder.com/800x600/22c55e/ffffff?text=목포진공원'
            ]
        },
        {
            id: 17,
            name: '목포시민운동장',
            category: '시설',
            sub_category: '체육시설',
            latitude: 34.8072,
            longitude: 126.3958,
            address: '전라남도 목포시 삼향천로 91',
            area: 35000,
            capacity: 8000,
            status: '정상',
            price: 125000000000, // 1,250억원
            description: '각종 체육행사가 열리는 종합운동장입니다.',
            manager: '목포시청 체육진흥과',
            contact: '061-270-8670',
            images: [
                'https://via.placeholder.com/800x600/f97316/ffffff?text=시민운동장'
            ]
        },
        {
            id: 18,
            name: '목포어린이바다과학관',
            category: '시설',
            sub_category: '과학관',
            latitude: 34.7668,
            longitude: 126.3742,
            address: '전라남도 목포시 삼학로 92',
            area: 4500,
            capacity: 400,
            status: '정상',
            price: 32000000000, // 320억원
            description: '어린이들이 해양과학을 체험할 수 있는 과학관입니다.',
            manager: '목포시청 문화예술과',
            contact: '061-270-8405',
            images: [
                'https://via.placeholder.com/800x600/0ea5e9/ffffff?text=바다과학관'
            ]
        },
        {
            id: 19,
            name: '목포 평화의 소녀상',
            category: '시설',
            sub_category: '기념물',
            latitude: 34.7842,
            longitude: 126.3762,
            address: '전라남도 목포시 평화로 82',
            status: '정상',
            price: 500000000, // 5억원
            description: '역사를 기억하고 평화를 기원하는 소녀상입니다.',
            manager: '목포시청 문화예술과',
            images: [
                'https://via.placeholder.com/800x600/7c3aed/ffffff?text=평화의소녀상'
            ]
        },
        {
            id: 20,
            name: '목포해양대학교 평생교육원',
            category: '건물',
            sub_category: '교육시설',
            latitude: 34.9053,
            longitude: 126.3805,
            address: '전라남도 목포시 해양대학로 91',
            area: 3500,
            capacity: 150,
            status: '점검중',
            price: 28000000000, // 280억원
            description: '지역주민을 위한 평생교육 프로그램을 운영합니다.',
            manager: '목포해양대학교',
            contact: '061-240-7114',
            images: [
                'https://via.placeholder.com/800x600/0891b2/ffffff?text=평생교육원'
            ]
        }
    ];
    
    AppState.assets = sampleAssets;
    AppState.filteredAssets = sampleAssets;
    
    UI.updateResultCount();
    UI.renderList();
    MapManager.displayMarkers(AppState.filteredAssets);
    
    console.log('✅ 목포시 샘플 데이터 20개 로드 완료 (이미지 포함)');
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
