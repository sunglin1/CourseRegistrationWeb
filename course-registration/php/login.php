
<?php
require_once __DIR__ . '/db.php';

require_post();

// 이전 페이지에서 전달받은 학번과 비밀번호
$id = trim((string)($_POST['student_id'] ?? ''));
$password = (string)($_POST['password'] ?? '');

// 학번 또는 비밀번호 미입력 확인
if ($id === '' || $password === '') {
    respond(['success' => false, 'message' => '학번과 비밀번호를 입력해 주세요.'], 400);
}

try {
    // MySQL 드라이버 연결
    $conn = db_connect();

    // 입력한 학번에 해당하는 학생 조회 SQL문
    $query = "SELECT student_id, password_hash
              FROM student
              WHERE student_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);

    // MySQL 학생 정보 조회 실행
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // MySQL 드라이버 연결 해제
    $stmt->close();
    $conn->close();

    // 학번 존재 여부 및 비밀번호 일치 확인
    if (!$user || !password_verify($password, $user['password_hash'])) {
        respond(['success' => false, 'message' => '학번 또는 비밀번호가 올바르지 않습니다.'], 401);
    }

    // 로그인 성공 시 세션 ID 갱신
    session_regenerate_id(true);

    // 로그인한 학생의 학번을 세션에 저장
    $_SESSION['student_id'] = $user['student_id'];

    // 로그인 성공 로그 기록
    require_once __DIR__ . '/log.php';
    writeLog('로그인 성공');

    // 로그인 결과를 JavaScript로 전달
    respond(['success' => true, 'message' => '로그인 성공']);

} catch (Throwable $e) {
    // 오류 발생 시 기록 및 실패 메시지 전달
    error_log($e->getMessage());
    respond(['success' => false, 'message' => '서버 오류가 발생했습니다.'], 500);
}
