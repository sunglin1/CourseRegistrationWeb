
<?php
require_once __DIR__ . '/db.php';

// 로그인 여부 확인
require_login();

try {
    // MySQL 드라이버 연결
    $conn = db_connect();

    // 현재 로그인한 학생의 수강신청 내역 조회 SQL문
    $query = "SELECT c.course_section_id AS id,
                     c.course_name AS name,
                     c.course_type AS type,
                     c.completion_type AS completion,
                     d.department_name AS area,
                     c.credit,
                     c.professor_name AS professor,
                     c.class_time AS time,
                     c.classroom
              FROM enrollment e
              JOIN course c ON e.course_section_id = c.course_section_id
              JOIN department d ON c.department_id = d.department_id
              WHERE e.student_id = ?
              ORDER BY e.registered_at, c.course_section_id";

    // SQL문 준비 및 학생 학번 전달
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['student_id']);

    // MySQL 수강신청 내역 조회 실행
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // MySQL 드라이버 연결 해제
    $stmt->close();
    $conn->close();

    // 조회 결과를 JavaScript로 전달
    respond(['success' => true, 'courses' => $courses]);

} catch (Throwable $e) {
    // 오류 발생 시 기록 및 실패 메시지 전달
    error_log($e->getMessage());
    respond(['success' => false, 'message' => '신청 내역 조회에 실패했습니다.'], 500);
}
