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
<%@ page import="java.net.InetAddress" %>
<%@ page import="java.net.URL" %>
<%@ page import="java.text.DateFormat" %>
<%@ page import="java.util.Date" %>

<cruisecontrol:jmxbase id="jmxBase"/>
<%
final String dateNow = DateFormat.getDateTimeInstance(
        DateFormat.SHORT, 
        DateFormat.SHORT, 
        request.getLocale()
).format(new Date());

  String name = System.getProperty("ccname", "");
  String hostname = InetAddress.getLocalHost().getHostName();
  boolean jmxEnabled = true;
  URL jmxURLPrefix = new URL(jmxBase, "invoke?operation=build&objectname=CruiseControl+Project%3Aname%3D");

  String baseURL = request.getScheme() 
                 + "://" + request.getServerName() 
                 + ":" + request.getServerPort()
                 + request.getContextPath() + "/";
                 
  String thisURL = request.getRequestURI();

  String sort = request.getParameter("sort");
%>
<html>
  <head>
    <title><%= name%> phpUnderControl - SVN at <%= hostname %></title>
    <base href="<%=baseURL%>" />
    <link type="application/rss+xml" rel="alternate" href="rss" title="RSS" />
    <link type="text/css" rel="stylesheet" href="css/php-under-control.css" />
    <meta HTTP-EQUIV="Refresh" CONTENT="200" URL="<%=thisURL%>?sort=<%=sort%>" />
    <script type="text/javascript" src="js/prototype.js"></script>
    <script language="JavaScript">
    // <![CDATA[
    
    new Ajax.PeriodicalUpdater(
        'dashboard', 
        'dashboard.jsp', {
            method: 'get',
            frequency: 2
        }
    );
    
    function callServer(url, projectName) {
      document.getElementById('serverData').innerHTML = '<iframe src="' + url + '" width="0" height="0" frameborder="0"></iframe>';
      alert('Scheduling build for ' + projectName);
    }

    function checkIframe(stylesheetURL) {
      if (top != self) {//We are being framed!

        //For Internet Explorer
        if (document.createStyleSheet) {
          document.createStyleSheet(stylesheetURL);
        }
        else { //Non-ie browsers

          var styles = "@import url('" + stylesheetURL + "');";

          var newSS = document.createElement('link');

          newSS.rel = 'stylesheet';

          newSS.href = 'data:text/css,' + escape(styles);

          document.getElementsByTagName("head")[0].appendChild(newSS);
        }
      }
    }
    // ]]>
    </script>
  </head>
  <body onload="checkIframe('<%=baseURL + "css/php-under-control.css"%>')">
    <div id="container">
      <cruisecontrol:link id="baseUrl" />
      <h1>
        <a href="<%=baseUrl%>">phpUnderControl</a>
      </h1>
      <h1 class="white" align="center">
        <%= name%> phpUnderControl at <%= hostname %> [<em><%= dateNow %></em>]
      </h1>
      <div id="serverData" class="hidden"></div>
      <form>
        <table style="width:100%;">
          <tbody>
            <tr>
              <td>
                <div id="dashboard" style="background: transparent;">
                  <%@ include file="dashboard.jsp" %>
                </div>
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
  </body>
</html>