<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// 로그아웃 처리
logout();

// 로그인 페이지로 리다이렉션
header('Location: login.php');
exit;
