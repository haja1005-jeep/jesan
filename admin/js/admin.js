// ================================================
// 관리자 페이지 JavaScript
// ================================================

/**
 * 대시보드 초기화
 */
async function initDashboard() {
    try {
        await loadPendingBookings();
        await loadRecentAssets();
        initChart();
    } catch (error) {
        console.error('대시보드 초기화 오류:', error);
    }
}

/**
 * 승인 대기 예약 로드
 */
async function loadPendingBookings() {
    try {
        const data = await API.getBookings({
            status: '신청',
            limit: 5
        });
        
        const tbody = document.getElementById('pendingBookings');
        
        if (!data.bookings || data.bookings.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 32px; color: #9ca3af;">
                        승인 대기 중인 예약이 없습니다.
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = data.bookings.map(booking => `
            <tr>
                <td>${booking.user_name || '익명'}</td>
                <td>${booking.asset_name}</td>
                <td>${formatDate(booking.booking_date)}</td>
                <td><span class="status-badge pending">대기중</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-sm primary" onclick="approveBooking(${booking.id})">승인</button>
                        <button class="btn-sm danger" onclick="rejectBooking(${booking.id})">거부</button>
                    </div>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('승인 대기 예약 로드 오류:', error);
    }
}

/**
 * 최근 등록 재산 로드
 */
async function loadRecentAssets() {
    try {
        const data = await API.getAssets({
            limit: 5
        });
        
        const container = document.getElementById('recentAssets');
        
        if (!data.assets || data.assets.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 32px; color: #9ca3af;">
                    등록된 재산이 없습니다.
                </div>
            `;
            return;
        }
        
        container.innerHTML = data.assets.map(asset => `
            <div class="asset-item">
                <div class="asset-icon">${CONFIG.MARKER_ICONS[asset.category] || '📍'}</div>
                <div class="asset-info">
                    <div class="asset-name">${asset.name}</div>
                    <div class="asset-meta">${asset.category} · ${asset.address}</div>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('최근 재산 로드 오류:', error);
    }
}

/**
 * 예약 승인
 */
async function approveBooking(bookingId) {
    if (!confirm('이 예약을 승인하시겠습니까?')) {
        return;
    }
    
    try {
        // API 호출 (PUT 메소드로 수정)
        const response = await fetch(`${CONFIG.API_BASE_URL}/booking.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: bookingId,
                status: '승인'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('예약이 승인되었습니다.');
            loadPendingBookings();
        } else {
            alert('예약 승인 중 오류가 발생했습니다.');
        }
    } catch (error) {
        console.error('예약 승인 오류:', error);
        alert('예약 승인 중 오류가 발생했습니다.');
    }
}

/**
 * 예약 거부
 */
async function rejectBooking(bookingId) {
    const reason = prompt('거부 사유를 입력해주세요:');
    if (!reason) {
        return;
    }
    
    try {
        const response = await fetch(`${CONFIG.API_BASE_URL}/booking.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: bookingId,
                status: '거부',
                admin_note: reason
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('예약이 거부되었습니다.');
            loadPendingBookings();
        } else {
            alert('예약 거부 중 오류가 발생했습니다.');
        }
    } catch (error) {
        console.error('예약 거부 오류:', error);
        alert('예약 거부 중 오류가 발생했습니다.');
    }
}

/**
 * 차트 초기화
 */
function initChart() {
    const ctx = document.getElementById('bookingChart');
    
    if (!ctx) return;
    
    // 샘플 데이터
    const labels = [];
    const data = [];
    
    // 최근 30일 데이터 생성
    for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' }));
        data.push(Math.floor(Math.random() * 20) + 5);
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '예약 수',
                data: data,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5
                    }
                }
            }
        }
    });
}

/**
 * 모달 관련 함수
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

/**
 * 모달 오버레이 클릭 시 닫기
 */
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('modal-close')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.classList.remove('active');
        }
    }
});

/**
 * 페이지 로드 시 실행
 */
window.addEventListener('load', () => {
    initDashboard();
});
