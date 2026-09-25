<%@ page language="java" import="java.sql.*, javax.sql.DataSource" contentType="text/html;charset=utf8" pageEncoding="utf8"%>

<%@ include file="./SQLconstants.jsp"%>

<%
	// 이전 페이지에서 전달 받은 메시지 확인
    request.setCharacterEncoding("UTF-8");
    String message = request.getParameter("message");
    if (message == null || message.equals("")) {
        message = "";
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
            "WHERE c.course_name LIKE ? " +
			"ORDER BY c.course_section_id";

		// PreparedStatement를 사용하여 SQL 쿼리 실행
		PreparedStatement pstmt = con.prepareStatement(query);
		//message 내용으로 검색할 수 있도록 설정
		pstmt.setString(1, "%" + message + "%");
		// SQL 쿼리 실행
        ResultSet result = pstmt.executeQuery();

%>

//표 형태
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
    </tr>

<%
        while (result.next()) {
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

<%-- <%@ include file="./log.jsp"%>
<%
	// 로그 데이터 추출
	writeLog( message + "와 관련된 책을 찾았습니다", request, session );
%> --%>
