
// ==========================================
// HTML 요소 가져오기
// ==========================================

const studentIdInput = document.getElementById("studentId");
const passwordInput = document.getElementById("password");
const loginButton = document.getElementById("loginButton");
const loginMessage = document.getElementById("loginMessage");

// ==========================================
// 로그인
// ==========================================

async function login() {
    const studentId = studentIdInput.value.trim();
    const password = passwordInput.value.trim();

    // 학번 미입력
    if (!studentId) {
        loginMessage.textContent = "학번을 입력해 주세요.";
        studentIdInput.focus();
        return;
    }

    // 비밀번호 미입력
    if (!password) {
        loginMessage.textContent = "비밀번호를 입력해 주세요.";
        passwordInput.focus();
        return;
    }

    // 오류 문구 제거
    loginMessage.textContent = "";

    // PHP 서버에 로그인 요청
    try {
        const body = new URLSearchParams({
            student_id: studentId,
            password: password
        });

        const response = await fetch("course-registration/php/login.php", {
            method: "POST",
            body: body,
            credentials: "same-origin"
        });

        const data = await response.json();

        // 로그인 실패
        if (!data.success) {
            loginMessage.textContent = data.message || "로그인에 실패했습니다.";
            return;
        }

        // 로그인 성공
        window.location.href = "index.php";

    } catch (error) {
        // 서버 연결 또는 응답 오류
        loginMessage.textContent = "서버에 연결할 수 없습니다.";
    }
}

// ==========================================
// 로그인 버튼 클릭
// ==========================================

loginButton.addEventListener("click", login);

// ==========================================
// Enter 키로 로그인
// ==========================================

studentIdInput.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        passwordInput.focus();
    }
});

passwordInput.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        login();
    }
});
