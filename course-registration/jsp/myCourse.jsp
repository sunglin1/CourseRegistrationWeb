<%@ page language="java"
    import="java.sql.*"
    contentType="text/html;charset=utf8"
    pageEncoding="utf8"%>

<%

    // 현재 로그인한 학생이라고 가정
    String currentStudentId = "202311088";


    try {

        // MySQL 드라이버 로드
        Class.forName(jdbc_driver);


        // MySQL 연결
        Connection myCourseCon =
            DriverManager.getConnection(
                mySQL_database,
                mySQL_id,
                mySQL_password
            );


        // 내가 신청한 강좌 조회
        String myCourseQuery =
            "SELECT " +

            "c.course_section_id, " +
            "c.course_name, " +
            "c.credit, " +
            "p.professor_name, " +
            "c.class_time, " +
            "c.classroom, " +
            "e.retake_type, " +
            "e.registered_at " +

            "FROM enrollment e " +

            "JOIN course c " +
            "ON e.course_section_id = c.course_section_id " +

            "LEFT JOIN professor p " +
            "ON c.professor_id = p.professor_id " +

            "WHERE e.student_id = ? " +

            "ORDER BY e.registered_at";


        PreparedStatement myCoursePstmt =
            myCourseCon.prepareStatement(myCourseQuery);


        myCoursePstmt.setString(
            1,
            currentStudentId
        );


        ResultSet myCourseResult =
            myCoursePstmt.executeQuery();

%>


<h2>내 수강목록</h2>


<table border="1"
       cellpadding="7"
       cellspacing="0">


    <tr>

        <th>학수번호-분반</th>
        <th>교과목명</th>
        <th>학점</th>
        <th>교수명</th>
        <th>수업시간</th>
        <th>강의실</th>
        <th>재수강여부</th>
        <th>수강취소</th>

    </tr>


<%

        while (myCourseResult.next()) {

%>


    <tr>

        <td>
            <%= myCourseResult.getString("course_section_id") %>
        </td>

        <td>
            <%= myCourseResult.getString("course_name") %>
        </td>

        <td>
            <%= myCourseResult.getString("credit") %>
        </td>

        <td>
            <%= myCourseResult.getString("professor_name") %>
        </td>

        <td>
            <%= myCourseResult.getString("class_time") %>
        </td>

        <td>
            <%= myCourseResult.getString("classroom") %>
        </td>

        <td>
            <%= myCourseResult.getString("retake_type") %>
        </td>


        <!-- 수강취소 버튼 -->
        <td>

            <form method="post"
                  action="./deleteSQL.jsp">

                <input type="hidden"
                       name="course_section_id"
                       value="<%= myCourseResult.getString("course_section_id") %>">

                <input type="submit"
                       value="수강취소">

            </form>

        </td>


    </tr>


<%

        }

%>


</table>


<%

        myCourseResult.close();
        myCoursePstmt.close();
        myCourseCon.close();


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