<?php
/**
 * 재산 목록 조회 API
 * GET /api/assets.php
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

setCorsHeaders();

try {
    $pdo = getDBConnection();
    
    // 검색 및 필터 파라미터
    $search = getParam('search', '');
    $category = getParam('category', '');
    $region = getParam('region', '');
    $status = getParam('status', '');
    $latitude = getParam('lat', '');
    $longitude = getParam('lng', '');
    $radius = getParam('radius', 10); // km
    
    // 페이지네이션
    $pagination = getPagination(
        getParam('page', 1),
        getParam('limit', 20)
    );
    
    // 쿼리 작성
    $sql = "SELECT 
                a.*,
                COUNT(DISTINCT b.id) as booking_count,
                AVG(r.rating) as avg_rating,
                COUNT(DISTINCT r.id) as review_count
            FROM " . table("assets") . " a
            LEFT JOIN " . table('bookings') . " b ON a.id = b.asset_id AND b.status IN ('승인', '완료')
            LEFT JOIN " . table('reviews') . " r ON a.id = r.asset_id
            WHERE 1=1";
    
    $params = [];
    
    // 검색어 필터
    if ($search) {
        $sql .= " AND (a.name LIKE :search OR a.address LIKE :search OR a.description LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    // 카테고리 필터
    if ($category) {
        $sql .= " AND a.category = :category";
        $params['category'] = $category;
    }
    
    // 지역 필터
    if ($region) {
        $sql .= " AND a.address LIKE :region";
        $params['region'] = '%' . $region . '%';
    }
    
    // 상태 필터
    if ($status) {
        $sql .= " AND a.status = :status";
        $params['status'] = $status;
    }
    
    $sql .= " GROUP BY a.id";
    
    // 거리순 정렬 (위치 정보가 있는 경우)
    if ($latitude && $longitude) {
        $sql .= " ORDER BY 
                  (6371 * acos(cos(radians(:lat)) * cos(radians(a.latitude)) * 
                  cos(radians(a.longitude) - radians(:lng)) + 
                  sin(radians(:lat)) * sin(radians(a.latitude)))) ASC";
        $params['lat'] = $latitude;
        $params['lng'] = $longitude;
    } else {
        $sql .= " ORDER BY a.created_at DESC";
    }
    
    // 페이지네이션 적용
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // 파라미터 바인딩
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
    
    $stmt->execute();
    $assets = $stmt->fetchAll();
    
    // 전체 개수 조회
    $countSql = "SELECT COUNT(DISTINCT a.id) as total FROM " . table("assets") . " a WHERE 1=1";
    foreach ($params as $key => $value) {
        if ($key !== 'lat' && $key !== 'lng') {
            if ($key === 'search') {
                $countSql .= " AND (a.name LIKE :search OR a.address LIKE :search OR a.description LIKE :search)";
            } elseif ($key === 'category') {
                $countSql .= " AND a.category = :category";
            } elseif ($key === 'region') {
                $countSql .= " AND a.address LIKE :region";
            } elseif ($key === 'status') {
                $countSql .= " AND a.status = :status";
            }
        }
    }
    
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        if ($key !== 'lat' && $key !== 'lng') {
            $countStmt->bindValue(':' . $key, $value);
        }
    }
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    // 거리 계산 (위치 정보가 있는 경우)
    if ($latitude && $longitude) {
        foreach ($assets as &$asset) {
            $asset['distance'] = calculateDistance(
                $latitude,
                $longitude,
                $asset['latitude'],
                $asset['longitude']
            );
            $asset['distance'] = round($asset['distance'], 2);
        }
    }
    
    // 응답
    sendSuccessResponse([
        'assets' => $assets,
        'pagination' => [
            'page' => $pagination['page'],
            'limit' => $pagination['limit'],
            'total' => intval($total),
            'total_pages' => ceil($total / $pagination['limit'])
        ]
    ]);
    
} catch (Exception $e) {
    error_log("재산 목록 조회 오류: " . $e->getMessage());
    sendErrorResponse('재산 목록을 조회하는 중 오류가 발생했습니다.', 500);
}
