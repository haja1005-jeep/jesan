// ================================================
// API 통신 모듈
// ================================================

const API = {
    /**
     * 재산 목록 조회
     */
    async getAssets(params = {}) {
        try {
            const queryString = new URLSearchParams(params).toString();
            const url = `${CONFIG.API_BASE_URL}/assets.php${queryString ? '?' + queryString : ''}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('재산 목록 조회 오류:', error);
            throw error;
        }
    },
    
    /**
     * 재산 상세 정보 조회
     */
    async getAssetDetail(id) {
        try {
            const response = await fetch(`${CONFIG.API_BASE_URL}/asset_detail.php?id=${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('재산 상세 정보 조회 오류:', error);
            throw error;
        }
    },
    
    /**
     * 예약 생성
     */
    async createBooking(bookingData) {
        try {
            const response = await fetch(`${CONFIG.API_BASE_URL}/booking.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bookingData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('예약 생성 오류:', error);
            throw error;
        }
    },
    
    /**
     * 예약 목록 조회
     */
    async getBookings(params = {}) {
        try {
            const queryString = new URLSearchParams(params).toString();
            const url = `${CONFIG.API_BASE_URL}/booking.php${queryString ? '?' + queryString : ''}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('예약 목록 조회 오류:', error);
            throw error;
        }
    },
    
    /**
     * 예약 취소
     */
    async cancelBooking(bookingId) {
        try {
            const response = await fetch(`${CONFIG.API_BASE_URL}/booking.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: bookingId })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('예약 취소 오류:', error);
            throw error;
        }
    },
    
    /**
     * 리뷰 작성
     */
    async createReview(reviewData) {
        try {
            const response = await fetch(`${CONFIG.API_BASE_URL}/review.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reviewData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('리뷰 작성 오류:', error);
            throw error;
        }
    }
};

/**
 * 디바운스 유틸리티 함수
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

/**
 * 날짜 포맷 유틸리티
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * 시간 포맷 유틸리티
 */
function formatTime(timeString) {
    if (!timeString) return '';
    const [hours, minutes] = timeString.split(':');
    return `${hours}:${minutes}`;
}

/**
 * 숫자 포맷 유틸리티
 */
function formatNumber(number) {
    return new Intl.NumberFormat('ko-KR').format(number);
}
