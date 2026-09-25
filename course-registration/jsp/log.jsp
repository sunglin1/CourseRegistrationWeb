<%@ page language="java" import="java.io.*, java.time.*" contentType= "text/html;charset=utf8" pageEncoding="utf8"%>
<%!
	public void writeLog( String message, HttpServletRequest request, HttpSession session )
	{
		try 
		{
			// 현재 프로젝트의 jsp/log.txt 경로를 자동으로 가져옴
            String logFileName = request.getServletContext().getRealPath("/course-registration/jsp/log.txt");
			BufferedWriter writer = new BufferedWriter( new FileWriter( logFileName, true ) );

			// 로그 데이터 출력
			writer.append( "\nTime:\t" + LocalDate.now() + " " + LocalTime.now() 	// 접속 시간	
				+ "\tSessionID:\t" + session.getId()				// 접속 ID	
				+ "\tURI:\t" + request.getRequestURI()				// 현재 페이지 
				+ "\tPrevious:\t" + request.getHeader("referer") 		// 접속 경로(이전페이지)
				+ "\tBrowser:\t" + request.getHeader("User-Agent") 		// 접속 브라우저	
				+ "\tMessage:\t" + message );

			writer.close();
		} 
		// 예외 처리
		catch (IOException e) 
		{
			e.printStackTrace();
		}
	}
%>
