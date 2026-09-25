<%@ page language="java"
    import="java.sql.*"
    contentType="text/html;charset=utf8"
    pageEncoding="utf8"%>

<%@ include file="./SQLconstants.jsp"%>
<%@ include file="./log.jsp"%>

<%

    request.setCharacterEncoding("UTF-8");


    // 현재 로그인한 학생이라고 가정
    String student_id = "202311088";


    // 수강신청할 강좌 번호
    String course_section_id =
        request.getParameter("course_section_id");


    String resultMessage = "";


    try {

        // MySQL 드라이버 로드
        Class.forName(jdbc_driver);


        // MySQL 연결
        Connection con =
            DriverManager.getConnection(
                mySQL_database,
                mySQL_id,
                mySQL_password
            );


        // 이미 신청한 강좌인지 확인
        String checkQuery =
            "SELECT course_section_id " +
            "FROM enrollment " +
            "WHERE student_id = ? " +
            "AND course_section_id = ?";


        PreparedStatement checkPstmt =
            con.prepareStatement(checkQuery);


        checkPstmt.setString(
            1,
            student_id
        );


        checkPstmt.setString(
            2,
            course_section_id
        );


        ResultSet checkResult =
            checkPstmt.executeQuery();


        // 이미 신청되어 있는 경우
        if (checkResult.next()) {

            resultMessage =
                " * 이미 신청한 강좌입니다.";

        }

        // 신청되어 있지 않은 경우
        else {

            String insertQuery =
                "INSERT INTO enrollment " +
                "(student_id, course_section_id, retake_type) " +
                "VALUES (?, ?, '신규')";


            PreparedStatement insertPstmt =
                con.prepareStatement(insertQuery);


            insertPstmt.setString(
                1,
                student_id
            );


            insertPstmt.setString(
                2,
                course_section_id
            );


            insertPstmt.executeUpdate();


            insertPstmt.close();


            resultMessage =
                " * 수강신청이 완료되었습니다.";


			// 로그 기록
			writeLog(
				"학생(" + student_id + ")이 강좌("
				+ course_section_id + ")를 수강신청하였습니다.",
				request,
				session
			);
        }


        checkResult.close();
        checkPstmt.close();
        con.close();


    } catch (SQLException e) {

        resultMessage =
            " * 수강신청 오류 : " +
            e.getMessage();


    } catch (Exception e) {

        resultMessage =
            " * 오류 : " +
            e.getMessage();

    }

%>


<!-- 처리 후 search.jsp로 이동 -->

<script>
    alert("<%= resultMessage %>");
    location.href = "./search.jsp";
</script>