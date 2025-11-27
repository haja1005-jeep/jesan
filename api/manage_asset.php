<?php
/**
 * 재산 관리 API
 * POST /api/manage_asset.php
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

setCorsHeaders();

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('잘못된 요청 방식입니다.', 405);
}

function getInput($key, $default = null) {
    if (!isset($_POST[$key])) return $default;
    $value = trim($_POST[$key]);
    return $value === '' ? $default : $value;
}

try {
    $pdo = getDBConnection();
    
    $action = getInput('action', '');
    
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
            sendErrorResponse('알 수 없는 작업입니다. (action: ' . $action . ')', 400);
    }
    
} catch (Exception $e) {
    error_log("재산 관리 오류: " . $e->getMessage());
    sendErrorResponse('작업 중 오류가 발생했습니다: ' . $e->getMessage(), 500);
}

/**
 * 재산 생성
 */
function createAsset($pdo) {
    $name = getInput('name');
    $category = getInput('category');
    $latitude = getInput('latitude');
    $longitude = getInput('longitude');
    
    if (!$name || !$category || !$latitude || !$longitude) {
        sendErrorResponse('필수 항목(이름, 카테고리, 위도, 경도)을 입력해주세요.', 400);
    }
    
    $sub_category = getInput('sub_category');
    $address = getInput('address');
    $dong = getInput('dong');
    $area = getInput('area');
    $price = getInput('price');
    $capacity = getInput('capacity');
    $status = getInput('status', '정상');
    $description = getInput('description');
    $manager = getInput('manager');
    $contact = getInput('contact');
    $vr_aerial_url = getInput('vr_aerial_url');
    $vr_ground_url = getInput('vr_ground_url');
    $youtube_url = getInput('youtube_url');
    
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
    
    $images = json_decode(getInput('images', '[]'), true);
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
    $id = getInput('id');
    
    if (!$id) {
        sendErrorResponse('재산 ID가 필요합니다.', 400);
    }
    
    $name = getInput('name');
    $category = getInput('category');
    $latitude = getInput('latitude');
    $longitude = getInput('longitude');
    
    if (!$name || !$category || !$latitude || !$longitude) {
        sendErrorResponse('필수 항목을 입력해주세요.', 400);
    }
    
    $sub_category = getInput('sub_category');
    $address = getInput('address');
    $dong = getInput('dong');
    $area = getInput('area');
    $price = getInput('price');
    $capacity = getInput('capacity');
    $status = getInput('status', '정상');
    $description = getInput('description');
    $manager = getInput('manager');
    $contact = getInput('contact');
    $vr_aerial_url = getInput('vr_aerial_url');
    $vr_ground_url = getInput('vr_ground_url');
    $youtube_url = getInput('youtube_url');
    
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
    
    // [수정] 이미지 수정 시 기존 파일 삭제는 선택 사항입니다.
    // 보통은 DB 연결만 끊고 파일은 남겨두거나, 별도 정리 스크립트를 쓰지만,
    // 여기서 완벽하게 하려면 기존 이미지 목록을 조회해서 파일 삭제 후 DB 삭제를 해야 합니다.
    // 현재는 DB 연결만 끊고 새로 덮어쓰는 방식입니다. (수정 시에는 파일 삭제 안 함)
    
    $deleteSql = "DELETE FROM " . table('asset_images') . " WHERE asset_id = :asset_id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute(['asset_id' => $id]);
    
    $images = json_decode(getInput('images', '[]'), true);
    if (!empty($images)) {
        saveAssetImages($pdo, $id, $images);
    }
    
    sendSuccessResponse([
        'asset_id' => $id,
        'message' => '재산이 수정되었습니다.'
    ]);
}

/**
 * [중요] 재산 삭제 (이미지 파일 포함)
 */
function deleteAsset($pdo) {
    $id = getInput('id');
    
    if (!$id) {
        sendErrorResponse('재산 ID가 필요합니다.', 400);
    }
    
    // 1. 삭제할 이미지 목록 조회
    $stmt = $pdo->prepare("SELECT image_url FROM " . table('asset_images') . " WHERE asset_id = :id");
    $stmt->execute(['id' => $id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. 서버 파일 삭제
    // 업로드 경로: /jesan/uploads/assets/ (웹 경로) -> ../uploads/assets/ (서버 경로)
    $uploadDir = dirname(__DIR__) . '/uploads/assets/';
    
    foreach ($images as $imageUrl) {
        // URL에서 파일명만 추출 (예: /jesan/uploads/assets/abc.jpg -> abc.jpg)
        $fileName = basename($imageUrl);
        $filePath = $uploadDir . $fileName;
        
        // 파일이 존재하면 삭제
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // 3. DB에서 이미지 레코드 삭제 (DB 외래키 설정에 따라 자동 삭제될 수도 있음)
    $deleteImgSql = "DELETE FROM " . table('asset_images') . " WHERE asset_id = :id";
    $stmtImg = $pdo->prepare($deleteImgSql);
    $stmtImg->execute(['id' => $id]);
    
    // 4. 재산 데이터 삭제
    $sql = "DELETE FROM " . table('assets') . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    
    sendSuccessResponse([
        'message' => '재산과 관련 이미지가 모두 삭제되었습니다.'
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
?>