<?php
require_once __DIR__ . '/db.php';

// 로그인 여부와 POST 요청 확인
require_login();
require_post();

// 이전 페이지에서 전달받은 강좌 ID
$id = trim((string)($_POST['course_id'] ?? ''));

if ($id === '') {
    respond(['success' => false, 'message' => '강좌를 선택해 주세요.'], 400);
}

try {
    // MySQL 연결
    $conn = db_connect();

    // 수강신청 삭제 SQL
    $query = "DELETE FROM enrollment
              WHERE student_id = ? AND course_section_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $_SESSION['student_id'], $id);

    // SQL 실행
    $stmt->execute();
    $deleted = $stmt->affected_rows;

    // MySQL 연결 종료
    $stmt->close();
    $conn->close();

    // 삭제 결과 확인
    if ($deleted === 0) {
        respond(['success' => false, 'message' => '신청 내역을 찾을 수 없습니다.'], 404);
    }

    // 로그 기록
    require_once __DIR__ . '/log.php';
    writeLog("수강취소: " . $id);

    // JavaScript로 결과 전달
    respond(['success' => true, 'message' => '수강신청이 취소되었습니다.']);

} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(['success' => false, 'message' => '수강취소 처리 중 오류가 발생했습니다.'], 500);
}