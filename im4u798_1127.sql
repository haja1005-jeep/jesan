-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- 호스트: localhost
-- 생성 시간: 25-11-27 00:28
-- 서버 버전: 10.2.44-MariaDB-log
-- PHP 버전: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `im4u798`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_activity_logs`
--

CREATE TABLE `jesan_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT '사용자 ID',
  `action` varchar(100) NOT NULL COMMENT '액션',
  `details` text DEFAULT NULL COMMENT '상세 내용',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP 주소',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_assets`
--

CREATE TABLE `jesan_assets` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '재산명',
  `category` enum('시설','토지','장비','공원','녹지','건물') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '카테고리',
  `sub_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '하위 카테고리',
  `latitude` decimal(10,8) NOT NULL COMMENT '위도',
  `longitude` decimal(11,8) NOT NULL COMMENT '경도',
  `address` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '주소',
  `dong` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '행정동',
  `area` decimal(10,2) DEFAULT NULL COMMENT '면적(㎡)',
  `price` bigint(20) DEFAULT NULL COMMENT '재산 금액(원)',
  `capacity` int(11) DEFAULT NULL COMMENT '수용인원',
  `status` enum('정상','점검중','사용불가') COLLATE utf8mb4_unicode_ci DEFAULT '정상' COMMENT '상태',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '설명',
  `manager` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '관리부서',
  `contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '연락처',
  `vr_aerial_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '360 VR 항공 사진 URL',
  `vr_ground_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '360 VR 지상 사진 URL',
  `youtube_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '유튜브 동영상 URL',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `jesan_assets`
--

INSERT INTO `jesan_assets` (`id`, `name`, `category`, `sub_category`, `latitude`, `longitude`, `address`, `dong`, `area`, `price`, `capacity`, `status`, `description`, `manager`, `contact`, `vr_aerial_url`, `vr_ground_url`, `youtube_url`, `created_at`, `updated_at`) VALUES
(1, '목포시청', '건물', '행정시설', 34.81180000, 126.39220000, '전라남도 목포시 번화로 15', '용해동', 8500.00, 125000000000, 300, '정상', '목포시 행정 업무를 총괄하는 시청사입니다.', '목포시청 총무과', '061-270-2000', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(2, '목포문화예술회관', '시설', '문화시설', 34.79171080, 126.41788430, '전라남도 목포시 남농로 152', '이로동', 12000.00, 85000000000, 1200, '정상', '다양한 공연과 문화행사가 열리는 복합문화공간입니다.', '목포시청 문화예술과', '061-270-8501', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(3, '목포자연사박물관', '시설', '박물관', 34.79365510, 126.42108605, '전라남도 목포시 남농로 135', '이로동', 5600.00, 35000000000, 500, '정상', '자연사 관련 전시와 체험 프로그램을 제공하는 박물관입니다.', '목포시청 문화예술과', '061-274-3655', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 05:17:31'),
(4, '유달산 조각공원', '공원', '도시공원', 34.79433029, 126.37471858, '전라남도 목포시 유달로 187', '유달동', 2340000.00, 120000000000, NULL, '정상', '목포의 상징인 유달산 일대의 근린공원입니다.', '목포시청 공원녹지과', '061-270-8331', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 05:17:31'),
(5, '갓바위문화타운', '시설', '문화시설', 34.79205859, 126.41857655, '전라남도 목포시 갓바위로 249', '이로동', 15000.00, 45000000000, 800, '정상', '전통문화와 현대문화가 어우러진 복합문화공간입니다.', '목포시청 문화예술과', '061-270-8501', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 05:17:31'),
(6, '평화광장', '공원', '광장', 34.79611290, 126.43311100, '전라남도 목포시 평화로 82', '신흥동', 45000.00, 28000000000, NULL, '정상', '각종 행사와 시민들의 휴식공간으로 활용되는 광장입니다.', '목포시청 공원녹지과', '061-270-8330', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 05:17:31'),
(7, '목포시실내체육관', '시설', '체육시설', 34.82529050, 126.40666400, '전라남도 목포시 삼향천로 28', '상동', 8900.00, 65000000000, 3000, '정상', '각종 체육행사와 생활체육 프로그램을 운영하는 체육관입니다.', '목포시청 체육진흥과', '061-270-8671', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(8, '목포실내수영장', '시설', '체육시설', 34.82529043, 126.40666393, '전라남도 목포시 삼향천로 20', '상동', 5200.00, 42000000000, 200, '정상', '시민들이 수영을 즐길 수 있는 실내수영장입니다.', '목포시청 체육진흥과', '061-270-8681', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 05:17:31'),
(9, '목포공공도서관', '건물', '도서관', 34.80727380, 126.37618260, '전라남도 목포시 산정로 119', '산정동', 4800.00, 38000000000, 350, '정상', '지역주민을 위한 공공도서관입니다.', '목포시청 도서관운영팀', '061-270-3652', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(10, '삼학도 공원', '공원', '해양공원', 34.78261020, 126.38999660, '전라남도 목포시 삼학로 92', '삼학동', 180000.00, 95000000000, NULL, '정상', '바다를 접한 아름다운 해양공원입니다.', '목포시청 공원녹지과', '061-270-8332', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 05:17:31'),
(11, '목포청소년수련원', '시설', '교육시설', 34.82216006, 126.41102300, '전라남도 목포시 양을로397번길 6', '상동', 6500.00, 48000000000, 400, '정상', '청소년들의 건전한 활동을 지원하는 수련관입니다.', '목포시청 청소년과', '061-270-8451', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(12, '목포생활도자박물관', '시설', '박물관', 34.79363767, 126.42037564, '전라남도 목포시 남농로 135', '이로동', 3200.00, 22000000000, 300, '정상', '생활도자기의 역사와 문화를 전시하는 박물관입니다.', '목포시청 문화예술과', '061-274-7330', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(13, '목포근대역사관 1관', '건물', '박물관', 34.78757050, 126.38208690, '전라남도 목포시 영산로29번길 6', '유달동', 2800.00, 18000000000, 200, '정상', '목포의 근대역사를 한눈에 볼 수 있는 역사관입니다.', '목포시청 문화예술과', '061-270-8728', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(14, '목포 만호동 회의실', '시설', '회의실', 34.78678924, 126.38718221, '전라남도 목포시 수강로12번길 24(보광동2가)', '동명동', NULL, 5000000000, 50, '정상', '시민들이 예약하여 사용할 수 있는 회의실입니다.', '목포시청 민원봉사과', '061-270-2100', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(15, '목포문화원', '시설', '문화시설', 34.78883068, 126.38620170, '전라남도 목포시 영산로 128', '동명동', 4200.00, 25000000000, 250, '정상', '지역 문화 보존과 전승을 위한 문화원입니다.', '목포문화원', '061-242-1195', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(16, '목포진 역사공원', '공원', '역사공원', 34.78578990, 126.38450158, '전라남도 목포시 수문로 27', '동명동', 25000.00, 15000000000, NULL, '정상', '목포진 성터를 보존한 역사공원입니다.', '목포시청 문화예술과', '061-270-8501', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(17, '목포종합경기장', '시설', '체육시설', 34.82636000, 126.39679280, '전라남도 전남 목포시 대양산단로 23', '삼향동', 35000.00, 125000000000, 8000, '정상', '각종 체육행사가 열리는 종합운동장입니다.', '목포시청 체육진흥과', '061-270-8670', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(18, '목포어린이바다과학관', '시설', '과학관', 34.78190139, 126.38851261, '전라남도 목포시 삼학로 92', '만호동', 4500.00, 32000000000, 400, '정상', '어린이들이 해양과학을 체험할 수 있는 과학관입니다.', '목포시청 문화예술과', '061-270-8405', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(19, '목포 평화의 소녀상', '시설', '기념물', 34.78821140, 126.38340229, '전라남도 목포시 대의동2가 1-74 평화의소녀상', '유달동', NULL, 500000000, NULL, '정상', '역사를 기억하고 평화를 기원하는 소녀상입니다.', '목포시청 문화예술과', NULL, NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38'),
(20, '목포대학교 평생교육원', '건물', '교육시설', 34.80789308, 126.40893357, '전라남도 목포시 송림로41번길 11', '용해동', 3500.00, 28000000000, 150, '점검중', '지역주민을 위한 평생교육 프로그램을 운영합니다.', '목포해양대학교', '061-240-7114', NULL, NULL, NULL, '2025-11-26 04:51:55', '2025-11-26 14:21:38');

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_asset_images`
--

CREATE TABLE `jesan_asset_images` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL COMMENT '재산 ID',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '이미지 URL',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT '대표 이미지 여부',
  `display_order` int(11) DEFAULT 0 COMMENT '표시 순서',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `jesan_asset_images`
--

INSERT INTO `jesan_asset_images` (`id`, `asset_id`, `image_url`, `is_primary`, `display_order`, `created_at`) VALUES
(1, 1, 'https://via.placeholder.com/800x600/667eea/ffffff?text=목포시청', 1, 0, '2025-11-26 22:52:36'),
(2, 2, 'https://via.placeholder.com/800x600/667eea/ffffff?text=목포문화예술회관', 1, 0, '2025-11-26 22:52:36'),
(3, 2, 'https://via.placeholder.com/800x600/764ba2/ffffff?text=대공연장', 0, 0, '2025-11-26 22:52:36');

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_bookings`
--

CREATE TABLE `jesan_bookings` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL COMMENT '재산 ID',
  `user_id` int(11) NOT NULL COMMENT '사용자 ID',
  `booking_date` date NOT NULL COMMENT '예약 날짜',
  `start_time` time DEFAULT NULL COMMENT '시작 시간',
  `end_time` time DEFAULT NULL COMMENT '종료 시간',
  `purpose` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '사용 목적',
  `status` enum('신청','승인','거부','취소','완료') COLLATE utf8mb4_unicode_ci DEFAULT '신청' COMMENT '상태',
  `admin_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '관리자 메모',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `jesan_bookings`
--

INSERT INTO `jesan_bookings` (`id`, `asset_id`, `user_id`, `booking_date`, `start_time`, `end_time`, `purpose`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2025-11-29', '14:00:00', '17:00:00', '지역 주민 모임', '신청', NULL, '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(2, 3, 2, '2025-12-01', '10:00:00', '12:00:00', '동호회 회의', '승인', NULL, '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(3, 1, 3, '2025-12-03', '19:00:00', '21:00:00', '문화 행사', '신청', NULL, '2025-11-26 09:48:42', '2025-11-26 09:48:42');

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_reviews`
--

CREATE TABLE `jesan_reviews` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL COMMENT '재산 ID',
  `user_id` int(11) NOT NULL COMMENT '사용자 ID',
  `booking_id` int(11) DEFAULT NULL COMMENT '예약 ID',
  `rating` int(11) NOT NULL COMMENT '평점 (1-5)',
  `comment` text DEFAULT NULL COMMENT '리뷰 내용',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `jesan_reviews`
--

INSERT INTO `jesan_reviews` (`id`, `asset_id`, `user_id`, `booking_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, NULL, 5, '시설이 깨끗하고 직원분들이 친절합니다.', '2025-11-26 09:48:42'),
(2, 2, 3, NULL, 4, '가족과 함께 산책하기 좋은 공원입니다.', '2025-11-26 09:48:42'),
(3, 5, 2, NULL, 5, '도서관이 넓고 쾌적합니다. 자주 이용하고 있습니다.', '2025-11-26 09:48:42');

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_users`
--

CREATE TABLE `jesan_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '아이디',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '비밀번호 (해시)',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '이름',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '전화번호',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '이메일',
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user' COMMENT '권한',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `jesan_users`
--

INSERT INTO `jesan_users` (`id`, `username`, `password`, `name`, `phone`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '관리자', NULL, 'admin@example.com', 'admin', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(2, 'user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '김철수', '010-1234-5678', 'user1@example.com', 'user', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(3, 'user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '이영희', '010-2345-6789', 'user2@example.com', 'user', '2025-11-26 09:48:42', '2025-11-26 09:48:42');

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `jesan_activity_logs`
--
ALTER TABLE `jesan_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- 테이블의 인덱스 `jesan_assets`
--
ALTER TABLE `jesan_assets`
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_dong` (`dong`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`latitude`,`longitude`);

--
-- 테이블의 인덱스 `jesan_asset_images`
--
ALTER TABLE `jesan_asset_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_display_order` (`asset_id`,`display_order`);

--
-- 테이블의 인덱스 `jesan_bookings`
--
ALTER TABLE `jesan_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_date` (`asset_id`,`booking_date`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_booking_date` (`booking_date`);

--
-- 테이블의 인덱스 `jesan_reviews`
--
ALTER TABLE `jesan_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_rating` (`rating`);

--
-- 테이블의 인덱스 `jesan_users`
--
ALTER TABLE `jesan_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `jesan_activity_logs`
--
ALTER TABLE `jesan_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `jesan_asset_images`
--
ALTER TABLE `jesan_asset_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 테이블의 AUTO_INCREMENT `jesan_bookings`
--
ALTER TABLE `jesan_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 테이블의 AUTO_INCREMENT `jesan_reviews`
--
ALTER TABLE `jesan_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 테이블의 AUTO_INCREMENT `jesan_users`
--
ALTER TABLE `jesan_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
