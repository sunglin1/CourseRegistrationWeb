const maxCredit = 18;
let selectedType = "전공";  // 처음에는 전공강좌가 선택되어 있음
let enrolledCourses = [];   // 학생이 신청한 과목을 저장하는 배열

const pageSize = 20;
let currentPage = 1;
let totalPages = 1;

// 마지막 검색 조건 저장
let currentSearchParams = {};

// 검색 요청 순서 관리
let latestSearchRequest = 0;


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
// 검색 드롭다운 옵션
// ==========================================

let filterOptions = {
    types: [],
    majorCompletions: [],
    generalAreas: [],
    departments: []
};

function fillOptions(select, values) {
    select.replaceChildren();
    select.add(new Option("전체", ""));

    values.forEach(value => {
        select.add(new Option(value, value));
    });
}


// ==========================================
// 전공 / 교양에 따라 드롭다운 목록 변경
// ==========================================

function updateSelectOptions() {
    const areaLabel = document.querySelector(
        'label[for="courseArea"]'
    );

    if (areaLabel) {
        areaLabel.textContent =
            selectedType === "전공" ? "학과" : "영역";
    }

    if (selectedType === "전공") {
        fillOptions(
            completionType,
            filterOptions.majorCompletions
        );

        fillOptions(
            courseArea,
            filterOptions.departments
        );
    } else {
        fillOptions(
            completionType,
            filterOptions.types.filter(
                type => type !== "전공"
            )
        );

        fillOptions(
            courseArea,
            filterOptions.generalAreas
        );
    }
}

// DB에서 옵션 불러오기
async function loadFilterOptions() {
    try {
        filterOptions = await api(
            "selectSQL.php?action=options"
        );

        updateSelectOptions();
    } catch (error) {
        showToast(error.message);
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


async function fetchCourses(params, page = 1) {
    // 현재 검색 조건 저장
    currentSearchParams = { ...params };

    // 요청 순서 기록
    const requestId = ++latestSearchRequest;

    try {
        const query = new URLSearchParams({
            ...params,
            page: page
        });

        const data = await api(
            "selectSQL.php?" + query.toString()
        );

        // 이전 검색 응답이 늦게 도착하면 무시
        if (requestId !== latestSearchRequest) {
            return;
        }

        // 현재 페이지 정보 갱신
        currentPage = data.page;
        totalPages = data.totalPages;

        // 강좌 표시
        displayCourses(data.courses);

        // 전체 검색 결과 개수 표시
        resultCount.textContent =
            `총 ${data.total}개 강좌`;

        // 페이지 번호 표시
        renderPagination();

    } catch (error) {
        if (requestId === latestSearchRequest) {
            showToast(error.message);
        }
    }
}


// ==========================================
// 페이지 번호 버튼 생성
// ==========================================

function renderPagination() {
    let pagination = document.getElementById(
        "coursePagination"
    );

    // 페이지 영역이 없다면 자동 생성
    if (!pagination) {
        pagination = document.createElement("div");
        pagination.id = "coursePagination";

        // 강좌 테이블 아래에 배치
        const table = resultTable.closest("table");
        table.insertAdjacentElement(
            "afterend",
            pagination
        );
    }

    // 기본 스타일
    Object.assign(pagination.style, {
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        gap: "6px",
        marginTop: "20px",
        marginBottom: "20px",
        flexWrap: "wrap"
    });

    pagination.replaceChildren();

    // 결과가 없거나 1페이지면 버튼 숨기기
    if (totalPages <= 1) {
        return;
    }

    // 이전 버튼
    addPageButton(
        pagination,
        "이전",
        currentPage - 1,
        currentPage === 1
    );

    // 한 번에 최대 5개의 페이지 번호 표시
    const startPage =
        Math.floor((currentPage - 1) / 5) * 5 + 1;

    const endPage = Math.min(
        startPage + 4,
        totalPages
    );

    for (let i = startPage; i <= endPage; i++) {
        addPageButton(
            pagination,
            String(i),
            i,
            false,
            i === currentPage
        );
    }

    // 다음 버튼
    addPageButton(
        pagination,
        "다음",
        currentPage + 1,
        currentPage === totalPages
    );
}

// ==========================================
// 개별 페이지 버튼 생성
// ==========================================

function addPageButton(
    container,
    label,
    page,
    disabled = false,
    active = false
) {
    const button = document.createElement("button");

    button.type = "button";
    button.textContent = label;
    button.disabled = disabled;

    Object.assign(button.style, {
        padding: "7px 12px",
        border: "1px solid #d1d5db",
        borderRadius: "6px",
        backgroundColor: active ? "#2563eb" : "#ffffff",
        color: active ? "#ffffff" : "#374151",
        cursor: disabled ? "default" : "pointer",
        fontWeight: active ? "bold" : "normal",
        opacity: disabled ? "0.4" : "1"
    });

    if (active) {
        button.setAttribute("aria-current", "page");
    }

    button.addEventListener("click", () => {
        if (disabled || active) {
            return;
        }

        fetchCourses(currentSearchParams, page);
    });

    container.appendChild(button);
}


// ==========================================
// 기본 강좌 조회
// ==========================================
// 전공/교양 + 이수구분 + 학과를 기준으로 조회

function viewCourses() {
    return fetchCourses({
        group: selectedType,

        type: selectedType === "전공"
            ? "전공"
            : completionType.value,

        completion: selectedType === "전공"
            ? completionType.value
            : courseArea.value,

        department: selectedType === "전공"
            ? courseArea.value
            : ""
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
// 초기화 버튼
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
    
    // 이전 검색 요청 무효화
    latestSearchRequest++;

    // 페이지 정보 초기화
    currentPage = 1;
    totalPages = 1;
    currentSearchParams = {};

    // 기존 페이지 번호 삭제
    const pagination = document.getElementById(
        "coursePagination"
    );

    if (pagination) {
        pagination.replaceChildren();
    }

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


loadFilterOptions();

// 처음에는 검색 결과를 바로 보여주지 않고
// 조회 버튼을 누르라고 표시
clearSearchResults();

// 수강신청 내역 불러오기
loadEnrollment();