<?php
require_once __DIR__ . '/SQLconstants.php'; //constants php 파일 불러오기

if (session_status() !== PHP_SESSION_ACTIVE) {  //세션 시작
    session_start();
}

function db_connect(): mysqli { //DB 연결, 연결 객체 반환
    global $mySQL_host, $mySQL_id, $mySQL_password, $mySQL_database; //constants
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); //오류 예외 처리
    $conn = new mysqli($mySQL_host, $mySQL_id, $mySQL_password, $mySQL_database);
    $conn->set_charset('utf8mb4');
    return $conn;
}

function respond(array $data, int $status = 200): void { //php가 처리한 결과를 javascript에 json 형식으로 반환
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_login(bool $json = true): void { //로그인 상태가 아닐 시 실행
    if (!isset($_SESSION['student_id'])) {
        if ($json) respond(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
        header('Location: /course-registration/login.html');
        exit;
    }
}

function require_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success'=>false, 'message'=>'잘못된 요청 방식입니다.'], 405);
}
