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
<%@ page errorPage="/error.jsp" contentType="text/html; charset=UTF-8"%>
<%@ taglib uri="/WEB-INF/cruisecontrol-jsp11.tld" prefix="cruisecontrol"%>
<%@ page import="net.sourceforge.cruisecontrol.*" %>
<%
  String name = System.getProperty("ccname", "");
  String host = request.getServerName();

  String baseURL = request.getScheme() 
                 + "://" + host 
                 + ":" + request.getServerPort()
                 + request.getContextPath() + "/";
%>
<html>
  <head>
    <title><%= name%> phpUnderControl - SVN at <%= host %></title>
    <base href="<%=baseURL%>" />
    <link type="application/rss+xml" rel="alternate" href="rss" title="RSS" />
    <link type="text/css" rel="stylesheet" href="css/php-under-control.css?v=2" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
  </head>
  <body onload="checkIframe('<%=baseURL + "css/php-under-control.css"%>')">
    <div id="serverData" style="display:none;"></div>
    <div id="container">
      <h1>
        <a href="<%=baseURL%>">phpUnderControl</a>
      </h1>
      <h1 class="white" align="center">
        <%= name%> phpUnderControl at <%= host %> [
        <em id="servertime"><%@ include file="servertime.jsp" %></em>
        ]
      </h1>
      <div id="serverData" class="hidden"></div>
      <form>
        <table style="width:100%;">
          <tbody>
            <tr>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td id="dashboard">
                <%@ include file="dashboard.jsp" %>
              </td>
            </tr>
            <tr>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td align="right">
                <a href="rss"><img border="0" src="images/rss.png"/></a>
              </td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
    <%@ include file="footer.jsp" %>
    <script type="text/javascript" src="js/prototype.js"></script>
    <script type="text/javascript" src="js/php-under-control.js"></script>
  </body>
</html>