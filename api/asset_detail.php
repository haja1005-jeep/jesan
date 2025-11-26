<?php
/**
 * 재산 상세 정보 조회 API
 * GET /api/asset_detail.php?id={asset_id}
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

setCorsHeaders();

try {
    $pdo = getDBConnection();
    
    // 재산 ID 가져오기
    $assetId = getParam('id');
    
    if (!$assetId) {
        sendErrorResponse('재산 ID가 필요합니다.');
    }
    
    // 재산 정보 조회
    $sql = "SELECT 
                a.*,
                COUNT(DISTINCT b.id) as booking_count,
                AVG(r.rating) as avg_rating,
                COUNT(DISTINCT r.id) as review_count
            FROM " . table('assets') . " a
            LEFT JOIN " . table('bookings') . " b ON a.id = b.asset_id AND b.status IN ('승인', '완료')
            LEFT JOIN " . table('reviews') . " r ON a.id = r.asset_id
            WHERE a.id = :id
            GROUP BY a.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $assetId]);
    $asset = $stmt->fetch();
    
    if (!$asset) {
        sendErrorResponse('재산을 찾을 수 없습니다.', 404);
    }
    
    // 이미지 조회
    $imageSql = "SELECT * FROM " . table('asset_images') . " WHERE asset_id = :asset_id ORDER BY is_primary DESC, id ASC";
    $imageStmt = $pdo->prepare($imageSql);
    $imageStmt->execute(['asset_id' => $assetId]);
    $images = $imageStmt->fetchAll();
    
    // 예약 가능한 날짜 조회 (향후 30일)
    $bookingSql = "SELECT 
                    booking_date,
                    COUNT(*) as booking_count
                FROM " . table('bookings') . "
                WHERE asset_id = :asset_id
                AND booking_date >= CURDATE()
                AND booking_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND status IN ('신청', '승인')
                GROUP BY booking_date";
    
    $bookingStmt = $pdo->prepare($bookingSql);
    $bookingStmt->execute(['asset_id' => $assetId]);
    $bookings = $bookingStmt->fetchAll();
    
    // 날짜별 예약 현황을 배열로 변환
    $bookingCalendar = [];
    foreach ($bookings as $booking) {
        $bookingCalendar[$booking['booking_date']] = intval($booking['booking_count']);
    }
    
    // 리뷰 조회 (최신 10개)
    $reviewSql = "SELECT 
                    r.*,
                    u.name as user_name
                FROM " . table('reviews') . " r
                LEFT JOIN " . table('users') . " u ON r.user_id = u.id
                WHERE r.asset_id = :asset_id
                ORDER BY r.created_at DESC
                LIMIT 10";
    
    $reviewStmt = $pdo->prepare($reviewSql);
    $reviewStmt->execute(['asset_id' => $assetId]);
    $reviews = $reviewStmt->fetchAll();
    
    // 평균 평점 계산 (별점별 개수)
    $ratingDistSql = "SELECT 
                        rating,
                        COUNT(*) as count
                    FROM " . table('reviews') . "
                    WHERE asset_id = :asset_id
                    GROUP BY rating
                    ORDER BY rating DESC";
    
    $ratingDistStmt = $pdo->prepare($ratingDistSql);
    $ratingDistStmt->execute(['asset_id' => $assetId]);
    $ratingDistribution = $ratingDistStmt->fetchAll();
    
    // 관련 재산 추천 (같은 카테고리, 가까운 위치)
    $relatedSql = "SELECT 
                    a.*,
                    (6371 * acos(cos(radians(:lat)) * cos(radians(a.latitude)) * 
                    cos(radians(a.longitude) - radians(:lng)) + 
                    sin(radians(:lat)) * sin(radians(a.latitude)))) as distance
                FROM " . table('assets') . " a
                WHERE a.category = :category
                AND a.id != :id
                AND a.status = '정상'
                ORDER BY distance ASC
                LIMIT 5";
    
    $relatedStmt = $pdo->prepare($relatedSql);
    $relatedStmt->execute([
        'lat' => $asset['latitude'],
        'lng' => $asset['longitude'],
        'category' => $asset['category'],
        'id' => $assetId
    ]);
    $relatedAssets = $relatedStmt->fetchAll();
    
    // 응답
    sendSuccessResponse([
        'asset' => $asset,
        'images' => $images,
        'booking_calendar' => $bookingCalendar,
        'reviews' => $reviews,
        'rating_distribution' => $ratingDistribution,
        'related_assets' => $relatedAssets
    ]);
    
} catch (Exception $e) {
    error_log("재산 상세 정보 조회 오류: " . $e->getMessage());
    sendErrorResponse('재산 상세 정보를 조회하는 중 오류가 발생했습니다.', 500);
}
