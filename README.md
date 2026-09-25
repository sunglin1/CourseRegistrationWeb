# CourseRegistrationWeb
2026-02 웹서버개발 팀플

## DB 설정

MySQL 접속 정보가 포함된 `SQLconstants.jsp` 파일은 보안을 위해 Git에 포함하지 않습니다.

프로젝트를 처음 실행하는 경우 `jsp/SQLconstants.example.jsp` 파일을 복사하여 `jsp/SQLconstants.jsp` 파일을 생성합니다.

```bash
cp jsp/SQLconstants.example.jsp jsp/SQLconstants.jsp
```

생성한 `SQLconstants.jsp`에서 자신의 MySQL 환경에 맞게 ID와 Password를 수정합니다.

```jsp
<%
	// MySQL ID : MySQL 설치시 아이디 ex) root, test, ...
	final String mySQL_id = "YOUR_MYSQL_ID"; 	

	// MySQL Password	
	final String mySQL_password = "YOUR_MYSQL_PASSWORD";
	
	// MySQL Database
	final String mySQL_database = "jdbc:mysql://localhost:3306/CourseDB?useUnicode=true&characterEncoding=UTF-8&serverTimezone=Asia/Seoul"; 

	// JDBC Driver : ex) com.mysql.jdbc.Driver, org.gjt.mm.mysql.Driver
	final String jdbc_driver = "com.mysql.jdbc.Driver"; 
%>
```

`SQLconstants.jsp`에는 실제 MySQL 접속 정보가 포함되므로 Git에 커밋하지 않습니다.

`.gitignore`에 다음 항목이 등록되어 있습니다.

```gitignore
jsp/SQLconstants.jsp
```
