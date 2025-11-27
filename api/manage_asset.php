<?php
/**
 * 재산 관리 API
 * POST /api/manage_asset.php
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 관리자 권한 확인
if (!isLoggedIn() || !isAdmin()) {
    sendErrorResponse('권한이 없습니다.', 403);
}

setCorsHeaders();

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('잘못된 요청 방식입니다.', 405);
}

try {
    $pdo = getDBConnection();
    
    $action = getParam('action', '');
    
    switch ($action) {
        case 'create':
            createAsset($pdo);
            break;
        case 'update':
            updateAsset($pdo);
            break;
        case 'delete':
            deleteAsset($pdo);
            break;
        default:
            sendErrorResponse('알 수 없는 작업입니다.', 400);
    }
    
} catch (Exception $e) {
    error_log("재산 관리 오류: " . $e->getMessage());
    sendErrorResponse('작업 중 오류가 발생했습니다.', 500);
}

/**
 * 재산 생성
 */
function createAsset($pdo) {
    // 필수 필드
    $name = getParam('name', '');
    $category = getParam('category', '');
    $latitude = getParam('latitude', '');
    $longitude = getParam('longitude', '');
    
    if (empty($name) || empty($category) || empty($latitude) || empty($longitude)) {
        sendErrorResponse('필수 항목을 입력해주세요.', 400);
    }
    
    // 선택 필드
    $sub_category = getParam('sub_category', null);
    $address = getParam('address', null);
    $dong = getParam('dong', null);
    $area = getParam('area', null);
    $price = getParam('price', null);
    $capacity = getParam('capacity', null);
    $status = getParam('status', '정상');
    $description = getParam('description', null);
    $manager = getParam('manager', null);
    $contact = getParam('contact', null);
    $vr_aerial_url = getParam('vr_aerial_url', null);
    $vr_ground_url = getParam('vr_ground_url', null);
    $youtube_url = getParam('youtube_url', null);
    
    // INSERT
    $sql = "INSERT INTO " . table('assets') . " 
            (name, category, sub_category, latitude, longitude, address, dong, area, price, 
             capacity, status, description, manager, contact, vr_aerial_url, vr_ground_url, youtube_url)
            VALUES 
            (:name, :category, :sub_category, :latitude, :longitude, :address, :dong, :area, :price,
             :capacity, :status, :description, :manager, :contact, :vr_aerial_url, :vr_ground_url, :youtube_url)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'category' => $category,
        'sub_category' => $sub_category,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'address' => $address,
        'dong' => $dong,
        'area' => $area,
        'price' => $price,
        'capacity' => $capacity,
        'status' => $status,
        'description' => $description,
        'manager' => $manager,
        'contact' => $contact,
        'vr_aerial_url' => $vr_aerial_url,
        'vr_ground_url' => $vr_ground_url,
        'youtube_url' => $youtube_url
    ]);
    
    $assetId = $pdo->lastInsertId();
    
    // 이미지 저장
    $images = json_decode(getParam('images', '[]'), true);
    if (!empty($images)) {
        saveAssetImages($pdo, $assetId, $images);
    }
    
    sendSuccessResponse([
        'asset_id' => $assetId,
        'message' => '재산이 등록되었습니다.'
    ]);
}

/**
 * 재산 수정
 */
function updateAsset($pdo) {
    $id = getParam('id', '');
    
    if (empty($id)) {
        sendErrorResponse('재산 ID가 필요합니다.', 400);
    }
    
    // 필수 필드
    $name = getParam('name', '');
    $category = getParam('category', '');
    $latitude = getParam('latitude', '');
    $longitude = getParam('longitude', '');
    
    if (empty($name) || empty($category) || empty($latitude) || empty($longitude)) {
        sendErrorResponse('필수 항목을 입력해주세요.', 400);
    }
    
    // 선택 필드
    $sub_category = getParam('sub_category', null);
    $address = getParam('address', null);
    $dong = getParam('dong', null);
    $area = getParam('area', null);
    $price = getParam('price', null);
    $capacity = getParam('capacity', null);
    $status = getParam('status', '정상');
    $description = getParam('description', null);
    $manager = getParam('manager', null);
    $contact = getParam('contact', null);
    $vr_aerial_url = getParam('vr_aerial_url', null);
    $vr_ground_url = getParam('vr_ground_url', null);
    $youtube_url = getParam('youtube_url', null);
    
    // UPDATE
    $sql = "UPDATE " . table('assets') . " 
            SET name = :name,
                category = :category,
                sub_category = :sub_category,
                latitude = :latitude,
                longitude = :longitude,
                address = :address,
                dong = :dong,
                area = :area,
                price = :price,
                capacity = :capacity,
                status = :status,
                description = :description,
                manager = :manager,
                contact = :contact,
                vr_aerial_url = :vr_aerial_url,
                vr_ground_url = :vr_ground_url,
                youtube_url = :youtube_url,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $id,
        'name' => $name,
        'category' => $category,
        'sub_category' => $sub_category,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'address' => $address,
        'dong' => $dong,
        'area' => $area,
        'price' => $price,
        'capacity' => $capacity,
        'status' => $status,
        'description' => $description,
        'manager' => $manager,
        'contact' => $contact,
        'vr_aerial_url' => $vr_aerial_url,
        'vr_ground_url' => $vr_ground_url,
        'youtube_url' => $youtube_url
    ]);
    
    // 기존 이미지 삭제
    $deleteSql = "DELETE FROM " . table('asset_images') . " WHERE asset_id = :asset_id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute(['asset_id' => $id]);
    
    // 새 이미지 저장
    $images = json_decode(getParam('images', '[]'), true);
    if (!empty($images)) {
        saveAssetImages($pdo, $id, $images);
    }
    
    sendSuccessResponse([
        'asset_id' => $id,
        'message' => '재산이 수정되었습니다.'
    ]);
}

/**
 * 재산 삭제
 */
function deleteAsset($pdo) {
    $id = getParam('id', '');
    
    if (empty($id)) {
        sendErrorResponse('재산 ID가 필요합니다.', 400);
    }
    
    // 삭제
    $sql = "DELETE FROM " . table('assets') . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    
    sendSuccessResponse([
        'message' => '재산이 삭제되었습니다.'
    ]);
}

/**
 * 재산 이미지 저장
 */
function saveAssetImages($pdo, $assetId, $images) {
    $sql = "INSERT INTO " . table('asset_images') . " 
            (asset_id, image_url, is_primary, display_order)
            VALUES (:asset_id, :image_url, :is_primary, :display_order)";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($images as $index => $imageUrl) {
        $stmt->execute([
            'asset_id' => $assetId,
            'image_url' => $imageUrl,
            'is_primary' => $index === 0 ? 1 : 0,
            'display_order' => $index
        ]);
    }
}
