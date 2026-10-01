# CourseRegistrationWeb

2026-02 웹서버개발 팀플

JSP와 PHP를 이용한 수강신청 웹사이트 프로젝트입니다.

MySQL의 `CourseDB` 데이터베이스를 사용하며, AWS EC2 서버에서 실행할 수 있습니다.

---

## 1. 프로젝트 구조

```text
CourseRegistrationWeb/
├── README.md
├── index.php
├── login.html
├── login.css
├── login.js
├── script.js
├── style.css
└── course-registration/
    ├── jsp/
    │   ├── search.jsp
    │   ├── SQLconstants.example.jsp
    │   └── ...
    └── php/
        ├── SQLconstants.example.php
        ├── db.php
        ├── login.php
        ├── logout.php
        ├── selectSQL.php
        ├── myCourseSQL.php
        ├── insertSQL.php
        ├── deleteSQL.php
        └── ...
```

- `course-registration/jsp/`: JSP 버전의 서버 코드
- `course-registration/php/`: PHP 버전의 서버 코드
- `login.html`, `index.php`: PHP 버전의 로그인 및 메인 페이지
- `login.js`, `script.js`: PHP 서버와 통신하는 JavaScript 코드

---

## 2. JSP 버전

### 2.1 실행 환경

JSP 버전은 Apache Tomcat 서버에서 실행합니다.

필요한 환경은 다음과 같습니다.

- Java
- Apache Tomcat 10
- MySQL
- JDBC 드라이버

### 2.2 GitHub 프로젝트 최초 배포

AWS EC2 Ubuntu 서버에 SSH로 접속합니다.

```bash
ssh -i "키파일.pem" ubuntu@[서버-IP]
```

Tomcat이 설치되어 있고 `/var/lib/tomcat10/webapps/ROOT/` 경로를 사용하는 환경을 기준으로 합니다.

프로젝트를 배포할 디렉터리로 이동합니다.

```bash
cd /var/lib/tomcat10/webapps/ROOT
```

GitHub 저장소를 복제합니다.

```bash
sudo git clone https://github.com/sunglin1/CourseRegistrationWeb.git
```

프로젝트 디렉터리로 이동합니다.

```bash
cd CourseRegistrationWeb
```

복제가 완료되면 다음 위치에 프로젝트가 생성됩니다.

```text
/var/lib/tomcat10/webapps/ROOT/CourseRegistrationWeb
```

JSP 파일은 다음 위치에 있습니다.

```text
/var/lib/tomcat10/webapps/ROOT/CourseRegistrationWeb/course-registration/jsp/
```

Tomcat에서 프로젝트를 제공하려면 해당 디렉터리가 Tomcat 웹 애플리케이션으로 인식되고, 필요한 JDBC 드라이버와 DB 설정이 준비되어 있어야 합니다.

### 2.3 DB 설정

JSP 버전은 `SQLconstants.jsp` 파일에서 MySQL 접속 정보를 관리합니다.

프로젝트의 JSP 폴더로 이동합니다.

```bash
cd /var/lib/tomcat10/webapps/ROOT/CourseRegistrationWeb/course-registration/jsp
```

예시 파일을 복사합니다.

```bash
sudo cp SQLconstants.example.jsp SQLconstants.jsp
```

설정 파일을 엽니다.

```bash
sudo nano SQLconstants.jsp
```

자신의 MySQL 환경에 맞게 정보를 수정합니다.

```jsp
<%
    // MySQL ID
    final String mySQL_id = "YOUR_MYSQL_ID";

    // MySQL Password
    final String mySQL_password = "YOUR_MYSQL_PASSWORD";

    // MySQL Database
    final String mySQL_database = "jdbc:mysql://localhost:3306/CourseDB?useUnicode=true&characterEncoding=UTF-8&serverTimezone=Asia/Seoul";

    // JDBC Driver
    final String jdbc_driver = "com.mysql.jdbc.Driver";
%>
```

저장 방법:

`Ctrl + O` → `Enter` → `Ctrl + X`

`SQLconstants.jsp`에는 실제 데이터베이스 접속 정보가 포함되므로 GitHub에 업로드하지 않습니다.

`.gitignore`에는 다음 항목을 등록합니다.

```gitignore
course-registration/jsp/SQLconstants.jsp
```

### 2.4 서버 접속

JSP 버전의 접속 주소는 다음과 같습니다.

```text
http://[서버-IP]:8080/CourseRegistrationWeb/course-registration/jsp/search.jsp
```

`[서버-IP]`에 AWS EC2 서버의 Public IP 주소를 입력합니다.

Tomcat이 8080번 포트에서 실행 중이어야 합니다.

### 2.5 서버 업데이트 방법

로컬에서 코드를 수정한 뒤 GitHub에 Push합니다.

AWS EC2 서버에서 JSP 프로젝트 디렉터리로 이동합니다.

```bash
cd /var/lib/tomcat10/webapps/ROOT/CourseRegistrationWeb
```

최신 변경사항을 반영합니다.

```bash
sudo git pull
```

변경된 내용을 확인하려면 브라우저를 새로고침합니다.

### 2.6 로컬 환경 실행 (Optional)

로컬에 Java, Tomcat, MySQL 환경을 구성한 경우 다음 주소에서 실행할 수 있습니다.

```text
http://localhost:8080/CourseRegistrationWeb/course-registration/jsp/search.jsp
```

로컬 MySQL에도 `CourseDB` 데이터베이스가 구성되어 있어야 합니다.

---

## 3. PHP 버전

### 3.1 실행 환경

PHP 버전은 Apache HTTP Server에서 실행합니다.

필요한 환경은 다음과 같습니다.

- Apache HTTP Server
- PHP
- MySQL
- PHP MySQL 확장 (`mysqli`)

### 3.2 GitHub 프로젝트 최초 배포

AWS EC2 Ubuntu 서버에 SSH로 접속합니다.

```bash
ssh -i "키파일.pem" ubuntu@[서버-IP]
```

Apache 웹 서버의 기본 디렉터리로 이동합니다.

```bash
cd /var/www/html
```

GitHub 저장소를 복제합니다.

```bash
sudo git clone https://github.com/sunglin1/CourseRegistrationWeb.git
```

프로젝트 디렉터리로 이동합니다.

```bash
cd CourseRegistrationWeb
```

복제가 완료되면 다음 위치에 프로젝트가 생성됩니다.

```text
/var/www/html/CourseRegistrationWeb
```

PHP 서버 코드는 다음 위치에 있습니다.

```text
/var/www/html/CourseRegistrationWeb/course-registration/php/
```

### 3.3 DB 설정

PHP 버전은 `SQLconstants.php` 파일에서 MySQL 접속 정보를 관리합니다.

PHP 폴더로 이동합니다.

```bash
cd /var/www/html/CourseRegistrationWeb/course-registration/php
```

예시 파일을 복사합니다.

```bash
sudo cp SQLconstants.example.php SQLconstants.php
```

설정 파일을 엽니다.

```bash
sudo nano SQLconstants.php
```

MySQL 환경에 맞게 정보를 수정합니다.

```php
<?php
    // MySQL 서버 주소
    $mySQL_host = "localhost";

    // MySQL ID
    $mySQL_id = "YOUR_MYSQL_ID";

    // MySQL Password
    $mySQL_password = "YOUR_MYSQL_PASSWORD";

    // MySQL Database
    $mySQL_database = "CourseDB";
?>
```

저장 방법:

`Ctrl + O` → `Enter` → `Ctrl + X`

`SQLconstants.php`에는 실제 데이터베이스 접속 정보가 포함되므로 GitHub에 업로드하지 않습니다.

`.gitignore`에는 다음 항목을 등록합니다.

```gitignore
course-registration/php/SQLconstants.php
```

### 3.4 서버 접속

PHP 버전의 로그인 페이지 주소는 다음과 같습니다.

```text
http://[서버-IP]/CourseRegistrationWeb/login.html
```

`[서버-IP]`에 AWS EC2 서버의 Public IP 주소를 입력합니다.

로그인 후 수강신청 메인 페이지로 이동합니다.

### 3.5 서버 업데이트 방법

로컬에서 기능 개발 및 테스트가 완료되면 GitHub에 변경사항을 Push합니다.

AWS EC2 서버에서 프로젝트 디렉터리로 이동합니다.

```bash
cd /var/www/html/CourseRegistrationWeb
```

GitHub의 최신 변경사항을 서버에 반영합니다.

```bash
sudo git pull
```

업데이트가 완료되면 브라우저에서 변경사항을 확인합니다.

### 3.6 로컬 환경 실행 (Optional)

로컬에서 PHP 코드를 수정하고 테스트하려면 Apache, PHP, MySQL 환경을 구성합니다.

프로젝트를 Apache 웹 루트에 배치한 경우 다음 주소로 접속할 수 있습니다.

```text
http://localhost/CourseRegistrationWeb/login.html
```

로컬 MySQL에 `CourseDB`를 생성하고 `SQLconstants.php`의 접속 정보를 설정해야 합니다.

---

## 4. 개발 및 배포 흐름

### JSP 버전

```text
VS Code에서 JSP 코드 수정
        ↓
(로컬 Tomcat에서 테스트)
        ↓
Git add / commit / push
        ↓
AWS EC2 서버에 SSH 접속
        ↓
Tomcat 프로젝트 디렉터리에서 git pull
        ↓
JSP 페이지 확인 (:8080)
```

### PHP 버전

```text
VS Code에서 PHP / JavaScript 코드 수정
        ↓
(로컬 Apache + PHP에서 테스트)
        ↓
Git add / commit / push
        ↓
AWS EC2 서버에 SSH 접속
        ↓
Apache 프로젝트 디렉터리에서 git pull
        ↓
PHP 페이지 확인 (80번 포트)
```
