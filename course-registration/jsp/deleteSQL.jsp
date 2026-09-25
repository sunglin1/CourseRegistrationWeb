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


    // 취소할 강좌 번호
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


        // enrollment에서 해당 강좌 삭제
        String deleteQuery =
            "DELETE FROM enrollment " +
            "WHERE student_id = ? " +
            "AND course_section_id = ?";


        PreparedStatement pstmt =
            con.prepareStatement(deleteQuery);


        pstmt.setString(
            1,
            student_id
        );


        pstmt.setString(
            2,
            course_section_id
        );


        int deletedRows =
            pstmt.executeUpdate();


        // 정상적으로 삭제된 경우
        if (deletedRows > 0) {

            resultMessage =
                " * 수강신청이 취소되었습니다.";

			// 로그 기록
			writeLog(
				"학생(" + student_id + ")이 강좌("
				+ course_section_id + ")의 수강신청을 취소하였습니다.",
				request,
				session
			);
        }

        // 삭제할 데이터가 없는 경우
        else {

            resultMessage =
                " * 취소할 강좌가 없습니다.";

        }


        pstmt.close();
        con.close();


    } catch (SQLException e) {

        resultMessage =
            " * 수강취소 오류 : " +
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