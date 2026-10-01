
<?php
require_once __DIR__ . '/db.php';

require_post();

// 로그아웃 기록
require_once __DIR__ . '/log.php';
writeLog('로그아웃');

// 세션에 저장된 학생 정보 삭제
$_SESSION = [];

// 브라우저에 저장된 세션 쿠키 삭제
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();

    setcookie(
        session_name(), '',
        time() - 42000,
        $p['path'],
        $p['domain'],
        $p['secure'],
        $p['httponly']
    );
}

// 서버의 세션 종료
session_destroy();

// 로그아웃 결과를 JavaScript로 전달
respond(['success' => true]);
