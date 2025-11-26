<?php
/**
 * 예약 관리 API (실서버용)
 * GET    /jesan/api/booking.php - 예약 목록 조회
 * POST   /jesan/api/booking.php - 예약 생성
 * PUT    /jesan/api/booking.php - 예약 수정
 * DELETE /jesan/api/booking.php - 예약 취소
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            handleGetBookings($pdo);
            break;
            
        case 'POST':
            handleCreateBooking($pdo);
            break;
            
        case 'PUT':
            handleUpdateBooking($pdo);
            break;
            
        case 'DELETE':
            handleCancelBooking($pdo);
            break;
            
        default:
            sendErrorResponse('지원하지 않는 HTTP 메소드입니다.', 405);
    }
    
} catch (Exception $e) {
    error_log("예약 API 오류: " . $e->getMessage());
    sendErrorResponse('예약 처리 중 오류가 발생했습니다.', 500);
}

/**
 * 예약 목록 조회
 */
function handleGetBookings($pdo) {
    $userId = getParam('user_id');
    $assetId = getParam('asset_id');
    $status = getParam('status');
    $dateFrom = getParam('date_from');
    $dateTo = getParam('date_to');
    
    $pagination = getPagination(
        getParam('page', 1),
        getParam('limit', 20)
    );
    
    $sql = "SELECT 
                b.*,
                a.name as asset_name,
                a.category as asset_category,
                a.address as asset_address,
                u.name as user_name,
                u.phone as user_phone
            FROM " . table('bookings') . " b
            INNER JOIN " . table('assets') . " a ON b.asset_id = a.id
            INNER JOIN " . table('users') . " u ON b.user_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if ($userId) {
        $sql .= " AND b.user_id = :user_id";
        $params['user_id'] = $userId;
    }
    
    if ($assetId) {
        $sql .= " AND b.asset_id = :asset_id";
        $params['asset_id'] = $assetId;
    }
    
    if ($status) {
        $sql .= " AND b.status = :status";
        $params['status'] = $status;
    }
    
    if ($dateFrom) {
        $sql .= " AND b.booking_date >= :date_from";
        $params['date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $sql .= " AND b.booking_date <= :date_to";
        $params['date_to'] = $dateTo;
    }
    
    $sql .= " ORDER BY b.booking_date DESC, b.start_time DESC";
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
    
    $stmt->execute();
    $bookings = $stmt->fetchAll();
    
    // 전체 개수 조회
    $countSql = "SELECT COUNT(*) as total FROM " . table('bookings') . " b WHERE 1=1";
    foreach ($params as $key => $value) {
        if ($key === 'user_id') {
            $countSql .= " AND b.user_id = :user_id";
        } elseif ($key === 'asset_id') {
            $countSql .= " AND b.asset_id = :asset_id";
        } elseif ($key === 'status') {
            $countSql .= " AND b.status = :status";
        } elseif ($key === 'date_from') {
            $countSql .= " AND b.booking_date >= :date_from";
        } elseif ($key === 'date_to') {
            $countSql .= " AND b.booking_date <= :date_to";
        }
    }
    
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue(':' . $key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    sendSuccessResponse([
        'bookings' => $bookings,
        'pagination' => [
            'page' => $pagination['page'],
            'limit' => $pagination['limit'],
            'total' => intval($total),
            'total_pages' => ceil($total / $pagination['limit'])
        ]
    ]);
}

/**
 * 예약 생성
 */
function handleCreateBooking($pdo) {
    // 인증 확인 (실제 환경에서는 주석 해제)
    // $userId = checkAuth();
    
    $data = getPostData();
    
    // 필수 필드 검증
    $required = ['asset_id', 'user_id', 'booking_date'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendErrorResponse("{$field}는 필수 항목입니다.");
        }
    }
    
    $assetId = $data['asset_id'];
    $userId = $data['user_id'];
    $bookingDate = $data['booking_date'];
    $startTime = $data['start_time'] ?? null;
    $endTime = $data['end_time'] ?? null;
    $purpose = $data['purpose'] ?? '';
    
    // 재산 상태 확인
    $assetStmt = $pdo->prepare("SELECT status FROM " . table('assets') . " WHERE id = :id");
    $assetStmt->execute(['id' => $assetId]);
    $asset = $assetStmt->fetch();
    
    if (!$asset) {
        sendErrorResponse('재산을 찾을 수 없습니다.', 404);
    }
    
    if ($asset['status'] !== '정상') {
        sendErrorResponse('현재 예약할 수 없는 재산입니다.');
    }
    
    // 중복 예약 확인
    $checkSql = "SELECT COUNT(*) as count 
                FROM " . table('bookings') . " 
                WHERE asset_id = :asset_id 
                AND booking_date = :booking_date 
                AND status IN ('신청', '승인')";
    
    if ($startTime && $endTime) {
        $checkSql .= " AND (
            (start_time <= :start_time AND end_time > :start_time) OR
            (start_time < :end_time AND end_time >= :end_time) OR
            (start_time >= :start_time AND end_time <= :end_time)
        )";
    }
    
    $checkStmt = $pdo->prepare($checkSql);
    $checkParams = [
        'asset_id' => $assetId,
        'booking_date' => $bookingDate
    ];
    
    if ($startTime && $endTime) {
        $checkParams['start_time'] = $startTime;
        $checkParams['end_time'] = $endTime;
    }
    
    $checkStmt->execute($checkParams);
    $duplicate = $checkStmt->fetch();
    
    if ($duplicate['count'] > 0) {
        sendErrorResponse('해당 시간에 이미 예약이 있습니다.');
    }
    
    // 예약 생성
    $sql = "INSERT INTO " . table('bookings') . " (
                asset_id, user_id, booking_date, start_time, end_time, purpose, status, created_at
            ) VALUES (
                :asset_id, :user_id, :booking_date, :start_time, :end_time, :purpose, '신청', NOW()
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'asset_id' => $assetId,
        'user_id' => $userId,
        'booking_date' => $bookingDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'purpose' => $purpose
    ]);
    
    $bookingId = $pdo->lastInsertId();
    
    // 활동 로그 (옵션)
    // logActivity($userId, 'booking_create', "예약 생성: ID {$bookingId}");
    
    sendSuccessResponse([
        'booking_id' => $bookingId,
        'message' => '예약이 신청되었습니다.'
    ], '예약 신청이 완료되었습니다.');
}

/**
 * 예약 수정 (관리자용)
 */
function handleUpdateBooking($pdo) {
    // 관리자 권한 확인 (실제 환경에서는 주석 해제)
    // checkAdmin();
    
    $data = getPostData();
    
    if (empty($data['id'])) {
        sendErrorResponse('예약 ID가 필요합니다.');
    }
    
    $bookingId = $data['id'];
    $status = $data['status'] ?? null;
    $adminNote = $data['admin_note'] ?? '';
    
    // 예약 존재 확인
    $checkStmt = $pdo->prepare("SELECT * FROM " . table('bookings') . " WHERE id = :id");
    $checkStmt->execute(['id' => $bookingId]);
    $booking = $checkStmt->fetch();
    
    if (!$booking) {
        sendErrorResponse('예약을 찾을 수 없습니다.', 404);
    }
    
    // 예약 수정
    $updates = [];
    $params = ['id' => $bookingId];
    
    if ($status) {
        $updates[] = "status = :status";
        $params['status'] = $status;
    }
    
    if ($adminNote) {
        $updates[] = "admin_note = :admin_note";
        $params['admin_note'] = $adminNote;
    }
    
    if (empty($updates)) {
        sendErrorResponse('수정할 내용이 없습니다.');
    }
    
    $sql = "UPDATE " . table('bookings') . " SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    sendSuccessResponse([], '예약이 수정되었습니다.');
}

/**
 * 예약 취소
 */
function handleCancelBooking($pdo) {
    // 인증 확인 (실제 환경에서는 주석 해제)
    // $userId = checkAuth();
    
    $data = getPostData();
    
    if (empty($data['id'])) {
        sendErrorResponse('예약 ID가 필요합니다.');
    }
    
    $bookingId = $data['id'];
    
    // 예약 존재 확인
    $checkStmt = $pdo->prepare("SELECT * FROM " . table('bookings') . " WHERE id = :id");
    $checkStmt->execute(['id' => $bookingId]);
    $booking = $checkStmt->fetch();
    
    if (!$booking) {
        sendErrorResponse('예약을 찾을 수 없습니다.', 404);
    }
    
    // 권한 확인 (본인 예약인지 또는 관리자인지)
    // if ($booking['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
    //     sendErrorResponse('예약을 취소할 권한이 없습니다.', 403);
    // }
    
    // 예약 취소
    $sql = "UPDATE " . table('bookings') . " SET status = '취소' WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $bookingId]);
    
    sendSuccessResponse([], '예약이 취소되었습니다.');
}
