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

    String ccname = System.getProperty("ccname", "");
    String project = request.getPathInfo().substring(1);
%>
<html>
<head>
  <title><%= ccname%> CruiseControl Build Results</title>
  <base href="<%=request.getScheme()%>://<%=request.getServerName()%>:<%=request.getServerPort()%><%=request.getContextPath()%>/" />
  <link type="text/css" rel="stylesheet" href="css/cruisecontrol.css"/>
  <link type="application/rss+xml" rel="alternate" href="<%= request.getContextPath() %>/rss/<%= project %>" title="RSS"/>
  <style type="text/css">
    body {
        font-family: arial,helvetica,sans-serif;
        font-size: 11px;
        margin: 0;
        padding: 10px;
    }
    table {
        border: 0;
    }
    #main-table {
        border-collapse: collapse;
        width: 98%;
    }
    #main-header th {
        font-size: 11px;
        margin: 0;
        padding: 0;
    }
    #main-header th.left {
        background: transparent url('images/puc/header-left.png') 0 0 no-repeat;
        height: 70px;
        width: 250px;
    }
    #main-header th.center {
        background: transparent url('images/puc/header-center.png') 0 0 repeat-x;
    }
    #main-header th.right {
        background: transparent url('images/puc/header-right.png') 0 0 no-repeat;
        width: 50px;
    }
    #main-header th h1 {
        float: left;
        margin: 0;
        padding: 0;
    }
    #main-header th h1 a {
        display: block;
        height: 70px;
        text-indent: -9999px;
        width: 250px;
    }

    #main-header th form {
        margin: 20px 0 0 15px;
        padding: 0;
        width: 180px;
    }
    #main-header th form fieldset {
        border: 0 none;
        margin: 0;
        padding: 5px;
        width: 165px;
    }
    #main-header th form fieldset legend a {
        color: #eeeeec;
        font-weight: bold;
        margin-left: 8px;
        text-decoration: none;
    }
    #main-header th form fieldset select {
        border: 1px solid #2e3436;
        font-size: 11px;
        margin: 0;
        padding: 0;
        width: 150px;
    }
    #main-header th span {
        color: #eeeeec;
        display: block;
        float: right;
        font-style: italic;
        font-weight: normal;
        margin: 25px 10px 0 0;
        text-align: left;
    }
    #main-body td {
        background-color: #eeeeec;
    }
    #main-body img {
        display: none;
    }
    #main-body div {
        background: transparent url('images/puc/tab-table-bg.png') 0 0 repeat-x;
        height: 27px;
    }
    .tab-table {
        float: left;
        border: 0 none;
    }
    .tab-table .tabs, .tab-table .tabs-selected {
        background: transparent;
        border: 0 none;
        padding: 0;
    }
    .tab-table .tabs a, .tab-table .tabs-selected {
        background: transparent url('images/puc/tab-table-bg.png') 0 0 repeat-x;
        border-right: 1px solid #888a85;
        color: #555753;
        display: block;
        font-size: 12px;
        font-weight: bold;
        line-height: 27px;
        text-align: center;
        width: 100px;
    }
    .tab-table .tabs a:hover, .tab-table .tabs-selected {
        background: transparent url('images/puc/tab-selected.png') 0 0 repeat-x;
        border-right-color: #3465a4;
        color: #eeeeec;
    }
    #main-body table.result {
        border: 0 none;
        border-collapse: collapse;
        width: 98%;
    }
    #main-body table.result th {
        background-color: #a40000;
        color: #eeeeec;
        font-size: 13px;
        line-height: 20px;
        text-align: left;
        text-indent: 5px;
        white-space: nowrap;
    }
    #main-body table.result tbody td {
        font-size: 11px;
        line-height: 13px;
        padding-left: 5px;
    }
    #main-body table.result tbody tr.oddrow td {
        background-color: #d3d7cf;
    } 
    #main-body table.result tbody td.error {
        color: #c00;
    }
    #main-body table.result tbody td.warning {
        color: #000;
    }
    #main-body pre.code-fragment {
        background-color: #fff;
        border: 1px solid #d3d7cf;
    }
  </style>
</head>
<body>
  <br />
  <table id="main-table" align="center" width="98%">
    <thead id="main-header">
      <%@ include file="header.jsp" %>
    </thead>
    <tbody id="main-body">
    <tr>
      <td colspan="5">
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
      </td>
    </tr>
    </tbody>
  </table>
</body>
</html>
