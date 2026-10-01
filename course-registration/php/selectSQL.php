
<?php
require_once __DIR__ . '/db.php';

require_login();

try {
    // 이전 페이지에서 전달받은 검색 조건
    $type = trim((string)($_GET['type'] ?? ''));
    $completion = trim((string)($_GET['completion'] ?? ''));
    $department = trim((string)($_GET['department'] ?? ''));
    $name = trim((string)($_GET['name'] ?? ''));
    $professor = trim((string)($_GET['professor'] ?? ''));

    // MySQL 강좌 조회 SQL문 작성
    $sql = 'SELECT c.course_section_id AS id,
                   c.course_name AS name,
                   c.course_type AS type,
                   c.completion_type AS completion,
                   d.department_name AS area,
                   c.credit,
                   c.professor_name AS professor,
                   c.class_time AS time,
                   c.classroom
            FROM course c
            JOIN department d ON c.department_id = d.department_id
            WHERE 1=1';

    $params = [];

    // 전공/교양, 이수구분, 학과 검색 조건 추가
    foreach (['c.course_type' => $type,
              'c.completion_type' => $completion,
              'd.department_name' => $department] as $column => $value) {
        if ($value !== '') {
            $sql .= " AND $column = ?";
            $params[] = $value;
        }
    }

    // 교과목명 검색 조건 추가
    if ($name !== '') {
        $sql .= ' AND c.course_name LIKE ?';
        $params[] = '%' . $name . '%';
    }

    // 교수명 검색 조건 추가
    if ($professor !== '') {
        $sql .= ' AND c.professor_name LIKE ?';
        $params[] = '%' . $professor . '%';
    }

    // 강좌 ID 순서대로 정렬
    $sql .= ' ORDER BY c.course_section_id';

    // MySQL 드라이버 연결
    $conn = db_connect();

    // SQL문 준비 및 검색 조건 전달
    $stmt = $conn->prepare($sql);

    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }

    // MySQL 강좌 조회 실행
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // MySQL 드라이버 연결 해제
    $stmt->close();
    $conn->close();

    // 강좌 검색 로그 기록
    require_once __DIR__ . '/log.php';
    writeLog('강좌 검색');

    // 조회 결과를 JavaScript로 전달
    respond(['success' => true, 'courses' => $courses]);

} catch (Throwable $e) {
    // 오류 발생 시 기록 및 실패 메시지 전달
    error_log($e->getMessage());
    respond(['success' => false, 'message' => '강좌 조회에 실패했습니다.'], 500);
}
