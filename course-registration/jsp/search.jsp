<%@ page contentType= "text/html;charset=utf8" pageEncoding="utf8"%>
<% 
	request.setCharacterEncoding("UTF-8");
	
	// 현재 선택된 검색 조건 유지
    String searchKeyword = request.getParameter("keyword");
    String searchCompletionType = request.getParameter("completion_type");
    String searchCampus = request.getParameter("campus");
    String searchTargetGrade = request.getParameter("target_grade");
    String searchDepartment = request.getParameter("department_id");

    if (searchKeyword == null) searchKeyword = "";
    if (searchCompletionType == null) searchCompletionType = "";
    if (searchCampus == null) searchCampus = "";
    if (searchTargetGrade == null) searchTargetGrade = "";
    if (searchDepartment == null) searchDepartment = "";
%>
<HTML>
	<HEAD>      
		<TITLE>수강신청 사이트</TITLE>

		<script language="javascript">  
			// 지정한 url로 이동하는 함수 
			function move( url )	
	 		{
				document.formm.action = url;
				document.formm.submit();
			}

			// 검색 함수
			function searchCourse()
			{
				document.formm.action = "./search.jsp";
				document.formm.submit();
			}


        // 검색조건 초기화 함수
			function resetSearch()
			{
				location.href = "./search.jsp";
			}
		</script>
	</HEAD>

	<BODY>
		<!-- 화면구성 -->
		<BR> 
		<h2>강좌 조회</h2>
		<form name = "formm" method = "post">				
			&nbsp; &nbsp; &nbsp; 
			<%-- 필터 --%>

			<!-- 교과목명 검색 -->
			교과목명 : <INPUT TYPE="text" NAME="keyword" SIZE="60" VALUE="<%= searchKeyword %>"> 
        	&nbsp;&nbsp;

			<!-- 이수구분 -->
			이수구분 : <select name="completion_type">
				<option value="">전체</option>
				<option value="전심"<%= searchCompletionType.equals("전심") ? "selected" : "" %>>전심</option>
				<option value="전선"<%= searchCompletionType.equals("전선") ? "selected" : "" %>>전선</option>
				<option value="전필"<%= searchCompletionType.equals("전필") ? "selected" : "" %>>전필</option>
				<option value="교필"<%= searchCompletionType.equals("교필") ? "selected" : "" %>>교필</option>
				<option value="교선"<%= searchCompletionType.equals("교선") ? "selected" : "" %>>교선</option>
				<option value="일선"<%= searchCompletionType.equals("일선") ? "selected" : "" %>>일선</option>
			</select>
			&nbsp;&nbsp;

			<!-- 캠퍼스 -->
			캠퍼스 : <select name="campus">
				<option value="">전체</option>
				<option value="서울"<%= searchCampus.equals("서울") ? "selected" : "" %>>서울</option>
				<option value="천안"<%= searchCampus.equals("천안") ? "selected" : "" %>>천안</option>
			</select>

			<br><br>
			&nbsp; &nbsp; &nbsp; 
			<!-- 대상학년 -->
			대상학년 :<select name="target_grade">
				<option value="">전체</option>
				<option value="1"<%= searchTargetGrade.equals("1") ? "selected" : "" %>>1학년</option>
				<option value="2"<%= searchTargetGrade.equals("2") ? "selected" : "" %>>2학년</option>
				<option value="3"<%= searchTargetGrade.equals("3") ? "selected" : "" %>>3학년</option>
				<option value="4"<%= searchTargetGrade.equals("4") ? "selected" : "" %>>4학년</option>
			</select>
			&nbsp;&nbsp;

			<!-- 개설학과 -->

			개설학과 :<select name="department_id">
				<option value="">전체</option>
				<option value="DEPT-GAME"<%= searchDepartment.equals("DEPT-GAME") ? "selected" : "" %>>게임전공</option>
				<option value="DEPT-ANI"<%= searchDepartment.equals("DEPT-ANI") ? "selected" : "" %>>애니메이션전공</option>
				<option value="DEPT-COM"<%= searchDepartment.equals("DEPT-COM") ? "selected" : "" %>>컴퓨터과학과</option>
			</select>

		</form>  

		<br>
		&nbsp; &nbsp; &nbsp; 
		
		<%-- 버튼 --%>
		<input type="button"
			value="강좌 검색"
			onclick="searchCourse();">

		<input type="button"
			value="초기화"
			onclick="resetSearch();">

		<br><br>

		
		<!-- 전체 강좌 목록 출력 -->
		<%@ include file="./selectSQL.jsp"%>

		<!-- 내 강좌 목록 출력 -->
		<%@ include file="./myCourse.jsp"%>
	</BODY>
</HTML>
 