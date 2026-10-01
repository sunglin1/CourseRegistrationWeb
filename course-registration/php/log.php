<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<?php 
	function writeLog(string $message)
	{
		// 로그 파일 이름
		$logFileName = __DIR__ . '/log.txt';
		$logFile = fopen( $logFileName, "a" );
		if( $logFile )
		{
			if (session_status() === PHP_SESSION_NONE) {
            	session_start();
        	}
			// 접속 정보
			$studentId = $_SESSION['student_id'] ?? '-';
			$sessionId = session_id();
			$uri = $_SERVER['PHP_SELF'] ?? '-';
			$previous = $_SERVER['HTTP_REFERER'] ?? '-';
			$browser = $_SERVER['HTTP_USER_AGENT'] ?? '-';
        	$message = str_replace(["\r", "\n"], ' ', $message);
			// 로그 데이터 출력
			$logData = "\nTime:\t" . date('Y-m-d H:i:s')
				. "\tStudentID:\t" . $studentId
				. "\tSessionID:\t" . $sessionId
				. "\tURI:\t" . $uri
				. "\tPrevious:\t" . $previous
				. "\tBrowser:\t" . $browser
				. "\tMessage:\t" . $message;
			// 파일에 로그 기록
			if (flock($logFile, LOCK_EX)) {
				fwrite($logFile, $logData);
				flock($logFile, LOCK_UN);
			}
        	fclose( $logFile );
		}
		else
		{
        	error_log("로그 파일 열기 실패: " . $logFileName);
		}
	}
?>
