-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- 호스트: localhost
-- 생성 시간: 25-11-26 11:27
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
  `area` decimal(10,2) DEFAULT NULL COMMENT '면적(㎡)',
  `capacity` int(11) DEFAULT NULL COMMENT '수용인원',
  `status` enum('정상','점검중','사용불가') COLLATE utf8mb4_unicode_ci DEFAULT '정상' COMMENT '상태',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '설명',
  `manager` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '관리부서',
  `contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '연락처',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `jesan_assets`
--

INSERT INTO `jesan_assets` (`id`, `name`, `category`, `sub_category`, `latitude`, `longitude`, `address`, `area`, `capacity`, `status`, `description`, `manager`, `contact`, `created_at`, `updated_at`) VALUES
(1, '강남구민회관', '시설', '문화시설', 37.51720000, 127.04730000, '서울특별시 강남구 학동로 426', 5000.00, 500, '정상', '다양한 문화 행사와 공연이 열리는 구민회관입니다.', '강남구청 문화체육과', '02-3423-5000', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(2, '역삼근린공원', '공원', '근린공원', 37.50100000, 127.03740000, '서울특별시 강남구 역삼동 736', 12000.00, NULL, '정상', '주민들의 휴식 공간으로 활용되는 공원입니다.', '강남구청 공원녹지과', '02-3423-5100', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(3, '강남구청 주민회의실', '시설', '회의실', 37.51720000, 127.04730000, '서울특별시 강남구 학동로 426', NULL, 30, '정상', '주민들이 예약하여 사용할 수 있는 회의실입니다.', '강남구청 민원봉사과', '02-3423-5200', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(4, '선릉역 공영주차장', '시설', '주차장', 37.50450000, 127.04900000, '서울특별시 강남구 선릉로 428', NULL, 150, '점검중', '24시간 운영되는 공영주차장입니다.', '강남구청 주차관리과', '02-3423-5300', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(5, '대치도서관', '건물', '도서관', 37.49570000, 127.06190000, '서울특별시 강남구 도곡로 541', 3500.00, 200, '정상', '지역 주민을 위한 공공도서관입니다.', '강남구청 교육지원과', '02-3423-5400', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(6, '개포체육공원', '공원', '체육공원', 37.48630000, 127.05300000, '서울특별시 강남구 개포동 12', 8500.00, NULL, '정상', '다양한 체육 시설을 갖춘 공원입니다.', '강남구청 체육과', '02-3423-5500', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(7, '삼성동 커뮤니티센터', '시설', '커뮤니티센터', 37.51330000, 127.05920000, '서울특별시 강남구 삼성동 159', 1200.00, 80, '정상', '주민들의 모임과 활동을 위한 공간입니다.', '강남구청 자치행정과', '02-3423-5600', '2025-11-26 09:48:42', '2025-11-26 09:48:42'),
(8, '청담공원', '공원', '근린공원', 37.52270000, 127.05300000, '서울특별시 강남구 청담동 118', 6800.00, NULL, '정상', '청담동 주민들의 산책과 휴식 공간입니다.', '강남구청 공원녹지과', '02-3423-5700', '2025-11-26 09:48:42', '2025-11-26 09:48:42');

-- --------------------------------------------------------

--
-- 테이블 구조 `jesan_asset_images`
--

CREATE TABLE `jesan_asset_images` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL COMMENT '재산 ID',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '이미지 URL',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT '대표 이미지 여부',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`latitude`,`longitude`);

--
-- 테이블의 인덱스 `jesan_asset_images`
--
ALTER TABLE `jesan_asset_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_id` (`asset_id`);

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
-- 테이블의 AUTO_INCREMENT `jesan_assets`
--
ALTER TABLE `jesan_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 테이블의 AUTO_INCREMENT `jesan_asset_images`
--
ALTER TABLE `jesan_asset_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
