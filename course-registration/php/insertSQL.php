
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

$conn = null;

try {
    // MySQL 드라이버 연결
    $conn = db_connect();

    // 수강신청 처리 시작
    $conn->begin_transaction();

    // 동일 학생의 동시 수강신청 방지
    $query = "SELECT student_id FROM student
              WHERE student_id = ? FOR UPDATE";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['student_id']);
    $stmt->execute();
    $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 신청하려는 강좌 정보 확인
    $query = "SELECT course_name, credit, capacity FROM course
              WHERE course_section_id = ? FOR UPDATE";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$course) {
        $conn->rollback();
        respond(['success' => false, 'message' => '존재하지 않는 강좌입니다.'], 404);
    }

    // 이미 신청한 강좌인지 확인
    $query = "SELECT 1 FROM enrollment
              WHERE student_id = ? AND course_section_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $_SESSION['student_id'], $id);
    $stmt->execute();
    $duplicate = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($duplicate) {
        $conn->rollback();
        respond(['success' => false, 'message' => '이미 신청한 강좌입니다.'], 409);
    }

    // 현재 신청한 총 학점 계산
    $query = "SELECT COALESCE(SUM(c.credit), 0) AS total
              FROM enrollment e
              JOIN course c ON e.course_section_id = c.course_section_id
              WHERE e.student_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_SESSION['student_id']);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // 18학점 초과 여부 확인
    if ($total + (int)$course['credit'] > 18) {
        $conn->rollback();
        respond(['success' => false, 'message' => '신청 가능 학점 18학점을 초과할 수 없습니다.'], 409);
    }

    // 강좌 정원 확인
    $query = "SELECT COUNT(*) AS count FROM enrollment
              WHERE course_section_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    if ($count >= (int)$course['capacity']) {
        $conn->rollback();
        respond(['success' => false, 'message' => '강좌 정원이 마감되었습니다.'], 409);
    }

    // MySQL 수강신청 추가 실행
    $query = "INSERT INTO enrollment (student_id, course_section_id)
              VALUES (?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $_SESSION['student_id'], $id);
    $stmt->execute();
    $stmt->close();

    // 수강신청 확정
    $conn->commit();

    // MySQL 드라이버 연결 해제
    $conn->close();

    // 로그 기록
    require_once __DIR__ . '/log.php';
    writeLog("수강신청: " . $id);

    // 수강신청 결과 전달
    $message = $course['course_name'] . " 수강신청이 완료되었습니다.";
    respond(['success' => true, 'message' => $message]);

} catch (Throwable $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            error_log($rollbackError->getMessage());
        }
    }

    error_log($e->getMessage());
    respond(['success' => false, 'message' => '수강신청 처리 중 오류가 발생했습니다.'], 500);
}
