// ================================================
// 🎨 이미지 갤러리 스와이프 기능
// ================================================

class ImageGallery {
    constructor(container) {
        this.container = container;
        this.slides = container.querySelector('.gallery-slides');
        this.images = [];
        this.currentIndex = 0;
        this.startX = 0;
        this.isDragging = false;
        this.startPos = 0;
        this.currentTranslate = 0;
        this.prevTranslate = 0;
        this.animationID = 0;
        
        this.init();
    }
    
    init() {
        // 터치 이벤트
        this.slides.addEventListener('touchstart', this.touchStart.bind(this), { passive: true });
        this.slides.addEventListener('touchend', this.touchEnd.bind(this));
        this.slides.addEventListener('touchmove', this.touchMove.bind(this), { passive: true });
        
        // 마우스 이벤트
        this.slides.addEventListener('mousedown', this.touchStart.bind(this));
        this.slides.addEventListener('mouseup', this.touchEnd.bind(this));
        this.slides.addEventListener('mouseleave', this.touchEnd.bind(this));
        this.slides.addEventListener('mousemove', this.touchMove.bind(this));
        
        // 네비게이션 버튼
        const prevBtn = this.container.querySelector('.gallery-nav.prev');
        const nextBtn = this.container.querySelector('.gallery-nav.next');
        
        if (prevBtn) prevBtn.addEventListener('click', () => this.prevSlide());
        if (nextBtn) nextBtn.addEventListener('click', () => this.nextSlide());
        
        // 썸네일 클릭
        const thumbnails = this.container.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => this.goToSlide(index));
        });
        
        // 키보드 네비게이션
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prevSlide();
            if (e.key === 'ArrowRight') this.nextSlide();
        });
    }
    
    touchStart(event) {
        this.isDragging = true;
        this.startPos = this.getPositionX(event);
        this.animationID = requestAnimationFrame(this.animation.bind(this));
        this.slides.style.cursor = 'grabbing';
    }
    
    touchMove(event) {
        if (this.isDragging) {
            const currentPosition = this.getPositionX(event);
            this.currentTranslate = this.prevTranslate + currentPosition - this.startPos;
        }
    }
    
    touchEnd() {
        this.isDragging = false;
        cancelAnimationFrame(this.animationID);
        
        const movedBy = this.currentTranslate - this.prevTranslate;
        
        // 스와이프 거리가 충분하면 슬라이드 변경
        if (movedBy < -50 && this.currentIndex < this.getSlideCount() - 1) {
            this.currentIndex += 1;
        }
        
        if (movedBy > 50 && this.currentIndex > 0) {
            this.currentIndex -= 1;
        }
        
        this.setPositionByIndex();
        this.slides.style.cursor = 'grab';
    }
    
    getPositionX(event) {
        return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
    }
    
    animation() {
        this.setSliderPosition();
        if (this.isDragging) requestAnimationFrame(this.animation.bind(this));
    }
    
    setSliderPosition() {
        this.slides.style.transform = `translateX(${this.currentTranslate}px)`;
    }
    
    setPositionByIndex() {
        this.currentTranslate = this.currentIndex * -window.innerWidth;
        this.prevTranslate = this.currentTranslate;
        this.setSliderPosition();
        this.updateUI();
    }
    
    prevSlide() {
        if (this.currentIndex > 0) {
            this.currentIndex -= 1;
            this.setPositionByIndex();
        }
    }
    
    nextSlide() {
        if (this.currentIndex < this.getSlideCount() - 1) {
            this.currentIndex += 1;
            this.setPositionByIndex();
        }
    }
    
    goToSlide(index) {
        this.currentIndex = index;
        this.setPositionByIndex();
    }
    
    getSlideCount() {
        return this.slides.querySelectorAll('.gallery-slide').length;
    }
    
    updateUI() {
        // 카운터 업데이트
        const counter = this.container.querySelector('.image-counter');
        if (counter) {
            counter.textContent = `${this.currentIndex + 1} / ${this.getSlideCount()}`;
        }
        
        // 썸네일 active 상태
        const thumbnails = this.container.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (index === this.currentIndex) {
                thumb.classList.add('active');
                // 썸네일을 보이는 영역으로 스크롤
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('active');
            }
        });
        
        // 네비게이션 버튼 비활성화
        const prevBtn = this.container.querySelector('.gallery-nav.prev');
        const nextBtn = this.container.querySelector('.gallery-nav.next');
        
        if (prevBtn) prevBtn.style.opacity = this.currentIndex === 0 ? '0.3' : '1';
        if (nextBtn) nextBtn.style.opacity = this.currentIndex === this.getSlideCount() - 1 ? '0.3' : '1';
    }
}

// ================================================
// 🎯 모달 관리
// ================================================

class AssetModal {
    constructor() {
        this.modal = null;
        this.gallery = null;
        this.currentAsset = null;
    }
    
    open(asset) {
        this.currentAsset = asset;
        this.render();
        
        // 갤러리 초기화
        setTimeout(() => {
            const galleryContainer = document.querySelector('.asset-image-gallery');
            if (galleryContainer) {
                this.gallery = new ImageGallery(galleryContainer);
            }
        }, 100);
    }
    
    close() {
        const modal = document.querySelector('.asset-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => modal.remove(), 300);
        }
    }
    
    render() {
        const images = this.currentAsset.images || [];
        const hasImages = images.length > 0;
        
        const modalHTML = `
            <div class="asset-modal active">
                <div class="modal-overlay" onclick="closeAssetModal()"></div>
                <div class="modal-content">
                    <button class="modal-close" onclick="closeAssetModal()">✕</button>
                    
                    <div class="modal-scroll-container">
                        ${hasImages ? this.renderGallery(images) : ''}
                        
                        <div class="asset-detail-header">
                            <div class="asset-detail-title">
                                <div class="asset-icon">${this.getCategoryIcon(this.currentAsset.category)}</div>
                                <div class="asset-title-text">
                                    <h2>${this.currentAsset.name}</h2>
                                    <div class="asset-meta">
                                        <span class="category-badge">${this.currentAsset.category}</span>
                                        <span class="status-badge status-${this.getStatusClass(this.currentAsset.status)}">
                                            ${this.currentAsset.status || '정상'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="asset-detail-body">
                            ${this.renderBasicInfo()}
                            ${this.renderDescription()}
                            ${this.renderManagerInfo()}
                        </div>
                        
                        <div class="asset-detail-footer">
                            <button class="action-btn btn-primary" onclick="alert('예약 기능 준비중입니다!')">
                                <span class="btn-icon">📅</span>
                                <span>예약하기</span>
                            </button>
                            <button class="action-btn btn-secondary" onclick="shareAsset()">
                                <span class="btn-icon">🔗</span>
                                <span>공유하기</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    renderGallery(images) {
        return `
            <div class="asset-image-gallery">
                <div class="gallery-main-container">
                    <div class="gallery-slides">
                        ${images.map(img => `
                            <div class="gallery-slide">
                                <img src="${img.image_url}" alt="${this.currentAsset.name}" 
                                     onclick="openLightbox('${img.image_url}')">
                            </div>
                        `).join('')}
                    </div>
                    
                    <button class="gallery-nav prev">‹</button>
                    <button class="gallery-nav next">›</button>
                    
                    <div class="image-counter">1 / ${images.length}</div>
                    <div class="zoom-indicator">
                        <span>🔍</span>
                        <span>클릭하여 확대</span>
                    </div>
                    ${images.length > 1 ? '<div class="swipe-indicator">👆 좌우로 스와이프</div>' : ''}
                </div>
                
                ${images.length > 1 ? `
                    <div class="thumbnail-list">
                        ${images.map((img, index) => `
                            <div class="thumbnail ${index === 0 ? 'active' : ''}">
                                <img src="${img.image_url}" alt="${this.currentAsset.name}">
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    renderBasicInfo() {
        const asset = this.currentAsset;
        return `
            <div class="detail-section">
                <h3>기본 정보</h3>
                <div class="info-cards-grid">
                    ${asset.area ? `
                        <div class="info-card">
                            <div class="info-card-icon">📐</div>
                            <div class="info-card-label">면적</div>
                            <div class="info-card-value">${this.formatArea(asset.area)}</div>
                        </div>
                    ` : ''}
                    
                    ${asset.price ? `
                        <div class="info-card">
                            <div class="info-card-icon">💰</div>
                            <div class="info-card-label">금액</div>
                            <div class="info-card-value">${this.formatPrice(asset.price)}</div>
                        </div>
                    ` : ''}
                    
                    ${asset.capacity ? `
                        <div class="info-card">
                            <div class="info-card-icon">👥</div>
                            <div class="info-card-label">수용인원</div>
                            <div class="info-card-value">${asset.capacity}명</div>
                        </div>
                    ` : ''}
                    
                    ${asset.address ? `
                        <div class="info-card" style="grid-column: 1 / -1;">
                            <div class="info-card-icon">📍</div>
                            <div class="info-card-label">주소</div>
                            <div class="info-card-value" style="font-size: 16px;">${asset.address}</div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    renderDescription() {
        if (!this.currentAsset.description) return '';
        
        return `
            <div class="detail-section">
                <h3>상세 설명</h3>
                <div class="description-card">
                    <p>${this.currentAsset.description}</p>
                </div>
            </div>
        `;
    }
    
    renderManagerInfo() {
        const asset = this.currentAsset;
        if (!asset.manager && !asset.contact) return '';
        
        return `
            <div class="detail-section">
                <h3>담당자 정보</h3>
                <div class="manager-info">
                    <div class="manager-avatar">👤</div>
                    <div class="manager-details">
                        <div class="manager-name">${asset.manager || '관리자'}</div>
                        <div class="manager-contact">
                            <span>📞</span>
                            <span>${asset.contact || '-'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    getCategoryIcon(category) {
        const icons = {
            '건물': '🏢',
            '시설': '🏛️',
            '공원': '🌳',
            '토지': '🗺️',
            '장비': '⚙️'
        };
        return icons[category] || '🏢';
    }
    
    getStatusClass(status) {
        const statusMap = {
            '정상': 'normal',
            '점검중': 'maintenance',
            '사용불가': 'unavailable'
        };
        return statusMap[status] || 'normal';
    }
    
    formatPrice(price) {
        if (price >= 100000000) return (price / 100000000).toFixed(0) + '억원';
        if (price >= 10000) return (price / 10000).toFixed(0) + '만원';
        return price.toLocaleString() + '원';
    }
    
    formatArea(area) {
        return area ? area.toLocaleString() + '㎡' : '-';
    }
}

// ================================================
// 🌐 전역 함수
// ================================================

let currentModal = null;

function openAssetModal(asset) {
    currentModal = new AssetModal();
    currentModal.open(asset);
}

function closeAssetModal() {
    if (currentModal) {
        currentModal.close();
        currentModal = null;
    }
}

function shareAsset() {
    if (navigator.share) {
        navigator.share({
            title: currentModal.currentAsset.name,
            text: `${currentModal.currentAsset.name} - ${currentModal.currentAsset.address}`,
            url: window.location.href
        }).catch(() => {});
    } else {
        // 폴백: 링크 복사
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('링크가 클립보드에 복사되었습니다!');
        });
    }
}

function openLightbox(imageUrl) {
    const lightboxHTML = `
        <div class="image-lightbox" style="display: flex;" onclick="this.remove()">
            <div class="lightbox-overlay"></div>
            <div class="lightbox-content">
                <button class="lightbox-close" onclick="event.stopPropagation(); this.closest('.image-lightbox').remove()">✕</button>
                <img src="${imageUrl}" alt="확대 이미지">
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', lightboxHTML);
}

// ================================================
// 🚀 초기화
// ================================================

// ESC 키로 모달 닫기
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAssetModal();
    }
});
