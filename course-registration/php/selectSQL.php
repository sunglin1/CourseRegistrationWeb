
<?php
require_once __DIR__ . '/db.php';

require_login();

try {
    // 드롭다운 옵션 조회
    if (($_GET['action'] ?? '') === 'options') {
        $conn = db_connect();

        // 전공, 교직, md, 교선, 교필, 일선
        $types = $conn->query("
            SELECT DISTINCT course_type
            FROM course
            WHERE course_type IS NOT NULL
            ORDER BY course_type
        ")->fetch_all(MYSQLI_ASSOC);

        // 전공 이수구분
        $majorCompletions = $conn->query("
            SELECT DISTINCT completion_type AS value
            FROM course
            WHERE course_type = '전공'
              AND completion_type IS NOT NULL
              AND completion_type <> ''
            ORDER BY completion_type
        ")->fetch_all(MYSQLI_ASSOC);

        // 교양 영역
        $generalAreas = $conn->query("
            SELECT DISTINCT completion_type AS value
            FROM course
            WHERE course_type <> '전공'
              AND completion_type IS NOT NULL
              AND completion_type <> ''
            ORDER BY completion_type
        ")->fetch_all(MYSQLI_ASSOC);

        // 전공 학과
        $departments = $conn->query("
            SELECT DISTINCT
                d.department_name AS value
            FROM course c
            JOIN department d
              ON c.department_id = d.department_id
            WHERE c.course_type = '전공'
            ORDER BY d.department_name
        ")->fetch_all(MYSQLI_ASSOC);

        $conn->close();

        respond([
            'success' => true,
            'types' => array_column($types, 'course_type'),
            'majorCompletions' =>
                array_column($majorCompletions, 'value'),
            'generalAreas' =>
                array_column($generalAreas, 'value'),
            'departments' =>
                array_column($departments, 'value')
        ]);
        exit;
    }
    // 이전 페이지에서 전달받은 검색 조건
    $type = trim((string)($_GET['type'] ?? ''));
    $completion = trim((string)($_GET['completion'] ?? ''));
    $department = trim((string)($_GET['department'] ?? ''));
    $group = trim((string)($_GET['group'] ?? ''));
    $name = trim((string)($_GET['name'] ?? ''));
    $professor = trim((string)($_GET['professor'] ?? ''));

    
    // 페이지당 20개씩 조회
    $pageSize = 20;

    // 현재 페이지 번호
    $page = max(1, (int)($_GET['page'] ?? 1));

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

    // 전공 / 교양 구분
    if ($group === '전공') {
        $sql .= " AND c.course_type = '전공'";
    } elseif ($group === '교양') {
        $sql .= " AND c.course_type IN (
            '교직', 'MD', '교선', '교필', '일선'
        )";
    }

    // 유형 / 이수구분 / 학과 조건
    foreach ([
        'c.course_type' => $type,
        'c.completion_type' => $completion,
        'd.department_name' => $department
    ] as $column => $value) {
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

    
    // DB 연결
    $conn = db_connect();

    // 검색 조건에 맞는 전체 강좌 수 조회
    $countSql = 'SELECT COUNT(*) AS total
                 FROM course c
                 JOIN department d
                   ON c.department_id = d.department_id
                 WHERE 1=1';

    // 기존 검색 SQL의 WHERE 조건 재사용
    $wherePosition = strpos($sql, 'WHERE 1=1');
    $countSql .= substr(
        $sql,
        $wherePosition + strlen('WHERE 1=1')
    );
    
    $countStmt = $conn->prepare($countSql);

    // 전체 강좌 수 조회에는 검색 조건만 전달
    if ($params) {
        $countStmt->bind_param(
            str_repeat('s', count($params)),
            ...$params
        );
    }

    $countStmt->execute();

    $totalCourses = (int) $countStmt
        ->get_result()
        ->fetch_assoc()['total'];

    $countStmt->close();

    // 전체 페이지 수
    $totalPages = max(
        1,
        (int) ceil($totalCourses / $pageSize)
    );

    // 존재하지 않는 페이지 번호 보정
    $page = min($page, $totalPages);

    // 조회 시작 위치
    $offset = ($page - 1) * $pageSize;

    // 현재 페이지에 해당하는 20개만 조회
    $sql .= ' ORDER BY c.course_section_id
              LIMIT ? OFFSET ?';

    $params[] = $pageSize;
    $params[] = $offset;

    
    // SQL문 준비
    $stmt = $conn->prepare($sql);

    // 검색 조건은 문자열(s),
    // LIMIT과 OFFSET은 정수(i)
    $types = str_repeat(
        's',
        count($params) - 2
    ) . 'ii';

    // SQL 파라미터 전달
    $stmt->bind_param($types, ...$params);

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
    respond([
        'success' => true,
        'courses' => $courses,

        // 페이지네이션 정보
        'total' => $totalCourses,
        'page' => $page,
        'pageSize' => $pageSize,
        'totalPages' => $totalPages
    ]);


} catch (Throwable $e) {
    // 오류 발생 시 기록 및 실패 메시지 전달
    error_log($e->getMessage());
    respond(['success' => false, 'message' => '강좌 조회에 실패했습니다.'], 500);
}
