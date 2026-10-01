<!-- 로그인 정보 가져오기 -->
<?php
require_once __DIR__ . '/course-registration/php/db.php'; //php 파일 불러오기

require_login(false); //로그인 여부 확인

$conn = db_connect(); //DB 연결

//학생 정보 가져오기
$stmt = $conn->prepare('SELECT s.student_name, s.grade, d.department_name FROM student s JOIN department d ON s.department_id=d.department_id WHERE s.student_id=?'); //학생 정보 가져오기
$stmt->bind_param('s', $_SESSION['student_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

//학생 정보가 없으면 로그아웃 처리
if (!$student) { 
    header('Location: /course-registration/php/logout.php'); 
    exit; 
}

function h($v) { 
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>상명대학교 수강신청</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="page-container">


        <!-- =========================
             사용자 정보
        ========================== -->

        <header class="user-card">

            <div class="profile-area">

                <div class="profile-icon">
                    👤
                </div>

                <div>
                    <p class="small-title">
                        상명대학교 수강신청
                    </p>

                    <h1>
                        사용자정보
                    </h1>
                </div>

            </div>


            <div class="user-info">

                <div>
                    <span>이름</span>
                    <strong>홍길동</strong>
                </div>

                <div>
                    <span>학과</span>
                    <strong>게임전공</strong>
                </div>

                <div>
                    <span>학년</span>
                    <strong>3학년</strong>
                </div>

                <div>
                    <span>신청 가능 학점</span>
                    <strong>18학점</strong>
                </div>

            </div>


            <button
                id="logoutButton"
                class="logout-button">

                로그아웃

            </button>

        </header>



        <!-- =========================
             메인 작업 영역
        ========================== -->

        <main class="workspace-grid">


            <!-- =========================
                 ① 강좌 조회
            ========================== -->

            <section class="card search-section">

                <div class="section-header">

                    <div>
                        <p class="section-number">
                            01
                        </p>

                        <h2>
                            강좌 조회
                        </h2>
                    </div>

                </div>


                <!-- 전공 / 교양 -->

                <div class="course-tabs">

                    <button
                        class="tab-button active"
                        data-type="전공">

                        전공강좌

                    </button>


                    <button
                        class="tab-button"
                        data-type="교양">

                        교양강좌

                    </button>

                </div>


                <!-- 조회 조건 -->

                <div class="search-form">

                    <label for="completionType">
                        이수구분
                    </label>

                    <select id="completionType">
                    </select>


                    <label for="courseArea">
                        영역
                    </label>

                    <select id="courseArea">
                    </select>


                    <button
                        id="viewButton"
                        class="primary-button view-button">

                        조회

                    </button>



                    <!-- 상세 검색 -->

                    <div class="search-divider">

                        <span>
                            상세 검색
                        </span>

                    </div>


                    <label for="courseName">
                        교과목명
                    </label>

                    <input
                        type="text"
                        id="courseName"
                        placeholder="과목명을 입력하세요">


                    <label for="professorName">
                        교수명
                    </label>

                    <input
                        type="text"
                        id="professorName"
                        placeholder="교수명을 입력하세요">


                    <div class="button-group">

                        <button
                            id="resetButton"
                            class="secondary-button">

                            초기화

                        </button>


                        <button
                            id="searchButton"
                            class="primary-button">

                            검색

                        </button>

                    </div>

                </div>

            </section>



            <!-- =========================
                 오른쪽 영역
                 ② 검색 결과
                 ③ 수강신청 내역
            ========================== -->

            <div class="right-column">


                <!-- =========================
                     ② 검색 결과
                ========================== -->

                <section class="card result-section">

                    <div class="section-header">

                        <div>

                            <p class="section-number">
                                02
                            </p>

                            <h2>
                                검색 결과
                            </h2>

                        </div>


                        <span
                            id="resultCount"
                            class="result-count">

                            0개 강좌

                        </span>

                    </div>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>과목명</th>
                                    <th>구분</th>
                                    <th>학점</th>
                                    <th>교수</th>
                                    <th>수업시간</th>
                                    <th>강의실</th>
                                    <th>신청</th>
                                </tr>

                            </thead>


                            <tbody id="resultTable">

                                <!-- JavaScript가 표시 -->

                            </tbody>

                        </table>

                    </div>

                </section>



                <!-- =========================
                     ③ 수강신청 내역
                ========================== -->

                <section class="card enrollment-section">

                    <div class="section-header">

                        <div>

                            <p class="section-number">
                                03
                            </p>

                            <h2>
                                수강신청 내역
                            </h2>

                        </div>


                        <div class="credit-box">

                            총 신청학점

                            <strong id="totalCredit">
                                0
                            </strong>

                            학점

                        </div>

                    </div>


                    <div class="table-wrapper enrollment-table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>과목명</th>
                                    <th>구분</th>
                                    <th>학점</th>
                                    <th>교수</th>
                                    <th>수업시간</th>
                                    <th>강의실</th>
                                    <th>관리</th>
                                </tr>

                            </thead>


                            <tbody id="enrollmentTable">

                                <!-- JavaScript가 표시 -->

                            </tbody>

                        </table>


                        <div
                            id="emptyMessage"
                            class="empty-message">

                            아직 신청한 강좌가 없습니다.

                        </div>

                    </div>

                </section>

            </div>

        </main>

    </div>



    <!-- =========================
         알림 메시지
    ========================== -->

    <div
        id="toast"
        class="toast">

        수강신청이 완료되었습니다.

    </div>


    <script src="script.js"></script>

</body>

</html>