<%--********************************************************************************
 * CruiseControl, a Continuous Integration Toolkit
 * Copyright (c) 2001, ThoughtWorks, Inc.
 * 200 E. Randolph, 25th Floor
 * Chicago, IL 60601 USA
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     + Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *
 *     + Redistributions in binary form must reproduce the above
 *       copyright notice, this list of conditions and the following
 *       disclaimer in the documentation and/or other materials provided
 *       with the distribution.
 *
 *     + Neither the name of ThoughtWorks, Inc., CruiseControl, nor the
 *       names of its contributors may be used to endorse or promote
 *       products derived from this software without specific prior
 *       written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************--%>
<%@page contentType="text/html; charset=utf-8"%>
<%@page errorPage="/error.jsp"%>
<%@ taglib uri="/WEB-INF/cruisecontrol-jsp11.tld" prefix="cruisecontrol"%>
<%
    String rmiPort = System.getProperty("cruisecontrol.rmiport");
    boolean rmiEnabled = rmiPort != null;

    String ccname  = System.getProperty("ccname", "");
    String project = request.getPathInfo().substring(1);
%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title><%= ccname%> phpUnderControl - SVN - Build Results</title>
    <base href="<%=request.getScheme()%>://<%=request.getServerName()%>:<%=request.getServerPort()%><%=request.getContextPath()%>/" />
    <link type="text/css" rel="stylesheet" href="css/SyntaxHighlighter.css"/>
    <link type="text/css" rel="stylesheet" href="css/php-under-control.css"/>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link type="application/rss+xml" rel="alternate" href="<%= request.getContextPath() %>/rss/<%= project %>" title="RSS"/>
  </head>
  <body>
    <div id="container">
      <%@ include file="header.jsp" %>
      <cruisecontrol:tabsheet>
        <tr>
          <td>
            <cruisecontrol:tab name="buildResults" label="Overview" >
              <%@ include file="buildresults.jsp" %>
            </cruisecontrol:tab>

            <cruisecontrol:tab name="testResults" label="Tests" >
              <%@ include file="phpunit.jsp" %>
            </cruisecontrol:tab>

            <cruisecontrol:loglink id="logs_url"/>
            <cruisecontrol:tab name="log" url="<%=logs_url%>" label="XML Log File" />

            <cruisecontrol:tab name="metrics" label="Metrics" >
              <%@ include file="metrics.jsp" %>
            </cruisecontrol:tab>
              
            <cruisecontrol:tab name="coverage" label="Coverage">
              <cruisecontrol:artifactsLink>
                <iframe src="<%= artifacts_url %>/coverage/index.html" width="100%" height="550" frameborder="0" />
                </iframe>
              </cruisecontrol:artifactsLink>
            </cruisecontrol:tab>
              
            <cruisecontrol:tab name="documentation" label="Documentation">
              <cruisecontrol:artifactsLink>
                <iframe src="<%= artifacts_url %>/api/index.html" width="100%" height="550" frameborder="0" />
                </iframe>
              </cruisecontrol:artifactsLink>
            </cruisecontrol:tab>

            <cruisecontrol:tab name="phpcs" label="CodeSniffer">
              <%@ include file="phpcs.jsp" %>
            </cruisecontrol:tab>
              
            <cruisecontrol:tab name="pmd" label="PHPUnit PMD">
              <%@ include file="phpunit-pmd.jsp" %>
            </cruisecontrol:tab>
          </td>
        </tr>
      </cruisecontrol:tabsheet>
    </div>
    <%@ include file="footer.jsp" %>
  </body>
</html>
