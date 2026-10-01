const maxCredit = 18;
let selectedType = "전공";  // 처음에는 전공강좌가 선택되어 있음
let enrolledCourses = [];   // 학생이 신청한 과목을 저장하는 배열

// ==========================================
// HTML 요소 가져오기
// ==========================================

const resultTable = document.getElementById("resultTable");
const enrollmentTable = document.getElementById("enrollmentTable");
const courseNameInput = document.getElementById("courseName");
const professorInput = document.getElementById("professorName");
const resultCount = document.getElementById("resultCount");
const totalCredit = document.getElementById("totalCredit");
const emptyMessage = document.getElementById("emptyMessage");
const completionType = document.getElementById("completionType");
const courseArea = document.getElementById("courseArea");
const tabButtons = document.querySelectorAll(".tab-button");

// ==========================================
// 전공 / 교양에 따라 드롭다운 목록 변경
// ==========================================

function updateSelectOptions() {

    // 기존 목록 비우기
    completionType.innerHTML = "";
    courseArea.innerHTML = "";

    // 전공/교양 강좌 선택 드롭다운
    if (selectedType === "전공") {
        completionType.innerHTML = `
            <option value="">전체</option>
            <option value="전필">전필</option>
            <option value="전선">전선</option>
        `;
        courseArea.innerHTML = `
            <option value="">전체</option>
            <option value="게임전공">게임전공</option>
            <option value="컴퓨터과학전공">컴퓨터과학전공</option>
        `;
    }

    else { //교양
        completionType.innerHTML = `
            <option value="">전체</option>
            <option value="교필">교필</option>
            <option value="교선">교선</option>
        `;
        courseArea.innerHTML = `
            <option value="">전체</option>
            <option value="기초교양">기초교양</option>
            <option value="인문사회">인문사회</option>
            <option value="문화예술">문화예술</option>
        `;
    }
}

// ==========================================
// PHP 서버에 요청 보내기
// ==========================================

async function api(path, options = {}) {
    const response = await fetch("course-registration/php/" + path, {
        credentials: "same-origin",
        ...options
    });

    let data;

    try {
        data = await response.json();
    } catch (error) {
        throw new Error("서버 응답을 확인할 수 없습니다.");
    }

    if (response.status === 401) {
        window.location.href = "login.html";
        throw new Error("로그인이 필요합니다.");
    }

    if (!data.success) {
        throw new Error(data.message || "요청에 실패했습니다.");
    }

    return data;
}

// ==========================================
// 테이블에 셀 추가
// ==========================================

function cell(row, value) {
    const td = document.createElement("td");
    td.textContent = value ?? "";
    row.appendChild(td);
}

// ==========================================
// 검색 결과 화면에 출력
// ==========================================

function displayCourses(courseList) {
    // 기존 검색 결과 삭제
    // resultTable.innerHTML = "";
    resultTable.replaceChildren();

    // 검색된 강좌 개수 표시
    resultCount.textContent = courseList.length + "개 강좌";

    // 결과가 하나도 없는 경우
    if (courseList.length === 0) {
        const row = document.createElement("tr");
        const td = document.createElement("td");

        td.colSpan = 7;
        td.textContent = "조회된 강좌가 없습니다.";

        row.appendChild(td);
        resultTable.appendChild(row);
        return;
    }

    // 검색된 강좌를 하나씩 표에 추가
    courseList.forEach(course => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${course.name}</td>
            <td>${course.completion}</td>
            <td>${course.credit}</td>
            <td>${course.professor}</td>
            <td>${course.time}</td>
            <td>${course.classroom}</td>
            <td>
                <button
                    class="apply-button"
                    onclick="enrollCourse('${course.id}')">
                    신청
                </button>
            </td>
        `;
        resultTable.appendChild(row);
    });
}

// ==========================================
// PHP에서 강좌 목록 가져오기
// ==========================================

async function fetchCourses(params) {
    try {
        const query = new URLSearchParams(params);
        const data = await api("selectSQL.php?" + query);

        displayCourses(data.courses); // 화면에 출력
    } catch (error) {
        showToast(error.message);
    }
}

// ==========================================
// 기본 강좌 조회
// ==========================================
// 전공/교양 + 이수구분 + 학과를 기준으로 조회

function viewCourses() {
    return fetchCourses({
        type: selectedType, // 전공/교양
        completion: completionType.value, // 이수구분
        department: courseArea.value // 학과
    });
}

// ==========================================
// 상세 검색
// ==========================================
// 전공 / 교양 탭과 상관없이
// 전체 강좌에서 교과목명 / 교수명으로 검색

function searchCourses() {
    return fetchCourses({
        name: courseNameInput.value.trim(),
        professor: professorInput.value.trim()
    });
}

// ==========================================
// 수강신청 내역 가져오기
// ==========================================

async function loadEnrollment() {
    try {
        const data = await api("myCourseSQL.php");

        enrolledCourses = data.courses;
        displayEnrollment();
    } catch (error) {
        showToast(error.message);
    }
}

// ==========================================
// 수강신청
// ==========================================

async function enrollCourse(id) {
    try {
        const data = await api("insertSQL.php", {
            method: "POST",
            body: new URLSearchParams({
                course_id: id
            })
        });

        showToast(data.message);
        await loadEnrollment();
    } catch (error) {
        showToast(error.message);
    }
}

// ==========================================
// 수강취소
// ==========================================

async function cancelCourse(id) {
    // 취소할 과목 찾기
    const course = enrolledCourses.find(course => course.id === id);

    // 정말 취소할 건지 확인
    const answer = confirm(course.name + " 강좌를 수강 취소하시겠습니까?");

    // 아니오를 누르면 종료
    if (!answer) {
        return;
    }

    // 수강취소 요청
    try {
        const data = await api("deleteSQL.php", {
            method: "POST",
            body: new URLSearchParams({
                course_id: id
            })
        });

        showToast(data.message);
        await loadEnrollment();
    } catch (error) {
        showToast(error.message);
    }
}

// ==========================================
// 수강신청 내역 화면 출력
// ==========================================

function displayEnrollment() {

    // 기존 내용 삭제
    enrollmentTable.replaceChildren();

    if (enrolledCourses.length === 0) { // 아무것도 신청하지 않은 경우
        emptyMessage.style.display = "block";
    } else {
        emptyMessage.style.display = "none";
    }

    // 신청한 과목 하나씩 표에 추가
    enrolledCourses.forEach(course => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${course.name}</td>
            <td>${course.completion}</td>
            <td>${course.credit}</td>
            <td>${course.professor}</td>
            <td>${course.time}</td>
            <td>${course.classroom}</td>
            <td>
                <button
                    class="cancel-button"
                    onclick="cancelCourse('${course.id}')">
                    취소
                </button>
            </td>
        `;
        enrollmentTable.appendChild(row);
    });
    updateTotalCredit(); // 총 학점 계산
}

// ==========================================
// 총 신청학점 계산
// ==========================================

function updateTotalCredit() {
    const total = enrolledCourses.reduce(
        (sum, course) => sum + Number(course.credit), 0
    );

    totalCredit.textContent = total;
}

// ==========================================
// 전공 / 교양 탭 클릭
// ==========================================

tabButtons.forEach(button => {
    button.addEventListener("click", function () {
        // 모든 버튼에서 active 제거
        tabButtons.forEach(button => {
            button.classList.remove("active");
        });

        this.classList.add("active"); // 지금 누른 버튼만 active
        selectedType = this.dataset.type; // 전공인지 교양인지 저장

        // 입력했던 상세검색 내용 초기화
        courseNameInput.value = "";
        professorInput.value = "";

        updateSelectOptions(); // 드롭다운 목록 변경
        clearSearchResults(); // 검색 결과 초기화
    });
});

// ==========================================
// 조회 버튼
// ==========================================

document.getElementById("viewButton").addEventListener(
    "click", viewCourses
);

// ==========================================
// 상세 검색 버튼
// ==========================================

document.getElementById("searchButton").addEventListener(
    "click", searchCourses
);

// ==========================================
// 15. 초기화 버튼
// ==========================================

document.getElementById("resetButton").addEventListener("click", function () {
    // 검색창 비우기
    courseNameInput.value = "";
    professorInput.value = "";
    completionType.value = "";
    courseArea.value = "";

    viewCourses();
});

// ==========================================
// Enter 키로 검색
// ==========================================

courseNameInput.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        searchCourses();
    }
});

professorInput.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        searchCourses();
    }
});

// ==========================================
// 검색 결과 초기화
// ==========================================

function clearSearchResults() {
    resultCount.textContent = "0개 강좌";

    resultTable.innerHTML = `
    <tr class="empty-result-row">
        <td colspan="7">
            <div class="empty-result">
                <div class="empty-icon">⌕</div>
                <strong>강좌를 조회해보세요</strong>
                <p>
                    전공/교양과 조회 조건을 선택한 후<br>
                    조회 버튼을 눌러주세요.
                </p>
            </div>
        </td>
    </tr>
`;
}

// ==========================================
// 오른쪽 아래 알림창
// ==========================================

let toastTimer;

function showToast(message) {
    const toast = document.getElementById("toast");

    // 알림 내용 변경
    toast.textContent = message;
    toast.classList.add("show");

    // 2.5초 후 사라지게 함
    toastTimer = setTimeout(function () {
        toast.classList.remove("show");
    }, 2500);
}

// ==========================================
// 로그아웃
// ==========================================

document.getElementById("logoutButton").addEventListener("click", async function () {
    const answer = confirm("로그아웃 하시겠습니까?");

    if (!answer) {
        return;
    }

    try {
        await api("logout.php", {
            method: "POST"
        });

        window.location.href = "login.html";
    } catch (error) {
        showToast(error.message);
    }
});


// ==========================================
// 사이트 처음 실행될 때
// ==========================================


// 전공용 이수구분 / 영역 목록 만들기
updateSelectOptions();

// 처음에는 검색 결과를 바로 보여주지 않고
// 조회 버튼을 누르라고 표시
clearSearchResults();

// 수강신청 내역 불러오기
loadEnrollment();