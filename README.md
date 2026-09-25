# CourseRegistrationWeb
2026-02 웹서버개발 팀플

## 실행 방법

### 서버 접속

AWS 서버에 배포된 프로젝트는 다음 주소에서 확인할 수 있습니다.

```text
http://[서버-IP]:8080/CourseRegistrationWeb/course-registration/jsp/search.jsp
```

> `[서버-IP]` 부분에 현재 AWS EC2 서버의 Public IP 주소를 입력합니다.

---

### 로컬 환경 실행 (Optional)

로컬에서 코드를 수정하고 바로 테스트하고 싶은 경우 Java, Tomcat, MySQL 등의 개발 환경을 설치하여 사용할 수 있습니다.

로컬 환경이 구성되어 있다면 다음 주소에서 프로젝트를 확인할 수 있습니다.

```text
http://localhost:8080/CourseRegistrationWeb/course-registration/jsp/search.jsp
```

Tomcat 서버가 실행 중이어야 하며, 로컬 MySQL에 프로젝트에서 사용하는 데이터베이스가 구성되어 있어야 합니다.

---

## 서버 업데이트 방법

로컬 환경에서 기능 개발 및 테스트가 완료되면 GitHub에 변경사항을 Push합니다.

그다음 AWS 서버에 접속하여 프로젝트 디렉터리로 이동합니다.

```bash
cd /var/lib/tomcat10/webapps/ROOT/CourseRegistrationWeb
```

GitHub의 최신 변경사항을 서버에 반영합니다.

```bash
sudo git pull
```

업데이트가 완료되면 서버 주소에 접속하거나 브라우저를 새로고침하여 변경사항을 확인합니다.

### 개발 및 배포 흐름

```text
VS Code에서 코드 수정
        ↓
로컬 Tomcat에서 테스트
        ↓
Git add / commit / push
        ↓
AWS 서버에서 git pull
        ↓
서버 페이지에서 최종 확인
```

> `SQLconstants.jsp`에는 데이터베이스 접속 정보가 포함되어 있으므로 Git에 업로드하지 않습니다. 로컬 환경과 AWS 서버에서 각각 별도로 관리합니다.


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
