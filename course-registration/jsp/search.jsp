<%@ page contentType= "text/html;charset=utf8" pageEncoding="utf8"%>
<% request.setCharacterEncoding("UTF-8");%>
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
		</script>
	</HEAD>

		<!-- 화면구성 -->
		<BR> 
		<h2>강좌 조회</h2>
		<form name = "formm" method = "post">				
			&nbsp; &nbsp; &nbsp; 
			교과목명 : <INPUT TYPE="text" NAME="keyword" SIZE="60" VALUE="<%= request.getParameter("keyword") == null ? "" : request.getParameter("keyword") %>"> 
		</form>  
		 &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;   
		<INPUT TYPE = "button" value = "강좌 검색" onClick="javascript:move( './search.jsp' );">
		<%-- <INPUT TYPE = "button" value = "새 책  추가" onClick="javascript:move( './insert.jsp' );">	
		<INPUT TYPE = "button" value = "책 삭제" onClick="javascript:move( './delete.jsp' );">	 --%>
		<BR> <BR> &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		<BR> <BR>  
		
		<!-- 전체 강좌 목록 출력 -->
		<%@ include file="./selectSQL.jsp"%>

		<!-- 내 강좌 목록 출력 -->
		<%@ include file="./myCourse.jsp"%>
	</BODY>
</HTML>
 