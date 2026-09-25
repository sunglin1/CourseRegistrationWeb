<%@ page language="java" import="java.sql.*, javax.sql.DataSource" contentType="text/html;charset=utf8" pageEncoding="utf8"%>

<%@ include file="./SQLconstants.jsp"%>
<%@ include file="./log.jsp"%>

<%
	// 이전 페이지에서 전달 받은 메시지 확인
    request.setCharacterEncoding("UTF-8");

	// 검색 조건 가져오기
    String keyword = request.getParameter("keyword");
    String completionType = request.getParameter("completion_type");
    String campus = request.getParameter("campus");
    String targetGrade = request.getParameter("target_grade");
    String departmentId = request.getParameter("department_id");

    // null 처리
    if (keyword == null) keyword = "";
    if (completionType == null) completionType = "";
    if (campus == null) campus = "";
    if (targetGrade == null) targetGrade = "";
    if (departmentId == null) departmentId = "";
	

	// 검색 로그
	//존재하는 검색 조건에 대해 로그 기록
    if (!keyword.equals("") || !completionType.equals("") || !campus.equals("") || !targetGrade.equals("") || !departmentId.equals(""))
    {
        String logMessage = "강좌 검색 - " + "교과목명:[" + keyword + "] " + "이수구분:[" + completionType + "] " + "캠퍼스:[" + campus + "] " + "대상학년:[" + targetGrade + "] " + "개설학과:[" + departmentId + "]";

        writeLog(logMessage, request, session);

    }

    try {
        // MySQL 연결
        Class.forName(jdbc_driver);

        Connection con = DriverManager.getConnection(
            mySQL_database,
            mySQL_id,
            mySQL_password
        );

        // 강좌 쿼리
        String query =
            "SELECT " +
            "c.course_section_id, " +
            "c.completion_type, " +
            "c.campus, " +
            "c.target_grade, " +
            "c.credit, " +
            "c.course_name, " +
            "p.professor_name, " +
            "c.class_time, " +
            "c.classroom, " +
            "c.capacity, " +
            "c.note, " +
            "d.department_name, " +
            "c.syllabus_url " +
            "FROM course c " +
            "LEFT JOIN professor p ON c.professor_id = p.professor_id " +
            "LEFT JOIN department d ON c.department_id = d.department_id " +
            "WHERE c.course_name LIKE ? ";

			// 검색 조건에 따라 쿼리 추가
			if (!completionType.equals("")) {
				query += "AND c.completion_type = ? ";
			}

			if (!campus.equals("")) {
				query += "AND c.campus = ? ";
			}

			if (!targetGrade.equals("")) {
				query += "AND c.target_grade = ? ";
			}

			if (!departmentId.equals("")) {
				query += "AND c.department_id = ? ";
			}

        query += "ORDER BY c.course_section_id";


		// PreparedStatement를 사용하여 SQL 쿼리 실행
		PreparedStatement pstmt = con.prepareStatement(query);
		
		//검색 설정
		int parameterIndex = 1;

		pstmt.setString(parameterIndex++, "%" + keyword + "%"); // 교과목명 검색

		if (!completionType.equals("")) { // 이수구분
			pstmt.setString(parameterIndex++, completionType);
		}
		if (!campus.equals("")) { // 캠퍼스
			pstmt.setString(parameterIndex++, campus);
		}
		if (!targetGrade.equals("")) { // 대상학년
			pstmt.setString(parameterIndex++, targetGrade);
		} 
		if (!departmentId.equals("")) { // 개설학과
			pstmt.setString(parameterIndex++, departmentId);
		}
		// SQL 쿼리 실행
        ResultSet result = pstmt.executeQuery();

%>

<h2>전체 강좌</h2>

<table border="1" cellpadding="7" cellspacing="0">

    <tr>
        <th>학수번호-분반</th>
        <th>이수구분</th>
        <th>캠퍼스</th>
        <th>대상학년</th>
        <th>학점</th>
        <th>교과목명</th>
        <th>교수명</th>
        <th>수업시간</th>
        <th>강의실</th>
        <th>정원</th>
        <th>개설학과</th>
        <th>비고</th>
        <th>강의계획서</th>
        <th>수강신청</th>
    </tr>

<%
        boolean hasResult = false;

        while (result.next()) {
            hasResult = true;
%>

    <tr>
        <td><%= result.getString("course_section_id") %></td>
        <td><%= result.getString("completion_type") %></td>
        <td><%= result.getString("campus") %></td>
        <td><%= result.getString("target_grade") %></td>
        <td><%= result.getString("credit") %></td>
        <td><%= result.getString("course_name") %></td>
        <td><%= result.getString("professor_name") %></td>
        <td><%= result.getString("class_time") %></td>
        <td><%= result.getString("classroom") %></td>
        <td><%= result.getString("capacity") %></td>
        <td><%= result.getString("department_name") %></td>
        <td><%= result.getString("note") %></td>

        <td>
            <a href="<%= result.getString("syllabus_url") %>"
               target="_blank">
                보기
            </a>
        </td>

		<!-- 수강신청 버튼 -->
        <td>

            <form method="post"
                  action="./insertSQL.jsp">

                <input type="hidden"
                       name="course_section_id"
                       value="<%= result.getString("course_section_id") %>">

                <input type="submit"
                       value="수강신청">

            </form>

        </td>
    </tr>

<%
        }
		//검색 결과가 없는 경우
		if (!hasResult) {
%>

    <tr>
        <td colspan="14" align="center">검색 결과가 없습니다.</td>
    </tr>

<%
        }
%>

</table>

<%
        // 연결 종료
        result.close();
        pstmt.close();
        con.close();

    } catch (SQLException e) {
%>

    <p>SQL 오류 : <%= e.getMessage() %></p>
	
<%
    } catch (Exception e) {
%>

    <p>오류 : <%= e.getMessage() %></p>
	
<%
    }
%>
